<?php
namespace App\Controllers;

class StoreController extends Controller
{
    public function home(): void
    {
        $stmt = db()->query('SELECT * FROM products WHERE status = "active" ORDER BY sort_order, id');
        $products = $stmt->fetchAll();

        $categories = [];
        foreach ($products as $p) {
            $c = $p['category'] ?: 'Genel';
            $categories[$c][] = $p;
        }

        echo $this->render('store/home', [
            'title' => setting('site_name', 'RCVXTR') . ' — Hosting & Domain',
            'products' => $products,
            'categories' => $categories,
        ], 'store');
    }

    public function category(string $category): void
    {
        $stmt = db()->prepare('SELECT * FROM products WHERE status = "active" AND category = ? ORDER BY sort_order, id');
        $stmt->execute([$category]);
        $products = $stmt->fetchAll();
        echo $this->render('store/category', [
            'title' => $category,
            'category' => $category,
            'products' => $products,
        ], 'store');
    }

    public function product(string $slug): void
    {
        $stmt = db()->prepare('SELECT * FROM products WHERE slug = ? AND status = "active"');
        $stmt->execute([$slug]);
        $product = $stmt->fetch();
        if (!$product) {
            http_response_code(404);
            echo $this->render('errors/404', ['title' => 'Ürün bulunamadı'], 'store');
            return;
        }
        $stmt = db()->prepare('SELECT * FROM product_config_options WHERE product_id = ? ORDER BY sort_order');
        $stmt->execute([$product['id']]);
        $options = $stmt->fetchAll();
        foreach ($options as &$o) {
            $o['options'] = json_decode($o['options'], true) ?: [];
        }

        echo $this->render('store/product', [
            'title' => $product['name'],
            'product' => $product,
            'options' => $options,
        ], 'store');
    }
    public function order(): void
    {
        $this->validateCsrf();
        auth()->require();
        $userId = auth()->id();

        $productId = (int)$this->input('product_id', 0);
        $cycle = $this->input('billing_cycle', 'monthly');
        $domain = trim($this->input('domain', ''));
        $paymentMethod = $this->input('payment_method', 'balance');

        $stmt = db()->prepare('SELECT * FROM products WHERE id = ? AND status = "active"');
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        if (!$product) {
            flash('error', 'Geçersiz ürün.');
            redirect(url('store'));
        }

        $config = [];
        $stmt = db()->prepare('SELECT * FROM product_config_options WHERE product_id = ?');
        $stmt->execute([$productId]);
        foreach ($stmt->fetchAll() as $opt) {
            $val = $this->input('config_' . $opt['id'], '');
            if ($opt['required'] && $val === '') {
                flash('error', $opt['name'] . ' seçimi zorunludur.');
                redirect(url('store/product/' . $product['slug']));
            }
            $config[$opt['name']] = $val;
        }

        $multiplier = ['monthly' => 1, 'quarterly' => 3, 'semi_annual' => 6, 'annually' => 10, 'biennially' => 24][$cycle] ?? 1;
        $amount = round(((float)$product['price'] * $multiplier) + (float)$product['setup_fee'], 2);

        $orderNumber = 'ORD-' . strtoupper(substr(md5(uniqid('', true)), 0, 10));
        $stmt = db()->prepare('INSERT INTO orders (order_number, user_id, product_id, billing_cycle, domain, config, amount, status, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, "pending", ?)');
        $stmt->execute([$orderNumber, $userId, $productId, $cycle, $domain, json_encode($config), $amount, $paymentMethod]);

        $invoice = $this->createInvoice($userId, [
            ['description' => $product['name'] . ' (' . $this->cycleLabel($cycle) . ')' . ($domain ? ' — ' . $domain : ''), 'amount' => $amount],
        ]);

        if ($paymentMethod === 'balance') {
            $user = auth()->user();
            if ((float)$user['balance'] >= $amount) {
                $stmt = db()->prepare('UPDATE users SET balance = balance - ? WHERE id = ?');
                $stmt->execute([$amount, $userId]);
                $this->markInvoicePaid($invoice, 'balance');
                $this->activateOrder($orderNumber, $userId, $product, $cycle, $domain, $config, $amount);
                flash('success', 'Siparişiniz bakiyenizden ödenerek aktive edildi!');
                redirect(url('client/services'));
            } else {
                flash('error', 'Bakiyeniz yetersiz. Lütfen bakiye yükleyin veya farklı bir ödeme yöntemi seçin.');
                redirect(url('client/invoices/' . $invoice));
            }
        }

        flash('info', 'Siparişiniz oluşturuldu. Ödeme sonrası hizmetiniz otomatik aktive edilecektir.');
        redirect(url('client/invoices/' . $invoice));
    }

    public function contact(): void
    {
        $this->validateCsrf();
        flash('success', 'Mesajınız alındı. En kısa sürede dönüş yapacağız.');
        redirect(url('/'));
    }
    public function createInvoice(int $userId, array $items): int
    {
        $taxRate = (float)setting('tax_rate', 0);
        $prefix = setting('invoice_prefix', 'INV-');
        $number = $prefix . date('Y') . str_pad((string)random_int(1, 99999), 5, '0', STR_PAD_LEFT);

        $subtotal = 0;
        foreach ($items as $i) $subtotal += (float)$i['amount'];
        $tax = round($subtotal * $taxRate / 100, 2);
        $total = round($subtotal + $tax, 2);

        $stmt = db()->prepare('INSERT INTO invoices (invoice_number, user_id, amount, tax, total, status, due_date) VALUES (?, ?, ?, ?, ?, "unpaid", ?)');
        $stmt->execute([$number, $userId, $subtotal, $tax, $total, date('Y-m-d', strtotime('+7 days'))]);
        $invoiceId = (int)db()->lastInsertId();

        $stmt = db()->prepare('INSERT INTO invoice_items (invoice_id, description, amount) VALUES (?, ?, ?)');
        foreach ($items as $i) {
            $stmt->execute([$invoiceId, $i['description'], $i['amount']]);
        }
        return $invoiceId;
    }

    public function markInvoicePaid(int $invoiceId, string $gateway): void
    {
        $stmt = db()->prepare('SELECT * FROM invoices WHERE id = ?');
        $stmt->execute([$invoiceId]);
        $inv = $stmt->fetch();
        if (!$inv || $inv['status'] === 'paid') return;

        $stmt = db()->prepare('UPDATE invoices SET status = "paid", paid_at = ? WHERE id = ?');
        $stmt->execute([now(), $invoiceId]);

        $stmt = db()->prepare('INSERT INTO transactions (invoice_id, user_id, amount, gateway, transaction_id, status) VALUES (?, ?, ?, ?, ?, "completed")');
        $stmt->execute([$invoiceId, $inv['user_id'], $inv['total'], $gateway, uniqid('TXN-')]);
    }

    public function activateOrder(string $orderNumber, int $userId, array $product, string $cycle, string $domain, array $config, float $amount): void
    {
        $stmt = db()->prepare('UPDATE orders SET status = "active" WHERE order_number = ?');
        $stmt->execute([$orderNumber]);

        $nextDue = date('Y-m-d', strtotime($this->cycleDuration($cycle)));
        $stmt = db()->prepare('INSERT INTO services (user_id, product_id, domain, config, amount, billing_cycle, status, next_due_date) VALUES (?, ?, ?, ?, ?, ?, "active", ?)');
        $stmt->execute([$userId, $product['id'], $domain, json_encode($config), $amount, $cycle, $nextDue]);
    }

    public function cycleLabel(string $cycle): string
    {
        return ['monthly' => 'Aylık', 'quarterly' => '3 Aylık', 'semi_annual' => '6 Aylık', 'annually' => 'Yıllık', 'biennially' => '2 Yıllık'][$cycle] ?? $cycle;
    }

    public function cycleDuration(string $cycle): string
    {
        return ['monthly' => '+1 month', 'quarterly' => '+3 months', 'semi_annual' => '+6 months', 'annually' => '+1 year', 'biennially' => '+2 years'][$cycle] ?? '+1 month';
    }
}

