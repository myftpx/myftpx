<?php
namespace App\Controllers;

class AdminController extends Controller
{
    private function guard(): void
    {
        auth()->requireAdmin();
    }

    public function showLogin(): void
    {
        if (auth()->checkAdmin()) redirect(url('admin'));
        echo $this->render('admin/login', ['title' => 'Yönetici Girişi'], 'admin-auth');
    }

    public function login(): void
    {
        $this->validateCsrf();
        $email = trim($this->input('email', ''));
        $password = (string)$this->input('password', '');
        $stmt = db()->prepare('SELECT * FROM admins WHERE email = ?');
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        if (!$admin || !password_verify($password, $admin['password'])) {
            flash('error', 'E-posta veya şifre hatalı.');
            redirect(url('admin/login'));
        }
        auth()->loginAdmin($admin);
        db()->prepare('UPDATE admins SET last_login = ? WHERE id = ?')->execute([now(), $admin['id']]);
        $this->log('admin_login', 'Yönetici girişi', null, (int)$admin['id']);
        redirect(url('admin'));
    }

    public function logout(): void
    {
        auth()->logoutAdmin();
        flash('success', 'Çıkış yaptınız.');
        redirect(url('admin/login'));
    }

    public function dashboard(): void
    {
        $this->guard();
        $stats = [];
        $stats['clients'] = db()->query('SELECT COUNT(*) c FROM users')->fetch()['c'];
        $stats['services'] = db()->query("SELECT COUNT(*) c FROM services WHERE status='active'")->fetch()['c'];
        $stats['domains'] = db()->query('SELECT COUNT(*) c FROM domains')->fetch()['c'];
        $stats['open_tickets'] = db()->query("SELECT COUNT(*) c FROM tickets WHERE status IN ('open','answered')")->fetch()['c'];
        $stats['unpaid_invoices'] = db()->query("SELECT COUNT(*) c FROM invoices WHERE status='unpaid'")->fetch()['c'];
        $stats['revenue'] = db()->query("SELECT COALESCE(SUM(total),0) s FROM invoices WHERE status='paid'")->fetch()['s'];
        $stats['pending_orders'] = db()->query("SELECT COUNT(*) c FROM orders WHERE status='pending'")->fetch()['c'];

        $recentTickets = db()->query('SELECT t.*, u.first_name, u.last_name FROM tickets t LEFT JOIN users u ON u.id=t.user_id ORDER BY t.last_reply_at DESC LIMIT 6')->fetchAll();
        $recentInvoices = db()->query('SELECT i.*, u.first_name, u.last_name FROM invoices i LEFT JOIN users u ON u.id=i.user_id ORDER BY i.id DESC LIMIT 6')->fetchAll();
        $recentOrders = db()->query('SELECT o.*, u.first_name, u.last_name, p.name pname FROM orders o LEFT JOIN users u ON u.id=o.user_id LEFT JOIN products p ON p.id=o.product_id ORDER BY o.id DESC LIMIT 6')->fetchAll();

        echo $this->render('admin/dashboard', [
            'title' => 'Yönetim Paneli',
            'stats' => $stats,
            'recentTickets' => $recentTickets,
            'recentInvoices' => $recentInvoices,
            'recentOrders' => $recentOrders,
        ], 'admin');
    }
    public function clients(): void
    {
        $this->guard();
        $q = trim($this->input('q', ''));
        if ($q !== '') {
            $stmt = db()->prepare('SELECT * FROM users WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? ORDER BY id DESC');
            $like = '%' . $q . '%';
            $stmt->execute([$like, $like, $like]);
            $clients = $stmt->fetchAll();
        } else {
            $clients = db()->query('SELECT * FROM users ORDER BY id DESC')->fetchAll();
        }
        echo $this->render('admin/clients', ['title' => 'Müşteriler', 'clients' => $clients, 'q' => $q], 'admin');
    }

    public function clientDetail(string $id): void
    {
        $this->guard();
        $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $client = $stmt->fetch();
        if (!$client) { http_response_code(404); echo $this->render('errors/404', ['title' => 'Bulunamadı'], 'admin'); return; }

        $services = db()->prepare('SELECT s.*, p.name pname FROM services s LEFT JOIN products p ON p.id=s.product_id WHERE s.user_id=? ORDER BY s.id DESC');
        $services->execute([$id]);
        $invoices = db()->prepare('SELECT * FROM invoices WHERE user_id=? ORDER BY id DESC LIMIT 20');
        $invoices->execute([$id]);
        $tickets = db()->prepare('SELECT * FROM tickets WHERE user_id=? ORDER BY id DESC LIMIT 20');
        $tickets->execute([$id]);
        $domains = db()->prepare('SELECT * FROM domains WHERE user_id=? ORDER BY id DESC');
        $domains->execute([$id]);
        $keys = db()->prepare('SELECT * FROM api_keys WHERE user_id=? ORDER BY id DESC');
        $keys->execute([$id]);

        echo $this->render('admin/client_detail', [
            'title' => $client['first_name'] . ' ' . $client['last_name'],
            'client' => $client,
            'services' => $services->fetchAll(),
            'invoices' => $invoices->fetchAll(),
            'tickets' => $tickets->fetchAll(),
            'domains' => $domains->fetchAll(),
            'keys' => $keys->fetchAll(),
        ], 'admin');
    }

    public function clientUpdate(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        $fields = ['first_name', 'last_name', 'email', 'phone', 'company', 'city', 'country', 'status'];
        $data = [];
        foreach ($fields as $f) $data[] = trim($this->input($f, ''));
        $data[] = $id;
        db()->prepare('UPDATE users SET first_name=?, last_name=?, email=?, phone=?, company=?, city=?, country=?, status=? WHERE id=?')->execute($data);
        flash('success', 'Müşteri güncellendi.');
        redirect(url('admin/clients/' . $id));
    }

    public function clientDelete(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        db()->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
        flash('success', 'Müşteri silindi.');
        redirect(url('admin/clients'));
    }

    public function clientBalance(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        $amount = (float)$this->input('amount', 0);
        $op = $this->input('op', 'add');
        $stmt = db()->prepare($op === 'set' ? 'UPDATE users SET balance = ? WHERE id = ?' : 'UPDATE users SET balance = balance + ? WHERE id = ?');
        $stmt->execute([$op === 'subtract' ? -$amount : $amount, $id]);
        flash('success', 'Bakiye güncellendi.');
        redirect(url('admin/clients/' . $id));
    }

    public function loginAs(string $id): void
    {
        $this->guard();
        $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $client = $stmt->fetch();
        if ($client) {
            auth()->login($client);
            $this->log('login_as', 'Yönetici müşteri olarak giriş yaptı', (int)$id, auth()->admin()['id'] ?? null);
            flash('success', $client['first_name'] . ' olarak giriş yaptınız.');
            redirect(url('client'));
        }
        redirect(url('admin/clients'));
    }
    public function products(): void
    {
        $this->guard();
        $products = db()->query('SELECT * FROM products ORDER BY sort_order, id')->fetchAll();
        echo $this->render('admin/products', ['title' => 'Ürünler', 'products' => $products], 'admin');
    }

    public function productNew(): void
    {
        $this->guard();
        echo $this->render('admin/product_form', [
            'title' => 'Yeni Ürün',
            'product' => null,
            'options' => [],
            'modules' => db()->query("SELECT * FROM modules WHERE type='server'")->fetchAll(),
        ], 'admin');
    }

    public function productCreate(): void
    {
        $this->guard(); $this->validateCsrf();
        $this->saveProduct(null);
        flash('success', 'Ürün oluşturuldu.');
        redirect(url('admin/products'));
    }

    public function productEdit(string $id): void
    {
        $this->guard();
        $stmt = db()->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        if (!$product) { http_response_code(404); echo $this->render('errors/404', ['title' => 'Bulunamadı'], 'admin'); return; }
        $stmt = db()->prepare('SELECT * FROM product_config_options WHERE product_id = ? ORDER BY sort_order');
        $stmt->execute([$id]);
        $options = $stmt->fetchAll();
        echo $this->render('admin/product_form', [
            'title' => 'Ürünü Düzenle',
            'product' => $product,
            'options' => $options,
            'modules' => db()->query("SELECT * FROM modules WHERE type='server'")->fetchAll(),
        ], 'admin');
    }

    public function productUpdate(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        $this->saveProduct((int)$id);
        flash('success', 'Ürün güncellendi.');
        redirect(url('admin/products'));
    }

    public function productDelete(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        db()->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
        flash('success', 'Ürün silindi.');
        redirect(url('admin/products'));
    }

    private function saveProduct(?int $id): void
    {
        $name = trim($this->input('name', ''));
        $data = [
            'name' => $name,
            'description' => $this->input('description', ''),
            'category' => trim($this->input('category', '')),
            'type' => $this->input('type', 'hosting'),
            'price' => (float)$this->input('price', 0),
            'setup_fee' => (float)$this->input('setup_fee', 0),
            'billing_cycle' => $this->input('billing_cycle', 'monthly'),
            'status' => $this->input('status', 'active'),
            'module' => $this->input('module', 'none'),
            'featured' => (int)$this->input('featured', 0),
        ];

        if ($id === null) {
            $stmt = db()->prepare('INSERT INTO products (name, slug, description, category, type, price, setup_fee, billing_cycle, status, module, featured) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$name, slug($name . '-' . uniqid()), $data['description'], $data['category'], $data['type'], $data['price'], $data['setup_fee'], $data['billing_cycle'], $data['status'], $data['module'], $data['featured']]);
            $id = (int)db()->lastInsertId();
        } else {
            $stmt = db()->prepare('UPDATE products SET name=?, description=?, category=?, type=?, price=?, setup_fee=?, billing_cycle=?, status=?, module=?, featured=? WHERE id=?');
            $stmt->execute([$name, $data['description'], $data['category'], $data['type'], $data['price'], $data['setup_fee'], $data['billing_cycle'], $data['status'], $data['module'], $data['featured'], $id]);
        }

        // Config options
        db()->prepare('DELETE FROM product_config_options WHERE product_id = ?')->execute([$id]);
        $names = $this->input('opt_name', []) ?: [];
        $types = $this->input('opt_type', []) ?: [];
        $required = $this->input('opt_required', []) ?: [];
        foreach ($names as $i => $n) {
            if (trim((string)$n) === '') continue;
            $opts = $this->input('opt_options', []) ?: [];
            $optionList = array_filter(array_map('trim', explode(',', (string)($opts[$i] ?? ''))));
            db()->prepare('INSERT INTO product_config_options (product_id, name, type, options, required, sort_order) VALUES (?, ?, ?, ?, ?, ?)')
                ->execute([$id, trim($n), $types[$i] ?? 'dropdown', json_encode($optionList), isset($required[$i]) ? 1 : 0, $i]);
        }
    }
    public function orders(): void
    {
        $this->guard();
        $orders = db()->query('SELECT o.*, u.first_name, u.last_name, p.name pname FROM orders o LEFT JOIN users u ON u.id=o.user_id LEFT JOIN products p ON p.id=o.product_id ORDER BY o.id DESC')->fetchAll();
        echo $this->render('admin/orders', ['title' => 'Siparişler', 'orders' => $orders], 'admin');
    }

    public function orderDetail(string $id): void
    {
        $this->guard();
        $stmt = db()->prepare('SELECT o.*, u.first_name, u.last_name, p.name pname FROM orders o LEFT JOIN users u ON u.id=o.user_id LEFT JOIN products p ON p.id=o.product_id WHERE o.id=?');
        $stmt->execute([$id]);
        $order = $stmt->fetch();
        if (!$order) { http_response_code(404); echo $this->render('errors/404', ['title' => 'Bulunamadı'], 'admin'); return; }
        echo $this->render('admin/order_detail', ['title' => $order['order_number'], 'order' => $order], 'admin');
    }

    public function orderUpdate(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        $status = $this->input('status', 'pending');
        $stmt = db()->prepare('SELECT * FROM orders WHERE id = ?');
        $stmt->execute([$id]);
        $order = $stmt->fetch();
        if ($order) {
            db()->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$status, $id]);
            if ($status === 'active') {
                $stmt = db()->prepare('SELECT * FROM products WHERE id = ?');
                $stmt->execute([$order['product_id']]);
                $product = $stmt->fetch();
                if ($product) {
                    $config = json_decode($order['config'], true) ?: [];
                    (new StoreController())->activateOrder($order['order_number'], (int)$order['user_id'], $product, $order['billing_cycle'], $order['domain'], $config, (float)$order['amount']);
                }
            }
        }
        flash('success', 'Sipariş durumu güncellendi.');
        redirect(url('admin/orders/' . $id));
    }

    public function services(): void
    {
        $this->guard();
        $services = db()->query('SELECT s.*, u.first_name, u.last_name, p.name pname FROM services s LEFT JOIN users u ON u.id=s.user_id LEFT JOIN products p ON p.id=s.product_id ORDER BY s.id DESC')->fetchAll();
        echo $this->render('admin/services', ['title' => 'Hizmetler', 'services' => $services], 'admin');
    }

    public function serviceDetail(string $id): void
    {
        $this->guard();
        $stmt = db()->prepare('SELECT s.*, u.first_name, u.last_name, p.name pname, p.module FROM services s LEFT JOIN users u ON u.id=s.user_id LEFT JOIN products p ON p.id=s.product_id WHERE s.id=?');
        $stmt->execute([$id]);
        $service = $stmt->fetch();
        if (!$service) { http_response_code(404); echo $this->render('errors/404', ['title' => 'Bulunamadı'], 'admin'); return; }
        $service['config'] = json_decode($service['config'], true) ?: [];
        echo $this->render('admin/service_detail', ['title' => $service['domain'] ?: $service['pname'], 'service' => $service], 'admin');
    }

    public function serviceUpdate(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        $status = $this->input('status', 'active');
        $domain = trim($this->input('domain', ''));
        $username = trim($this->input('username', ''));
        $password = $this->input('password', '');
        $nextDue = $this->input('next_due_date', '');
        $stmt = db()->prepare('UPDATE services SET status=?, domain=?, username=?, next_due_date=? WHERE id=?');
        $stmt->execute([$status, $domain, $username, $nextDue ?: null, $id]);
        if ($password !== '') {
            db()->prepare('UPDATE services SET password=? WHERE id=?')->execute([$password, $id]);
        }
        flash('success', 'Hizmet güncellendi.');
        redirect(url('admin/services/' . $id));
    }
    public function invoices(): void
    {
        $this->guard();
        $invoices = db()->query('SELECT i.*, u.first_name, u.last_name FROM invoices i LEFT JOIN users u ON u.id=i.user_id ORDER BY i.id DESC')->fetchAll();
        echo $this->render('admin/invoices', ['title' => 'Faturalar', 'invoices' => $invoices], 'admin');
    }

    public function invoiceNew(): void
    {
        $this->guard();
        $clients = db()->query('SELECT id, first_name, last_name, email FROM users ORDER BY first_name')->fetchAll();
        echo $this->render('admin/invoice_new', ['title' => 'Yeni Fatura', 'clients' => $clients], 'admin');
    }

    public function invoiceCreate(): void
    {
        $this->guard(); $this->validateCsrf();
        $userId = (int)$this->input('user_id', 0);
        $descriptions = $this->input('description', []) ?: [];
        $amounts = $this->input('amount', []) ?: [];
        $items = [];
        foreach ($descriptions as $i => $d) {
            if (trim((string)$d) === '') continue;
            $items[] = ['description' => trim($d), 'amount' => (float)($amounts[$i] ?? 0)];
        }
        if (!$userId || !$items) {
            flash('error', 'Müşteri ve en az bir kalem gerekli.');
            redirect(url('admin/invoices/new'));
        }
        (new StoreController())->createInvoice($userId, $items);
        flash('success', 'Fatura oluşturuldu.');
        redirect(url('admin/invoices'));
    }

    public function invoiceDetail(string $id): void
    {
        $this->guard();
        $stmt = db()->prepare('SELECT i.*, u.first_name, u.last_name, u.email FROM invoices i LEFT JOIN users u ON u.id=i.user_id WHERE i.id=?');
        $stmt->execute([$id]);
        $invoice = $stmt->fetch();
        if (!$invoice) { http_response_code(404); echo $this->render('errors/404', ['title' => 'Bulunamadı'], 'admin'); return; }
        $stmt = db()->prepare('SELECT * FROM invoice_items WHERE invoice_id = ?');
        $stmt->execute([$id]);
        echo $this->render('admin/invoice_detail', ['title' => $invoice['invoice_number'], 'invoice' => $invoice, 'items' => $stmt->fetchAll()], 'admin');
    }

    public function invoiceMarkPaid(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        (new StoreController())->markInvoicePaid((int)$id, 'admin');
        flash('success', 'Fatura ödendi olarak işaretlendi.');
        redirect(url('admin/invoices/' . $id));
    }

    public function invoiceDelete(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        db()->prepare('DELETE FROM invoice_items WHERE invoice_id = ?')->execute([$id]);
        db()->prepare('DELETE FROM invoices WHERE id = ?')->execute([$id]);
        flash('success', 'Fatura silindi.');
        redirect(url('admin/invoices'));
    }
    public function tickets(): void
    {
        $this->guard();
        $status = $this->input('status', '');
        if ($status) {
            $stmt = db()->prepare('SELECT t.*, u.first_name, u.last_name FROM tickets t LEFT JOIN users u ON u.id=t.user_id WHERE t.status=? ORDER BY t.last_reply_at DESC');
            $stmt->execute([$status]);
            $tickets = $stmt->fetchAll();
        } else {
            $tickets = db()->query('SELECT t.*, u.first_name, u.last_name FROM tickets t LEFT JOIN users u ON u.id=t.user_id ORDER BY t.last_reply_at DESC')->fetchAll();
        }
        echo $this->render('admin/tickets', ['title' => 'Destek Biletleri', 'tickets' => $tickets, 'status' => $status], 'admin');
    }

    public function ticketDetail(string $id): void
    {
        $this->guard();
        $stmt = db()->prepare('SELECT t.*, u.first_name, u.last_name, u.email FROM tickets t LEFT JOIN users u ON u.id=t.user_id WHERE t.id=?');
        $stmt->execute([$id]);
        $ticket = $stmt->fetch();
        if (!$ticket) { http_response_code(404); echo $this->render('errors/404', ['title' => 'Bulunamadı'], 'admin'); return; }
        $stmt = db()->prepare('SELECT r.*, u.first_name, u.last_name, a.name admin_name FROM ticket_replies r LEFT JOIN users u ON u.id=r.user_id LEFT JOIN admins a ON a.id=r.user_id WHERE r.ticket_id=? ORDER BY r.id ASC');
        $stmt->execute([$id]);
        echo $this->render('admin/ticket_detail', ['title' => '#' . $ticket['ticket_number'], 'ticket' => $ticket, 'replies' => $stmt->fetchAll()], 'admin');
    }

    public function ticketReply(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        $message = trim($this->input('message', ''));
        if ($message === '') redirect(url('admin/tickets/' . $id));
        $adminId = auth()->admin()['id'] ?? null;
        db()->prepare('INSERT INTO ticket_replies (ticket_id, user_id, is_admin, message) VALUES (?, ?, 1, ?)')->execute([$id, $adminId, $message]);
        db()->prepare('UPDATE tickets SET status="answered", last_reply_at=? WHERE id=?')->execute([now(), $id]);
        flash('success', 'Yanıt gönderildi.');
        redirect(url('admin/tickets/' . $id));
    }

    public function ticketStatus(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        $status = $this->input('status', 'open');
        db()->prepare('UPDATE tickets SET status=? WHERE id=?')->execute([$status, $id]);
        redirect(url('admin/tickets/' . $id));
    }
    public function domains(): void
    {
        $this->guard();
        $domains = db()->query('SELECT d.*, u.first_name, u.last_name FROM domains d LEFT JOIN users u ON u.id=d.user_id ORDER BY d.id DESC')->fetchAll();
        echo $this->render('admin/domains', ['title' => 'Alan Adları', 'domains' => $domains], 'admin');
    }

    public function domainDetail(string $id): void
    {
        $this->guard();
        $stmt = db()->prepare('SELECT d.*, u.first_name, u.last_name FROM domains d LEFT JOIN users u ON u.id=d.user_id WHERE d.id=?');
        $stmt->execute([$id]);
        $domain = $stmt->fetch();
        if (!$domain) { http_response_code(404); echo $this->render('errors/404', ['title' => 'Bulunamadı'], 'admin'); return; }
        echo $this->render('admin/domain_detail', ['title' => $domain['domain'], 'domain' => $domain], 'admin');
    }

    public function domainUpdate(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        $stmt = db()->prepare('UPDATE domains SET registrar=?, status=?, expiry_date=?, registration_period=? WHERE id=?');
        $stmt->execute([
            trim($this->input('registrar', '')),
            $this->input('status', 'active'),
            $this->input('expiry_date', '') ?: null,
            (int)$this->input('registration_period', 1),
            $id,
        ]);
        flash('success', 'Alan adı güncellendi.');
        redirect(url('admin/domains/' . $id));
    }

    public function domainDelete(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        db()->prepare('DELETE FROM domains WHERE id = ?')->execute([$id]);
        flash('success', 'Alan adı silindi.');
        redirect(url('admin/domains'));
    }

    public function transactions(): void
    {
        $this->guard();
        $transactions = db()->query('SELECT t.*, u.first_name, u.last_name FROM transactions t LEFT JOIN users u ON u.id=t.user_id ORDER BY t.id DESC')->fetchAll();
        echo $this->render('admin/transactions', ['title' => 'İşlemler', 'transactions' => $transactions], 'admin');
    }
    public function payments(): void
    {
        $this->guard();
        $gateways = db()->query('SELECT * FROM payment_gateways ORDER BY sort_order')->fetchAll();
        foreach ($gateways as &$g) $g['config'] = json_decode($g['config'], true) ?: [];
        echo $this->render('admin/payments', ['title' => 'Ödeme Yöntemleri', 'gateways' => $gateways], 'admin');
    }

    public function paymentsSave(string $code): void
    {
        $this->guard(); $this->validateCsrf();
        $enabled = (int)$this->input('enabled', 0);
        $fields = (array)$this->input('config', []);
        $stmt = db()->prepare('UPDATE payment_gateways SET enabled = ?, config = ? WHERE code = ?');
        $stmt->execute([$enabled, json_encode($fields), $code]);
        flash('success', 'Ödeme yöntemi güncellendi.');
        redirect(url('admin/payments'));
    }

    public function modules(): void
    {
        $this->guard();
        $modules = db()->query('SELECT * FROM modules ORDER BY id')->fetchAll();
        foreach ($modules as &$m) $m['config'] = json_decode($m['config'], true) ?: [];
        echo $this->render('admin/modules', ['title' => 'Modüller & Entegrasyonlar', 'modules' => $modules], 'admin');
    }

    public function modulesSave(string $code): void
    {
        $this->guard(); $this->validateCsrf();
        $enabled = (int)$this->input('enabled', 0);
        $fields = (array)$this->input('config', []);
        $stmt = db()->prepare('UPDATE modules SET enabled = ?, config = ? WHERE code = ?');
        $stmt->execute([$enabled, json_encode($fields), $code]);
        flash('success', 'Modül güncellendi.');
        redirect(url('admin/modules'));
    }

    public function settings(): void
    {
        $this->guard();
        $themes = ['rcvxtrwhite' => 'RCVXTRWHITE', 'rcvxtrdark' => 'RCVXTRDARK'];
        echo $this->render('admin/settings', ['title' => 'Genel Ayarlar', 'themes' => $themes], 'admin');
    }

    public function settingsSave(): void
    {
        $this->guard(); $this->validateCsrf();
        $keys = ['site_name', 'theme', 'currency', 'admin_email', 'tax_rate', 'invoice_prefix', 'default_language', 'api_enabled', 'allow_registration', 'maintenance_mode', 'support_email', 'terms_url', 'privacy_url'];
        foreach ($keys as $k) {
            set_setting($k, $this->input($k, ''));
        }
        // Checkbox fields
        set_setting('api_enabled', isset($_POST['api_enabled']) ? '1' : '0');
        set_setting('allow_registration', isset($_POST['allow_registration']) ? '1' : '0');
        set_setting('maintenance_mode', isset($_POST['maintenance_mode']) ? '1' : '0');
        flash('success', 'Ayarlar kaydedildi.');
        redirect(url('admin/settings'));
    }

    public function reports(): void
    {
        $this->guard();
        $monthly = db()->query("SELECT strftime('%Y-%m', paid_at) ym, SUM(total) s FROM invoices WHERE status='paid' AND paid_at IS NOT NULL GROUP BY ym ORDER BY ym DESC LIMIT 12")->fetchAll();
        $byGateway = db()->query("SELECT gateway, COUNT(*) c, SUM(amount) s FROM transactions WHERE status='completed' GROUP BY gateway")->fetchAll();
        echo $this->render('admin/reports', ['title' => 'Raporlar', 'monthly' => $monthly, 'byGateway' => $byGateway], 'admin');
    }
}






