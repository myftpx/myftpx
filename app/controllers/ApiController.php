<?php
namespace App\Controllers;

class ApiController extends Controller
{
    private function authenticate(): array
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!(int)setting('api_enabled', 1)) {
            $this->json(['error' => 'API devre dışı'], 403);
        }

        $apiKey = $this->header('X-Api-Key') ?? $this->header('X-API-Key');
        if (!$apiKey) {
            $auth = $this->header('Authorization');
            if ($auth && preg_match('/Bearer\s+(\S+)/i', $auth, $m)) $apiKey = $m[1];
        }
        $authKey = $this->header('X-Auth-Key') ?? '';

        if (!$apiKey) {
            $this->json(['error' => 'API anahtarı eksik (X-Api-Key)'], 401);
        }

        $stmt = db()->prepare('SELECT * FROM api_keys WHERE api_key = ?');
        $stmt->execute([$apiKey]);
        $key = $stmt->fetch();
        if (!$key || $key['status'] !== 'active') {
            $this->json(['error' => 'Geçersiz veya devre dışı API anahtarı'], 401);
        }
        if ($authKey !== '' && !hash_equals($key['auth_key'], $authKey)) {
            $this->json(['error' => 'Auth key doğrulanamadı'], 401);
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $allowed = array_filter(array_map('trim', explode("\n", str_replace(',', "\n", $key['allowed_ips'] ?? ''))));
        if (!empty($allowed) && !in_array($ip, $allowed, true)) {
            $this->json(['error' => 'IP izin verilmedi: ' . $ip], 403);
        }

        $key['permissions'] = json_decode($key['permissions'], true) ?: [];
        return $key;
    }

    private function header(string $name): ?string
    {
        $name = str_replace('-', '_', strtoupper($name));
        return $_SERVER['HTTP_' . $name] ?? $_SERVER[$name] ?? null;
    }

    private function requirePermission(array $key, string $perm): void
    {
        if (in_array('*', $key['permissions'], true)) return;
        if (!in_array($perm, $key['permissions'], true)) {
            $this->json(['error' => 'Bu işlem için yetki yok: ' . $perm], 403);
        }
    }

    private function respond($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit;
    }

    private function safeUser(int $id): array
    {
        $stmt = db()->prepare('SELECT id, first_name, last_name, email, phone, company, balance, currency, status, created_at FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $u = $stmt->fetch() ?: [];
        unset($u['password']);
        return $u;
    }
    public function handle(string $resource, ?string $id = null): void
    {
        $key = $this->authenticate();
        $userId = (int)$key['user_id'];
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        db()->prepare('UPDATE api_keys SET last_used_at = ? WHERE id = ?')->execute([now(), $key['id']]);

        switch ($resource) {
            case 'me':
            case 'account':
                $this->requirePermission($key, 'account:read');
                $this->respond(['data' => $this->safeUser($userId)]);
                break;
            case 'services':
                $this->handleServices($userId, $key, $method, $id);
                break;
            case 'domains':
                $this->handleDomains($userId, $key, $method, $id);
                break;
            case 'invoices':
                $this->handleInvoices($userId, $key, $method, $id);
                break;
            case 'tickets':
                $this->handleTickets($userId, $key, $method, $id);
                break;
            case 'balance':
                $this->handleBalance($userId, $key, $method);
                break;
            case 'orders':
                $this->handleOrders($userId, $key, $method);
                break;
            default:
                $this->json(['error' => 'Bilinmeyen kaynak: ' . $resource], 404);
        }
    }

    private function handleServices(int $uid, array $key, string $method, ?string $id): void
    {
        $this->requirePermission($key, 'services:read');
        if ($method === 'GET' && $id === null) {
            $stmt = db()->prepare('SELECT s.*, p.name product_name FROM services s LEFT JOIN products p ON p.id=s.product_id WHERE s.user_id = ?');
            $stmt->execute([$uid]);
            $this->respond(['data' => $stmt->fetchAll()]);
        }
        if ($method === 'GET' && $id !== null) {
            $stmt = db()->prepare('SELECT s.*, p.name product_name FROM services s LEFT JOIN products p ON p.id=s.product_id WHERE s.id = ? AND s.user_id = ?');
            $stmt->execute([$id, $uid]);
            $this->respond(['data' => $stmt->fetch() ?: null]);
        }
        $this->json(['error' => 'Desteklenmeyen istek'], 405);
    }
    private function handleDomains(int $uid, array $key, string $method, ?string $id): void
    {
        $this->requirePermission($key, 'domains:read');
        if ($method === 'GET' && $id === null) {
            $stmt = db()->prepare('SELECT * FROM domains WHERE user_id = ?');
            $stmt->execute([$uid]);
            $this->respond(['data' => $stmt->fetchAll()]);
        }
        if ($method === 'GET' && $id !== null) {
            $stmt = db()->prepare('SELECT * FROM domains WHERE id = ? AND user_id = ?');
            $stmt->execute([$id, $uid]);
            $this->respond(['data' => $stmt->fetch() ?: null]);
        }
        if (($method === 'PUT' || $method === 'POST') && $id !== null) {
            $this->requirePermission($key, 'domains:write');
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $records = $input['dns'] ?? null;
            if ($records !== null) {
                db()->prepare('UPDATE domains SET dns = ? WHERE id = ? AND user_id = ?')->execute([json_encode($records), $id, $uid]);
            }
            $this->respond(['success' => true, 'message' => 'Alan adı güncellendi']);
        }
        $this->json(['error' => 'Desteklenmeyen istek'], 405);
    }

    private function handleInvoices(int $uid, array $key, string $method, ?string $id): void
    {
        $this->requirePermission($key, 'invoices:read');
        if ($method === 'GET' && $id === null) {
            $stmt = db()->prepare('SELECT * FROM invoices WHERE user_id = ?');
            $stmt->execute([$uid]);
            $this->respond(['data' => $stmt->fetchAll()]);
        }
        if ($method === 'GET' && $id !== null) {
            $stmt = db()->prepare('SELECT i.* FROM invoices i WHERE i.id = ? AND i.user_id = ?');
            $stmt->execute([$id, $uid]);
            $inv = $stmt->fetch();
            $stmt = db()->prepare('SELECT * FROM invoice_items WHERE invoice_id = ?');
            $stmt->execute([$id]);
            $this->respond(['data' => $inv ? array_merge($inv, ['items' => $stmt->fetchAll()]) : null]);
        }
        if ($method === 'POST' && $id !== null) {
            $this->requirePermission($key, 'invoices:pay');
            $stmt = db()->prepare('SELECT * FROM invoices WHERE id = ? AND user_id = ?');
            $stmt->execute([$id, $uid]);
            $invoice = $stmt->fetch();
            if (!$invoice) $this->json(['error' => 'Fatura bulunamadı'], 404);
            if ($invoice['status'] === 'paid') $this->respond(['success' => true, 'message' => 'Zaten ödenmiş']);
            $stmt = db()->prepare('SELECT balance FROM users WHERE id = ?');
            $stmt->execute([$uid]);
            $balance = (float)$stmt->fetch()['balance'];
            if ($balance < (float)$invoice['total']) $this->json(['error' => 'Yetersiz bakiye'], 402);
            db()->prepare('UPDATE users SET balance = balance - ? WHERE id = ?')->execute([$invoice['total'], $uid]);
            (new StoreController())->markInvoicePaid((int)$id, 'api');
            $this->respond(['success' => true, 'message' => 'Fatura ödendi']);
        }
        $this->json(['error' => 'Desteklenmeyen istek'], 405);
    }
    private function handleTickets(int $uid, array $key, string $method, ?string $id): void
    {
        $this->requirePermission($key, 'tickets:read');
        if ($method === 'GET' && $id === null) {
            $stmt = db()->prepare('SELECT * FROM tickets WHERE user_id = ? ORDER BY id DESC');
            $stmt->execute([$uid]);
            $this->respond(['data' => $stmt->fetchAll()]);
        }
        if ($method === 'GET' && $id !== null) {
            $stmt = db()->prepare('SELECT * FROM tickets WHERE id = ? AND user_id = ?');
            $stmt->execute([$id, $uid]);
            $ticket = $stmt->fetch();
            $stmt = db()->prepare('SELECT * FROM ticket_replies WHERE ticket_id = ? ORDER BY id ASC');
            $stmt->execute([$id]);
            $this->respond(['data' => $ticket ? array_merge($ticket, ['replies' => $stmt->fetchAll()]) : null]);
        }
        if ($method === 'POST' && $id === null) {
            $this->requirePermission($key, 'tickets:write');
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $subject = trim($input['subject'] ?? '');
            $message = trim($input['message'] ?? '');
            if ($subject === '' || $message === '') $this->json(['error' => 'subject ve message zorunlu'], 422);
            $number = 'TKT-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
            db()->prepare('INSERT INTO tickets (ticket_number, user_id, subject, department, priority, status) VALUES (?, ?, ?, ?, ?, "open")')
                ->execute([$number, $uid, $subject, $input['department'] ?? 'Destek', $input['priority'] ?? 'medium']);
            $tid = (int)db()->lastInsertId();
            db()->prepare('INSERT INTO ticket_replies (ticket_id, user_id, is_admin, message) VALUES (?, ?, 0, ?)')->execute([$tid, $uid, $message]);
            $this->respond(['success' => true, 'id' => $tid, 'ticket_number' => $number], 201);
        }
        if ($method === 'POST' && $id !== null) {
            $this->requirePermission($key, 'tickets:write');
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $message = trim($input['message'] ?? '');
            if ($message === '') $this->json(['error' => 'message zorunlu'], 422);
            db()->prepare('INSERT INTO ticket_replies (ticket_id, user_id, is_admin, message) VALUES (?, ?, 0, ?)')->execute([$id, $uid, $message]);
            db()->prepare('UPDATE tickets SET status="open", last_reply_at=? WHERE id=? AND user_id=?')->execute([now(), $id, $uid]);
            $this->respond(['success' => true]);
        }
        $this->json(['error' => 'Desteklenmeyen istek'], 405);
    }

    private function handleBalance(int $uid, array $key, string $method): void
    {
        $this->requirePermission($key, 'account:read');
        $stmt = db()->prepare('SELECT balance, currency FROM users WHERE id = ?');
        $stmt->execute([$uid]);
        $this->respond(['data' => $stmt->fetch()]);
    }

    private function handleOrders(int $uid, array $key, string $method): void
    {
        if ($method !== 'POST') $this->json(['error' => 'Yalnızca POST desteklenir'], 405);
        $this->requirePermission($key, 'orders:write');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $productId = (int)($input['product_id'] ?? 0);
        $cycle = $input['billing_cycle'] ?? 'monthly';
        $domain = trim($input['domain'] ?? '');
        $paymentMethod = $input['payment_method'] ?? 'balance';

        $stmt = db()->prepare('SELECT * FROM products WHERE id = ? AND status = "active"');
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        if (!$product) $this->json(['error' => 'Geçersiz ürün'], 404);

        $multiplier = ['monthly' => 1, 'quarterly' => 3, 'semi_annual' => 6, 'annually' => 10, 'biennially' => 24][$cycle] ?? 1;
        $amount = round(((float)$product['price'] * $multiplier) + (float)$product['setup_fee'], 2);

        if ($paymentMethod !== 'balance') $this->json(['error' => 'API üzerinden yalnızca bakiye ile sipariş verilebilir'], 422);

        $stmt = db()->prepare('SELECT balance FROM users WHERE id = ?');
        $stmt->execute([$uid]);
        $balance = (float)$stmt->fetch()['balance'];
        if ($balance < $amount) $this->json(['error' => 'Yetersiz bakiye'], 402);

        $orderNumber = 'ORD-' . strtoupper(substr(md5(uniqid('', true)), 0, 10));
        db()->prepare('INSERT INTO orders (order_number, user_id, product_id, billing_cycle, domain, config, amount, status, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, "pending", ?)')
            ->execute([$orderNumber, $uid, $productId, $cycle, $domain, json_encode($input['config'] ?? []), $amount, $paymentMethod]);

        $invoice = (new StoreController())->createInvoice($uid, [
            ['description' => $product['name'] . ' (API)', 'amount' => $amount],
        ]);

        db()->prepare('UPDATE users SET balance = balance - ? WHERE id = ?')->execute([$amount, $uid]);
        (new StoreController())->markInvoicePaid($invoice, 'balance');
        (new StoreController())->activateOrder($orderNumber, $uid, $product, $cycle, $domain, $input['config'] ?? [], $amount);

        $this->respond(['success' => true, 'order_number' => $orderNumber, 'invoice_id' => $invoice], 201);
    }
}


