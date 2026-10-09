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
        // Admin OTP (2FA)
        $code = \App\Core\Otp::generate($email, 'admin');
        \App\Core\Otp::sendEmail($email, $code);
        $_SESSION['pending_admin_email'] = $email;
        flash('info', 'Yönetici girişi için doğrulama kodu e-posta adresinize gönderildi.');
        redirect(url('admin/verify'));
    }

    public function showVerify(): void
    {
        if (empty($_SESSION['pending_admin_email'])) redirect(url('admin/login'));
        echo $this->render('admin/verify', ['title' => 'Yönetici Doğrulama', 'email' => $_SESSION['pending_admin_email']], 'admin-auth');
    }

    public function verify(): void
    {
        $this->validateCsrf();
        $email = $_SESSION['pending_admin_email'] ?? '';
        $code = trim($this->input('code', ''));
        if ($email === '' || !\App\Core\Otp::verify($email, $code, 'admin')) {
            flash('error', 'Doğrulama kodu hatalı veya süresi dolmuş.');
            redirect(url('admin/verify'));
        }
        $stmt = db()->prepare('SELECT * FROM admins WHERE email = ?');
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        unset($_SESSION['pending_admin_email']);
        if (!$admin) redirect(url('admin/login'));

        auth()->loginAdmin($admin);
        db()->prepare('UPDATE admins SET last_login = ? WHERE id = ?')->execute([now(), $admin['id']]);
        $this->log('admin_login', 'Yönetici girişi (OTP doğrulandı)', null, (int)$admin['id']);
        redirect(url('admin'));
    }

    public function admins(): void
    {
        $this->guard();
        $admins = db()->query('SELECT id, name, email, role, last_login, created_at FROM admins ORDER BY id')->fetchAll();
        echo $this->render('admin/admins', ['title' => 'Yöneticiler', 'admins' => $admins], 'admin');
    }

    public function adminAdd(): void
    {
        $this->guard(); $this->validateCsrf();
        $name = trim($this->input('name', ''));
        $email = trim($this->input('email', ''));
        $password = (string)$this->input('password', '');
        $role = $this->input('role', 'admin');
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
            flash('error', 'Geçerli bilgiler girin (şifre en az 6 karakter).');
            redirect(url('admin/admins'));
        }
        db()->prepare('INSERT INTO admins (name, email, password, role) VALUES (?, ?, ?, ?)')
            ->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
        flash('success', 'Yönetici eklendi.');
        redirect(url('admin/admins'));
    }

    public function adminDelete(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        if ((int)$id === (int)(auth()->admin()['id'] ?? 0)) {
            flash('error', 'Kendi hesabınızı silemezsiniz.');
            redirect(url('admin/admins'));
        }
        db()->prepare('DELETE FROM admins WHERE id = ?')->execute([$id]);
        flash('success', 'Yönetici silindi.');
        redirect(url('admin/admins'));
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
        $keys = ['site_name', 'theme', 'currency', 'admin_email', 'tax_rate', 'invoice_prefix', 'default_language', 'api_enabled', 'allow_registration', 'maintenance_mode', 'support_email', 'terms_url', 'privacy_url',
                 'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_from_email', 'smtp_from_name', 'smtp_encryption', 'sms_gateway', 'sms_api_key', 'sms_api_secret', 'sms_sender', 'mail_method'];
        foreach ($keys as $k) {
            set_setting($k, $this->input($k, ''));
        }
        // Checkbox fields
        set_setting('api_enabled', isset($_POST['api_enabled']) ? '1' : '0');
        set_setting('allow_registration', isset($_POST['allow_registration']) ? '1' : '0');
        set_setting('maintenance_mode', isset($_POST['maintenance_mode']) ? '1' : '0');
        set_setting('sms_enabled', isset($_POST['sms_enabled']) ? '1' : '0');
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

    public function testMail(): void
    {
        $this->guard(); $this->validateCsrf();
        $email = trim($this->input('email', ''));
        if ($email === '') {
            $this->json(['success' => false, 'message' => 'E-posta girin.']);
        }
        $ok = \App\Core\Mailer::send($email, 'Test E-postası — ' . setting('site_name', 'RCVXTR'), "Bu bir test e-postasıdır.\n\nMail sisteminiz çalışıyor!");
        if ($ok) {
            $this->json(['success' => true, 'message' => 'E-posta gönderildi. Gelen kutusu/spam klasörünü kontrol edin.']);
        }
        $this->json(['success' => false, 'message' => 'Gönderilemedi. SMTP ayarlarını veya "Log" yöntemini deneyin.']);
    }

    public function bankAccounts(): void
    {
        $this->guard();
        $accounts = db()->query('SELECT * FROM bank_accounts ORDER BY sort_order, id')->fetchAll();
        echo $this->render('admin/bank_accounts', ['title' => 'Banka Hesapları', 'accounts' => $accounts], 'admin');
    }

    public function bankAccountAdd(): void
    {
        $this->guard(); $this->validateCsrf();
        $stmt = db()->prepare('INSERT INTO bank_accounts (bank_name, account_holder, iban, account_no, branch_code) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            trim($this->input('bank_name', '')),
            trim($this->input('account_holder', '')),
            trim($this->input('iban', '')),
            trim($this->input('account_no', '')),
            trim($this->input('branch_code', '')),
        ]);
        flash('success', 'Banka hesabı eklendi.');
        redirect(url('admin/bank-accounts'));
    }

    public function bankAccountDelete(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        db()->prepare('DELETE FROM bank_accounts WHERE id = ?')->execute([$id]);
        flash('success', 'Banka hesabı silindi.');
        redirect(url('admin/bank-accounts'));
    }

    public function bankAccountToggle(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        db()->prepare('UPDATE bank_accounts SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END WHERE id = ?')->execute([$id]);
        redirect(url('admin/bank-accounts'));
    }

    public function paymentLogs(): void
    {
        $this->guard();
        $logs = db()->query('SELECT l.*, u.first_name, u.last_name, u.email FROM payment_logs l LEFT JOIN users u ON u.id = l.user_id ORDER BY l.id DESC LIMIT 500')->fetchAll();
        echo $this->render('admin/payment_logs', ['title' => 'Ödeme Kayıtları (Loglar)', 'logs' => $logs], 'admin');
    }

    public function transactionConfirm(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        $stmt = db()->prepare('SELECT * FROM transactions WHERE id = ?');
        $stmt->execute([$id]);
        $tx = $stmt->fetch();
        if ($tx && $tx['status'] === 'pending') {
            db()->prepare('UPDATE transactions SET status = "completed" WHERE id = ?')->execute([$id]);
            if ($tx['invoice_id']) {
                (new StoreController())->markInvoicePaid((int)$tx['invoice_id'], $tx['gateway'] ?: 'bank_transfer');
            } else {
                // Balance top-up (no invoice) — credit the user's balance
                db()->prepare('UPDATE users SET balance = balance + ? WHERE id = ?')->execute([$tx['amount'], $tx['user_id']]);
            }
            payment_log($tx['user_id'], $tx['invoice_id'], $tx['gateway'] ?: 'bank_transfer', 'bank_transfer.confirmed', 'success', 'Havale yönetici tarafından onaylandı.', (float)$tx['amount'], $tx['transaction_id']);
            flash('success', 'Havale onaylandı' . ($tx['invoice_id'] ? ', fatura ödendi.' : ', bakiye eklendi.'));
        }
        redirect(url('admin/transactions'));
    }

    public function transactionDeny(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        $stmt = db()->prepare('SELECT * FROM transactions WHERE id = ?');
        $stmt->execute([$id]);
        $tx = $stmt->fetch();
        if ($tx && $tx['status'] === 'pending') {
            db()->prepare('UPDATE transactions SET status = "failed" WHERE id = ?')->execute([$id]);
            payment_log($tx['user_id'], $tx['invoice_id'], $tx['gateway'] ?: 'bank_transfer', 'bank_transfer.denied', 'failed', 'Havale yönetici tarafından reddedildi.', (float)$tx['amount'], $tx['transaction_id']);
            flash('info', 'Havale reddedildi.');
        }
        redirect(url('admin/transactions'));
    }

    public function announcements(): void
    {
        $this->guard();
        $items = db()->query('SELECT * FROM announcements ORDER BY published_at DESC')->fetchAll();
        echo $this->render('admin/announcements', ['title' => 'Duyurular', 'items' => $items], 'admin');
    }

    public function announcementAdd(): void
    {
        $this->guard(); $this->validateCsrf();
        db()->prepare('INSERT INTO announcements (title, body, status) VALUES (?, ?, ?)')
            ->execute([trim($this->input('title', '')), $this->input('body', ''), (int)$this->input('status', 1)]);
        flash('success', 'Duyuru eklendi.');
        redirect(url('admin/announcements'));
    }

    public function announcementDelete(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        db()->prepare('DELETE FROM announcements WHERE id = ?')->execute([$id]);
        flash('success', 'Duyuru silindi.');
        redirect(url('admin/announcements'));
    }

    public function kb(): void
    {
        $this->guard();
        $categories = db()->query('SELECT c.*, (SELECT COUNT(*) FROM kb_articles a WHERE a.category_id = c.id) cnt FROM kb_categories c ORDER BY c.sort_order, c.id')->fetchAll();
        $articles = db()->query('SELECT a.*, c.name cat FROM kb_articles a LEFT JOIN kb_categories c ON c.id = a.category_id ORDER BY a.id DESC')->fetchAll();
        echo $this->render('admin/kb', ['title' => 'Bilgi Bankası', 'categories' => $categories, 'articles' => $articles], 'admin');
    }

    public function kbCategoryAdd(): void
    {
        $this->guard(); $this->validateCsrf();
        db()->prepare('INSERT INTO kb_categories (name, description) VALUES (?, ?)')->execute([trim($this->input('name', '')), $this->input('description', '')]);
        flash('success', 'Kategori eklendi.');
        redirect(url('admin/kb'));
    }

    public function kbCategoryDelete(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        db()->prepare('DELETE FROM kb_categories WHERE id = ?')->execute([$id]);
        flash('success', 'Kategori silindi.');
        redirect(url('admin/kb'));
    }

    public function kbArticleAdd(): void
    {
        $this->guard(); $this->validateCsrf();
        db()->prepare('INSERT INTO kb_articles (category_id, title, body, status) VALUES (?, ?, ?, ?)')
            ->execute([(int)$this->input('category_id', 0), trim($this->input('title', '')), $this->input('body', ''), (int)$this->input('status', 1)]);
        flash('success', 'Makale eklendi.');
        redirect(url('admin/kb'));
    }

    public function kbArticleDelete(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        db()->prepare('DELETE FROM kb_articles WHERE id = ?')->execute([$id]);
        flash('success', 'Makale silindi.');
        redirect(url('admin/kb'));
    }
    public function tldPricing(): void
    {
        $this->guard();
        $tlds = db()->query('SELECT * FROM tld_pricing ORDER BY tld')->fetchAll();
        echo $this->render('admin/tld', ['title' => 'TLD Fiyatlandırma', 'tlds' => $tlds], 'admin');
    }

    public function tldAdd(): void
    {
        $this->guard(); $this->validateCsrf();
        $tld = '.' . ltrim(trim($this->input('tld', '')), '.');
        db()->prepare('INSERT OR IGNORE INTO tld_pricing (tld, register_price, transfer_price, renew_price, status) VALUES (?, ?, ?, ?, ?)')
            ->execute([$tld, (float)$this->input('register_price', 0), (float)$this->input('transfer_price', 0), (float)$this->input('renew_price', 0), 1]);
        flash('success', 'TLD eklendi.');
        redirect(url('admin/tld'));
    }

    public function tldDelete(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        db()->prepare('DELETE FROM tld_pricing WHERE id = ?')->execute([$id]);
        flash('success', 'TLD silindi.');
        redirect(url('admin/tld'));
    }

    public function addons(): void
    {
        $this->guard();
        $addons = db()->query('SELECT * FROM addons ORDER BY id DESC')->fetchAll();
        echo $this->render('admin/addons', ['title' => 'Ürün Eklentileri', 'addons' => $addons], 'admin');
    }

    public function addonAdd(): void
    {
        $this->guard(); $this->validateCsrf();
        db()->prepare('INSERT INTO addons (name, description, price, billing_cycle, status) VALUES (?, ?, ?, ?, ?)')
            ->execute([trim($this->input('name', '')), $this->input('description', ''), (float)$this->input('price', 0), $this->input('billing_cycle', 'monthly'), 1]);
        flash('success', 'Eklenti oluşturuldu.');
        redirect(url('admin/addons'));
    }

    public function addonDelete(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        db()->prepare('DELETE FROM addons WHERE id = ?')->execute([$id]);
        flash('success', 'Eklenti silindi.');
        redirect(url('admin/addons'));
    }

    public function promotions(): void
    {
        $this->guard();
        $promos = db()->query('SELECT * FROM promotions ORDER BY id DESC')->fetchAll();
        echo $this->render('admin/promotions', ['title' => 'Promosyonlar / Kuponlar', 'promos' => $promos], 'admin');
    }

    public function promotionAdd(): void
    {
        $this->guard(); $this->validateCsrf();
        db()->prepare('INSERT INTO promotions (code, discount_type, discount_value, applies_to, valid_from, valid_until, max_uses, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([
                strtoupper(trim($this->input('code', ''))),
                $this->input('discount_type', 'percent'),
                (float)$this->input('discount_value', 0),
                $this->input('applies_to', 'all'),
                $this->input('valid_from', '') ?: null,
                $this->input('valid_until', '') ?: null,
                (int)$this->input('max_uses', 0),
                1,
            ]);
        flash('success', 'Promosyon oluşturuldu.');
        redirect(url('admin/promotions'));
    }

    public function promotionDelete(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        db()->prepare('DELETE FROM promotions WHERE id = ?')->execute([$id]);
        flash('success', 'Promosyon silindi.');
        redirect(url('admin/promotions'));
    }

    public function emailTemplates(): void
    {
        $this->guard();
        $templates = db()->query('SELECT * FROM email_templates ORDER BY id')->fetchAll();
        echo $this->render('admin/email_templates', ['title' => 'E-posta Şablonları', 'templates' => $templates], 'admin');
    }

    public function emailTemplateSave(string $code): void
    {
        $this->guard(); $this->validateCsrf();
        db()->prepare('UPDATE email_templates SET subject = ?, body = ?, enabled = ? WHERE code = ?')
            ->execute([$this->input('subject', ''), $this->input('body', ''), (int)$this->input('enabled', 1), $code]);
        flash('success', 'Şablon güncellendi.');
        redirect(url('admin/email-templates'));
    }

    public function predefinedReplies(): void
    {
        $this->guard();
        $replies = db()->query('SELECT * FROM predefined_replies ORDER BY name')->fetchAll();
        echo $this->render('admin/predefined_replies', ['title' => 'Hazır Yanıtlar', 'replies' => $replies], 'admin');
    }

    public function predefinedReplyAdd(): void
    {
        $this->guard(); $this->validateCsrf();
        db()->prepare('INSERT INTO predefined_replies (name, body) VALUES (?, ?)')->execute([trim($this->input('name', '')), $this->input('body', '')]);
        flash('success', 'Hazır yanıt eklendi.');
        redirect(url('admin/predefined-replies'));
    }

    public function predefinedReplyDelete(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        db()->prepare('DELETE FROM predefined_replies WHERE id = ?')->execute([$id]);
        flash('success', 'Hazır yanıt silindi.');
        redirect(url('admin/predefined-replies'));
    }

    public function blog(): void
    {
        $this->guard();
        $categories = db()->query('SELECT * FROM blog_categories ORDER BY sort_order, id')->fetchAll();
        $posts = db()->query('SELECT p.*, c.name cat FROM blog_posts p LEFT JOIN blog_categories c ON c.id = p.category_id ORDER BY p.id DESC')->fetchAll();
        echo $this->render('admin/blog', ['title' => 'Blog Yönetimi', 'categories' => $categories, 'posts' => $posts], 'admin');
    }

    public function blogCategoryAdd(): void
    {
        $this->guard(); $this->validateCsrf();
        db()->prepare('INSERT INTO blog_categories (name, slug) VALUES (?, ?)')->execute([trim($this->input('name', '')), slug($this->input('name', ''))]);
        flash('success', 'Kategori eklendi.');
        redirect(url('admin/blog'));
    }

    public function blogCategoryDelete(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        db()->prepare('DELETE FROM blog_categories WHERE id = ?')->execute([$id]);
        flash('success', 'Kategori silindi.');
        redirect(url('admin/blog'));
    }

    public function blogPostAdd(): void
    {
        $this->guard(); $this->validateCsrf();
        $title = trim($this->input('title', ''));
        db()->prepare('INSERT INTO blog_posts (title, slug, category_id, excerpt, content, image, status, seo_title, seo_description, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([$title, slug($title . '-' . uniqid()), (int)$this->input('category_id', 0), $this->input('excerpt', ''), $this->input('content', ''), $this->input('image', ''), (int)$this->input('status', 1), $this->input('seo_title', ''), $this->input('seo_description', ''), now()]);
        flash('success', 'Blog yazısı eklendi.');
        redirect(url('admin/blog'));
    }

    public function blogPostDelete(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        db()->prepare('DELETE FROM blog_posts WHERE id = ?')->execute([$id]);
        flash('success', 'Blog yazısı silindi.');
        redirect(url('admin/blog'));
    }

    public function activityLog(): void
    {
        $this->guard();
        $logs = db()->query('SELECT l.*, u.first_name, u.last_name, a.name admin_name FROM activity_log l LEFT JOIN users u ON u.id = l.user_id LEFT JOIN admins a ON a.id = l.admin_id ORDER BY l.id DESC LIMIT 500')->fetchAll();
        echo $this->render('admin/activity_log', ['title' => 'Kullanıcı Hareket Logu', 'logs' => $logs], 'admin');
    }
    public function netlen(): void
    {
        $this->guard();
        $localDomains = db()->query("SELECT * FROM domains WHERE registrar = 'netlen' ORDER BY id DESC")->fetchAll();
        $balance = null;
        $domains = null;
        $error = null;
        $api = netlen();
        if ($api && $api->isEnabled()) {
            $b = $api->getBalance();
            if ($b['success']) $balance = $b['data'];
            $d = $api->listDomains();
            if ($d['success']) $domains = $d['data'];
            else $error = $d['message'];
        }
        echo $this->render('admin/netlen', ['title' => 'Netlen Bayilik (Domain)', 'localDomains' => $localDomains, 'balance' => $balance, 'domains' => $domains, 'error' => $error], 'admin');
    }

    public function netlenSave(): void
    {
        $this->guard(); $this->validateCsrf();
        set_setting('netlen_enabled', isset($_POST['netlen_enabled']) ? '1' : '0');
        set_setting('netlen_api_key', trim($this->input('netlen_api_key', '')));
        set_setting('netlen_api_url', trim($this->input('netlen_api_url', 'https://api.netlen.com.tr/v2')));
        set_setting('netlen_ns1', trim($this->input('netlen_ns1', 'ns1.netlen.com.tr')));
        set_setting('netlen_ns2', trim($this->input('netlen_ns2', 'ns2.netlen.com.tr')));
        flash('success', 'Netlen ayarları kaydedildi.');
        redirect(url('admin/netlen'));
    }

    public function netlenSync(): void
    {
        $this->guard(); $this->validateCsrf();
        $api = netlen();
        if (!$api || !$api->isEnabled()) {
            flash('error', 'Netlen modülü etkin değil veya API anahtarı eksik.');
            redirect(url('admin/netlen'));
        }
        $res = $api->listDomains();
        if (!$res['success']) {
            flash('error', 'Netlen API hatası: ' . $res['message']);
            redirect(url('admin/netlen'));
        }
        $count = 0;
        foreach ((array)$res['data'] as $d) {
            $name = $d['domain'] ?? ($d['name'] ?? '');
            if ($name === '') continue;
            $stmt = db()->prepare('SELECT id FROM domains WHERE domain = ?');
            $stmt->execute([$name]);
            if (!$stmt->fetch()) {
                db()->prepare('INSERT INTO domains (user_id, domain, registrar, tld, status, expiry_date, nameservers, dns) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
                    ->execute([0, $name, 'netlen', $d['tld'] ?? '', $d['status'] ?? 'active', $d['expires_at'] ?? ($d['expiry_date'] ?? null), json_encode([setting('netlen_ns1'), setting('netlen_ns2')]), json_encode([])]);
                $count++;
            }
        }
        flash('success', "{$count} alan adı senkronize edildi.");
        redirect(url('admin/netlen'));
    }

    public function netlenRegister(): void
    {
        $this->guard(); $this->validateCsrf();
        $api = netlen();
        $domain = trim($this->input('domain', ''));
        $years = (int)$this->input('years', 1);
        $contact = [
            'name' => trim($this->input('contact_name', '')),
            'email' => trim($this->input('contact_email', '')),
            'phone' => trim($this->input('contact_phone', '')),
            'address' => trim($this->input('contact_address', '')),
            'city' => trim($this->input('contact_city', '')),
            'postal_code' => trim($this->input('contact_postal', '')),
            'country' => 'TR',
        ];
        if (!$api || !$api->isEnabled()) {
            flash('error', 'Netlen modülü etkin değil.');
            redirect(url('admin/netlen'));
        }
        $res = $api->registerDomain($domain, $years, $contact, [setting('netlen_ns1'), setting('netlen_ns2')]);
        if ($res['success']) {
            flash('success', 'Alan adı Netlen üzerinden kaydedildi: ' . $domain);
        } else {
            flash('error', 'Kayıt başarısız: ' . $res['message']);
        }
        redirect(url('admin/netlen'));
    }

    public function affiliates(): void
    {
        $this->guard();
        $affiliates = db()->query("SELECT * FROM users WHERE referral_code != '' ORDER BY affiliate_balance DESC")->fetchAll();
        $commissions = db()->query('SELECT c.*, a.first_name afname, a.last_name aflname, u.first_name rfname, u.last_name rlname FROM affiliate_commissions c LEFT JOIN users a ON a.id = c.affiliate_id LEFT JOIN users u ON u.id = c.referred_user_id ORDER BY c.id DESC LIMIT 300')->fetchAll();
        echo $this->render('admin/affiliates', ['title' => 'Ortaklık (Affiliate)', 'affiliates' => $affiliates, 'commissions' => $commissions], 'admin');
    }

    public function affiliatePayout(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        db()->prepare('UPDATE affiliate_commissions SET status = "paid" WHERE affiliate_id = ? AND status = "pending"')->execute([$id]);
        flash('success', 'Komisyonlar ödendi olarak işaretlendi.');
        redirect(url('admin/affiliates'));
    }

    public function downloads(): void
    {
        $this->guard();
        $downloads = db()->query('SELECT * FROM downloads ORDER BY id DESC')->fetchAll();
        echo $this->render('admin/downloads', ['title' => 'İndirmeler', 'downloads' => $downloads], 'admin');
    }

    public function downloadAdd(): void
    {
        $this->guard(); $this->validateCsrf();
        db()->prepare('INSERT INTO downloads (name, description, file_path, category, status) VALUES (?, ?, ?, ?, 1)')
            ->execute([trim($this->input('name', '')), $this->input('description', ''), trim($this->input('file_path', '')), trim($this->input('category', ''))]);
        flash('success', 'İndirme eklendi.');
        redirect(url('admin/downloads'));
    }

    public function downloadDelete(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        db()->prepare('DELETE FROM downloads WHERE id = ?')->execute([$id]);
        flash('success', 'İndirme silindi.');
        redirect(url('admin/downloads'));
    }

    public function customFields(): void
    {
        $this->guard();
        $fields = db()->query('SELECT * FROM client_custom_fields ORDER BY sort_order, id')->fetchAll();
        echo $this->render('admin/custom_fields', ['title' => 'Özel Alanlar', 'fields' => $fields], 'admin');
    }

    public function customFieldAdd(): void
    {
        $this->guard(); $this->validateCsrf();
        db()->prepare('INSERT INTO client_custom_fields (name, field_type, options, required) VALUES (?, ?, ?, ?)')
            ->execute([trim($this->input('name', '')), $this->input('field_type', 'text'), $this->input('options', ''), (int)$this->input('required', 0)]);
        flash('success', 'Özel alan eklendi.');
        redirect(url('admin/custom-fields'));
    }

    public function customFieldDelete(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        db()->prepare('DELETE FROM client_custom_fields WHERE id = ?')->execute([$id]);
        db()->prepare('DELETE FROM client_custom_values WHERE field_id = ?')->execute([$id]);
        flash('success', 'Özel alan silindi.');
        redirect(url('admin/custom-fields'));
    }
    public function cancellations(): void
    {
        $this->guard();
        $requests = db()->query('SELECT cr.*, s.domain, u.first_name, u.last_name FROM cancellation_requests cr LEFT JOIN services s ON s.id = cr.service_id LEFT JOIN users u ON u.id = cr.user_id ORDER BY cr.id DESC')->fetchAll();
        echo $this->render('admin/cancellations', ['title' => 'İptal Talepleri', 'requests' => $requests], 'admin');
    }

    public function cancellationApprove(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        $stmt = db()->prepare('SELECT * FROM cancellation_requests WHERE id = ?');
        $stmt->execute([$id]);
        $req = $stmt->fetch();
        if ($req && $req['status'] === 'pending') {
            db()->prepare('UPDATE cancellation_requests SET status = "approved" WHERE id = ?')->execute([$id]);
            db()->prepare('UPDATE services SET status = "cancelled" WHERE id = ?')->execute([$req['service_id']]);
            flash('success', 'İptal onaylandı, hizmet sonlandırıldı.');
        }
        redirect(url('admin/cancellations'));
    }

    public function cancellationDeny(string $id): void
    {
        $this->guard(); $this->validateCsrf();
        db()->prepare('UPDATE cancellation_requests SET status = "denied" WHERE id = ?')->execute([$id]);
        flash('info', 'İptal talebi reddedildi.');
        redirect(url('admin/cancellations'));
    }

    public function massMail(): void
    {
        $this->guard();
        $clientCount = (int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
        echo $this->render('admin/mass_mail', ['title' => 'Toplu E-posta', 'clientCount' => $clientCount], 'admin');
    }

    public function massMailSend(): void
    {
        $this->guard(); $this->validateCsrf();
        $subject = trim($this->input('subject', ''));
        $body = $this->input('body', '');
        if ($subject === '' || $body === '') {
            flash('error', 'Konu ve içerik zorunludur.');
            redirect(url('admin/mass-mail'));
        }
        $users = db()->query('SELECT * FROM users WHERE status = "active"')->fetchAll();
        $sent = 0;
        foreach ($users as $u) {
            $msg = \App\Core\Mailer::interpolate($body, [
                'first_name' => $u['first_name'], 'last_name' => $u['last_name'],
                'email' => $u['email'], 'site_name' => setting('site_name', 'RCVXTR'),
            ]);
            if (\App\Core\Mailer::send($u['email'], $subject, $msg)) $sent++;
        }
        flash('success', "Toplu e-posta gönderildi: {$sent}/" . count($users) . ' alıcı.');
        redirect(url('admin/mass-mail'));
    }
}






