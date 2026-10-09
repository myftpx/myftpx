<?php
namespace App\Controllers;

class ClientController extends Controller
{
    private function userId(): int
    {
        auth()->require();
        return auth()->id();
    }

    public function dashboard(): void
    {
        $uid = $this->userId();
        $user = auth()->user();

        $stats = [];
        $stats['services'] = db()->query("SELECT COUNT(*) c FROM services WHERE user_id = $uid AND status IN ('active','pending')")->fetch()['c'];
        $stats['domains'] = db()->query("SELECT COUNT(*) c FROM domains WHERE user_id = $uid")->fetch()['c'];
        $stats['open_tickets'] = db()->query("SELECT COUNT(*) c FROM tickets WHERE user_id = $uid AND status IN ('open','answered')")->fetch()['c'];
        $stats['unpaid_invoices'] = db()->query("SELECT COUNT(*) c FROM invoices WHERE user_id = $uid AND status = 'unpaid'")->fetch()['c'];
        $stats['balance'] = $user['balance'];

        $recentInvoices = db()->query("SELECT * FROM invoices WHERE user_id = $uid ORDER BY id DESC LIMIT 5")->fetchAll();
        $recentTickets = db()->query("SELECT * FROM tickets WHERE user_id = $uid ORDER BY id DESC LIMIT 5")->fetchAll();
        $recentServices = db()->query("SELECT s.*, p.name product_name FROM services s LEFT JOIN products p ON p.id = s.product_id WHERE s.user_id = $uid ORDER BY s.id DESC LIMIT 5")->fetchAll();

        echo $this->render('client/dashboard', [
            'title' => 'Panel',
            'user' => $user,
            'stats' => $stats,
            'recentInvoices' => $recentInvoices,
            'recentTickets' => $recentTickets,
            'recentServices' => $recentServices,
        ], 'client');
    }

    public function services(): void
    {
        $uid = $this->userId();
        $stmt = db()->prepare('SELECT s.*, p.name product_name, p.type product_type FROM services s LEFT JOIN products p ON p.id = s.product_id WHERE s.user_id = ? ORDER BY s.id DESC');
        $stmt->execute([$uid]);
        $services = $stmt->fetchAll();
        echo $this->render('client/services', ['title' => 'Hizmetlerim', 'services' => $services], 'client');
    }

    public function serviceDetail(string $id): void
    {
        $uid = $this->userId();
        $stmt = db()->prepare('SELECT s.*, p.name product_name, p.type product_type, p.module FROM services s LEFT JOIN products p ON p.id = s.product_id WHERE s.id = ? AND s.user_id = ?');
        $stmt->execute([$id, $uid]);
        $service = $stmt->fetch();
        if (!$service) {
            http_response_code(404);
            echo $this->render('errors/404', ['title' => 'Hizmet bulunamadı'], 'client');
            return;
        }
        $service['config'] = json_decode($service['config'], true) ?: [];
        $cardsStmt = db()->prepare("SELECT * FROM saved_cards WHERE user_id = ? AND status = 'active' ORDER BY is_default DESC, id DESC");
        $cardsStmt->execute([$uid]);
        $cards = $cardsStmt->fetchAll();
        echo $this->render('client/service_detail', ['title' => $service['domain'] ?: $service['product_name'], 'service' => $service, 'cards' => $cards], 'client');
    }

    public function domains(): void
    {
        $uid = $this->userId();
        $stmt = db()->prepare('SELECT * FROM domains WHERE user_id = ? ORDER BY id DESC');
        $stmt->execute([$uid]);
        $domains = $stmt->fetchAll();
        echo $this->render('client/domains', ['title' => 'Alan Adlarım', 'domains' => $domains], 'client');
    }

    public function domainDetail(string $id): void
    {
        $uid = $this->userId();
        $stmt = db()->prepare('SELECT * FROM domains WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $uid]);
        $domain = $stmt->fetch();
        if (!$domain) {
            http_response_code(404);
            echo $this->render('errors/404', ['title' => 'Alan adı bulunamadı'], 'client');
            return;
        }
        $domain['dns'] = json_decode($domain['dns'], true) ?: [];
        $domain['nameservers'] = json_decode($domain['nameservers'], true) ?: ['ns1.rcvxtr.com', 'ns2.rcvxtr.com'];
        echo $this->render('client/domain_detail', ['title' => $domain['domain'], 'domain' => $domain], 'client');
    }

    public function domainDns(string $id): void
    {
        $this->validateCsrf();
        $uid = $this->userId();
        $stmt = db()->prepare('SELECT id FROM domains WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $uid]);
        if (!$stmt->fetch()) redirect(url('client/domains'));

        $records = [];
        $types = $_POST['type'] ?? [];
        $names = $_POST['name'] ?? [];
        $values = $_POST['value'] ?? [];
        $ttls = $_POST['ttl'] ?? [];
        foreach ($types as $i => $type) {
            if (trim((string)($names[$i] ?? '')) === '') continue;
            $records[] = [
                'type' => $type,
                'name' => trim($names[$i]),
                'value' => trim($values[$i] ?? ''),
                'ttl' => (int)($ttls[$i] ?? 3600),
            ];
        }
        $stmt = db()->prepare('UPDATE domains SET dns = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([json_encode($records), $id, $uid]);
        flash('success', 'DNS kayıtları güncellendi.');
        redirect(url('client/domains/' . $id));
    }
    public function invoices(): void
    {
        $uid = $this->userId();
        $stmt = db()->prepare('SELECT * FROM invoices WHERE user_id = ? ORDER BY id DESC');
        $stmt->execute([$uid]);
        $invoices = $stmt->fetchAll();
        echo $this->render('client/invoices', ['title' => 'Faturalarım', 'invoices' => $invoices], 'client');
    }

    public function invoiceDetail(string $id): void
    {
        $uid = $this->userId();
        $stmt = db()->prepare('SELECT * FROM invoices WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $uid]);
        $invoice = $stmt->fetch();
        if (!$invoice) {
            http_response_code(404);
            echo $this->render('errors/404', ['title' => 'Fatura bulunamadı'], 'client');
            return;
        }
        $stmt = db()->prepare('SELECT * FROM invoice_items WHERE invoice_id = ?');
        $stmt->execute([$id]);
        $items = $stmt->fetchAll();
        $gateways = db()->query('SELECT * FROM payment_gateways WHERE enabled = 1 ORDER BY sort_order')->fetchAll();

        $cardsStmt = db()->prepare("SELECT * FROM saved_cards WHERE user_id = ? AND status = 'active' ORDER BY is_default DESC, id DESC");
        $cardsStmt->execute([$uid]);
        $cards = $cardsStmt->fetchAll();

        $bankAccounts = db()->query('SELECT * FROM bank_accounts WHERE is_active = 1 ORDER BY sort_order, id')->fetchAll();

        echo $this->render('client/invoice_detail', [
            'title' => 'Fatura ' . $invoice['invoice_number'],
            'invoice' => $invoice,
            'items' => $items,
            'gateways' => $gateways,
            'cards' => $cards,
            'bankAccounts' => $bankAccounts,
        ], 'client');
    }

    public function payInvoice(string $id): void
    {
        $this->validateCsrf();
        $uid = $this->userId();
        $gateway = $this->input('gateway', 'balance');

        $stmt = db()->prepare('SELECT * FROM invoices WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $uid]);
        $invoice = $stmt->fetch();
        if (!$invoice || $invoice['status'] === 'paid') {
            flash('error', 'Fatura bulunamadı veya zaten ödenmiş.');
            redirect(url('client/invoices'));
        }

        // Balance payment
        if ($gateway === 'balance') {
            $user = auth()->user();
            if ((float)$user['balance'] >= (float)$invoice['total']) {
                $stmt = db()->prepare('UPDATE users SET balance = balance - ? WHERE id = ?');
                $stmt->execute([$invoice['total'], $uid]);
                (new StoreController())->markInvoicePaid((int)$id, 'balance');
                payment_log($uid, (int)$id, 'balance', 'payment.success', 'success', 'Fatura bakiyeden ödendi.', (float)$invoice['total']);
                flash('success', 'Fatura bakiyenizden ödendi.');
            } else {
                flash('error', 'Bakiyeniz yetersiz.');
            }
            redirect(url('client/invoices/' . $id));
        }

        // Card payment (paytr / iyzico)
        if ($gateway === 'paytr' || $gateway === 'iyzico') {
            $gw = gateway($gateway);
            if (!$gw) {
                flash('error', 'Bu ödeme yöntemi şu anda kullanılamıyor.');
                redirect(url('client/invoices/' . $id));
            }

            $savedCardId = (int)$this->input('saved_card_id', 0);
            $saveCard = (int)$this->input('save_card', 0);
            $result = null;
            $token = null;
            $cardUserKey = null;
            $last4 = '';
            $brand = '';

            if ($savedCardId > 0) {
                $stmt = db()->prepare("SELECT * FROM saved_cards WHERE id = ? AND user_id = ? AND gateway = ? AND status = 'active'");
                $stmt->execute([$savedCardId, $uid, $gateway]);
                $card = $stmt->fetch();
                if (!$card) {
                    flash('error', 'Geçersiz kart seçimi.');
                    redirect(url('client/invoices/' . $id));
                }
                $token = $card['card_token'];
                $cardUserKey = $card['card_user_key'] ?: null;
                $last4 = $card['last4'];
                $brand = $card['brand'];
                $result = $gw->chargeToken($token, $cardUserKey, (float)$invoice['total'], 'Fatura ' . $invoice['invoice_number']);
            } else {
                $cardData = [
                    'holder' => trim($this->input('holder', '')),
                    'number' => preg_replace('/\D/', '', $this->input('number', '')),
                    'expiry_month' => $this->input('expiry_month', ''),
                    'expiry_year' => $this->input('expiry_year', ''),
                    'cvv' => $this->input('cvv', ''),
                    'email' => auth()->user()['email'],
                ];
                if (strlen($cardData['number']) < 15 || $cardData['holder'] === '') {
                    flash('error', 'Lütfen kart bilgilerini eksiksiz girin.');
                    redirect(url('client/invoices/' . $id));
                }
                $result = $gw->charge($cardData, (float)$invoice['total'], 'Fatura ' . $invoice['invoice_number']);
                if (!empty($result['success']) && ($saveCard || !empty($result['token']))) {
                    $token = $result['token'] ?? null;
                    $cardUserKey = $result['card_user_key'] ?? null;
                    $last4 = $result['last4'] ?? '';
                    $brand = $result['brand'] ?? '';
                }
            }

            if (!empty($result['success'])) {
                (new StoreController())->markInvoicePaid((int)$id, $gateway);
                payment_log($uid, (int)$id, $gateway, 'payment.success', 'success', 'Kart ile ödeme başarılı.', (float)$invoice['total'], $result['transaction_id'] ?? '');

                // Persist newly tokenized card (save_card flow)
                if ($token && $savedCardId === 0 && $saveCard) {
                    $count = db()->prepare('SELECT COUNT(*) FROM saved_cards WHERE user_id = ?');
                    $count->execute([$uid]);
                    $isDefault = (int)$count->fetchColumn() === 0 ? 1 : 0;
                    db()->prepare('INSERT INTO saved_cards (user_id, gateway, card_token, card_user_key, last4, brand, holder_name, expiry_month, expiry_year, is_default) VALUES (?,?,?,?,?,?,?,?,?,?)')
                        ->execute([$uid, $gateway, $token, $cardUserKey, $last4, $brand, $this->input('holder', ''), $this->input('expiry_month', ''), $this->input('expiry_year', ''), $isDefault]);
                    payment_log($uid, null, $gateway, 'card.save', 'success', 'Ödeme sırasında kart kaydedildi.', 0);
                }
                flash('success', 'Ödeme alındı, teşekkürler!');
            } else {
                payment_log($uid, (int)$id, $gateway, 'payment.failed', 'failed', $result['message'] ?? 'Ödeme başarısız.', (float)$invoice['total']);
                flash('error', 'Ödeme başarısız: ' . ($result['message'] ?? 'bilinmeyen hata'));
            }
            redirect(url('client/invoices/' . $id));
        }

        flash('error', 'Geçersiz ödeme yöntemi.');
        redirect(url('client/invoices/' . $id));
    }
    public function tickets(): void
    {
        $uid = $this->userId();
        $stmt = db()->prepare('SELECT * FROM tickets WHERE user_id = ? ORDER BY id DESC');
        $stmt->execute([$uid]);
        $tickets = $stmt->fetchAll();
        echo $this->render('client/tickets', ['title' => 'Destek Biletlerim', 'tickets' => $tickets], 'client');
    }

    public function ticketNew(): void
    {
        $uid = $this->userId();
        $departments = ['Destek', 'Satış', 'Faturalama', 'Teknik'];
        echo $this->render('client/ticket_new', ['title' => 'Yeni Destek Bileti', 'departments' => $departments], 'client');
    }

    public function ticketCreate(): void
    {
        $this->validateCsrf();
        $uid = $this->userId();
        $subject = trim($this->input('subject', ''));
        $department = $this->input('department', 'Destek');
        $priority = $this->input('priority', 'medium');
        $message = trim($this->input('message', ''));

        if ($subject === '' || $message === '') {
            flash('error', 'Konu ve mesaj alanları zorunludur.');
            redirect(url('client/tickets/new'));
        }
        $number = 'TKT-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
        $stmt = db()->prepare('INSERT INTO tickets (ticket_number, user_id, subject, department, priority, status) VALUES (?, ?, ?, ?, ?, "open")');
        $stmt->execute([$number, $uid, $subject, $department, $priority]);
        $ticketId = (int)db()->lastInsertId();

        $stmt = db()->prepare('INSERT INTO ticket_replies (ticket_id, user_id, is_admin, message) VALUES (?, ?, 0, ?)');
        $stmt->execute([$ticketId, $uid, $message]);

        flash('success', 'Destek biletiniz oluşturuldu.');
        redirect(url('client/tickets/' . $ticketId));
    }

    public function ticketDetail(string $id): void
    {
        $uid = $this->userId();
        $stmt = db()->prepare('SELECT * FROM tickets WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $uid]);
        $ticket = $stmt->fetch();
        if (!$ticket) {
            http_response_code(404);
            echo $this->render('errors/404', ['title' => 'Bilet bulunamadı'], 'client');
            return;
        }
        $stmt = db()->prepare('SELECT r.*, u.first_name, u.last_name FROM ticket_replies r LEFT JOIN users u ON u.id = r.user_id WHERE r.ticket_id = ? ORDER BY r.id ASC');
        $stmt->execute([$id]);
        $replies = $stmt->fetchAll();
        echo $this->render('client/ticket_detail', ['title' => '#' . $ticket['ticket_number'], 'ticket' => $ticket, 'replies' => $replies], 'client');
    }

    public function ticketReply(string $id): void
    {
        $this->validateCsrf();
        $uid = $this->userId();
        $message = trim($this->input('message', ''));
        if ($message === '') redirect(url('client/tickets/' . $id));

        $stmt = db()->prepare('SELECT id FROM tickets WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $uid]);
        if (!$stmt->fetch()) redirect(url('client/tickets'));

        $stmt = db()->prepare('INSERT INTO ticket_replies (ticket_id, user_id, is_admin, message) VALUES (?, ?, 0, ?)');
        $stmt->execute([$id, $uid, $message]);
        $stmt = db()->prepare('UPDATE tickets SET status = "open", last_reply_at = ? WHERE id = ?');
        $stmt->execute([now(), $id]);
        flash('success', 'Yanıtınız gönderildi.');
        redirect(url('client/tickets/' . $id));
    }

    public function balance(): void
    {
        $uid = $this->userId();
        $stmt = db()->prepare('SELECT * FROM transactions WHERE user_id = ? ORDER BY id DESC LIMIT 50');
        $stmt->execute([$uid]);
        $transactions = $stmt->fetchAll();
        echo $this->render('client/balance', ['title' => 'Bakiye', 'user' => auth()->user(), 'transactions' => $transactions], 'client');
    }

    public function balanceAdd(): void
    {
        $this->validateCsrf();
        $uid = $this->userId();
        $amount = (float)$this->input('amount', 0);
        $gateway = $this->input('gateway', 'bank_transfer');
        if ($amount <= 0) {
            flash('error', 'Geçerli bir tutar girin.');
            redirect(url('client/balance'));
        }

        if ($gateway === 'bank_transfer') {
            // Manual transfer: pending until admin confirms
            $stmt = db()->prepare('INSERT INTO transactions (user_id, amount, gateway, transaction_id, status) VALUES (?, ?, ?, ?, "pending")');
            $stmt->execute([$uid, $amount, 'bank_transfer', 'ADD-' . uniqid()]);
            payment_log($uid, null, 'bank_transfer', 'balance.add_submitted', 'info', 'Bakiye yükleme (havale) bildirimi alındı, onay bekleniyor.', $amount);
            flash('info', 'Havale bildiriminiz alındı. Onaylandığında bakiyenize eklenecektir.');
        } else {
            // Card / simulated instant credit
            $stmt = db()->prepare('UPDATE users SET balance = balance + ? WHERE id = ?');
            $stmt->execute([$amount, $uid]);
            $stmt = db()->prepare('INSERT INTO transactions (user_id, amount, gateway, transaction_id, status) VALUES (?, ?, ?, ?, "completed")');
            $stmt->execute([$uid, $amount, $gateway, uniqid('ADD-')]);
            payment_log($uid, null, $gateway, 'balance.add', 'success', 'Bakiyeye ' . $amount . ' eklendi.', $amount);
            flash('success', 'Bakiyenize ' . money($amount) . ' eklendi.');
        }
        redirect(url('client/balance'));
    }
    public function apiKeys(): void
    {
        $uid = $this->userId();
        $stmt = db()->prepare('SELECT * FROM api_keys WHERE user_id = ? ORDER BY id DESC');
        $stmt->execute([$uid]);
        $keys = $stmt->fetchAll();
        echo $this->render('client/api', ['title' => 'API Erişimi', 'keys' => $keys], 'client');
    }

    public function apiCreate(): void
    {
        $this->validateCsrf();
        $uid = $this->userId();
        $name = trim($this->input('name', 'API Anahtarı'));
        $permissions = (array)($this->input('permissions', []));
        $ips = trim($this->input('allowed_ips', ''));

        $apiKey = 'rcvx_' . random_key(32);
        $authKey = random_key(48);

        $stmt = db()->prepare('INSERT INTO api_keys (user_id, name, api_key, auth_key, allowed_ips, permissions) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$uid, $name, $apiKey, $authKey, $ips, json_encode($permissions)]);
        flash('success', 'API anahtarı oluşturuldu. Auth key yalnızca bir kez gösterilir, kaydedin!');
        redirect(url('client/api'));
    }

    public function apiDelete(string $id): void
    {
        $this->validateCsrf();
        $uid = $this->userId();
        $stmt = db()->prepare('DELETE FROM api_keys WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $uid]);
        flash('success', 'API anahtarı silindi.');
        redirect(url('client/api'));
    }

    public function apiToggle(string $id): void
    {
        $this->validateCsrf();
        $uid = $this->userId();
        $stmt = db()->prepare('UPDATE api_keys SET status = CASE WHEN status = "active" THEN "disabled" ELSE "active" END WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $uid]);
        redirect(url('client/api'));
    }

    public function apiIps(string $id): void
    {
        $this->validateCsrf();
        $uid = $this->userId();
        $ips = trim($this->input('allowed_ips', ''));
        $stmt = db()->prepare('UPDATE api_keys SET allowed_ips = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([$ips, $id, $uid]);
        flash('success', 'IP izin listesi güncellendi.');
        redirect(url('client/api'));
    }

    public function profile(): void
    {
        $uid = $this->userId();
        echo $this->render('client/profile', ['title' => 'Profilim', 'user' => auth()->user()], 'client');
    }

    public function profileSave(): void
    {
        $this->validateCsrf();
        $uid = $this->userId();
        $fields = ['first_name', 'last_name', 'phone', 'company', 'address', 'city', 'country', 'postal_code'];
        $data = [];
        foreach ($fields as $f) $data[] = trim($this->input($f, ''));
        $data[] = $uid;
        $stmt = db()->prepare('UPDATE users SET first_name=?, last_name=?, phone=?, company=?, address=?, city=?, country=?, postal_code=? WHERE id=?');
        $stmt->execute($data);
        flash('success', 'Profil bilgileriniz güncellendi.');
        redirect(url('client/profile'));
    }

    public function profilePassword(): void
    {
        $this->validateCsrf();
        $uid = $this->userId();
        $current = (string)$this->input('current_password', '');
        $new = (string)$this->input('new_password', '');
        $confirm = (string)$this->input('confirm_password', '');

        $user = auth()->user();
        if (!password_verify($current, $user['password'])) {
            flash('error', 'Mevcut şifre hatalı.');
            redirect(url('client/profile'));
        }
        if (strlen($new) < 6 || $new !== $confirm) {
            flash('error', 'Yeni şifre en az 6 karakter olmalı ve eşleşmelidir.');
            redirect(url('client/profile'));
        }
        $stmt = db()->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->execute([password_hash($new, PASSWORD_DEFAULT), $uid]);
        flash('success', 'Şifreniz değiştirildi.');
        redirect(url('client/profile'));
    }

    public function cards(): void
    {
        $uid = $this->userId();
        $stmt = db()->prepare('SELECT * FROM saved_cards WHERE user_id = ? ORDER BY is_default DESC, id DESC');
        $stmt->execute([$uid]);
        $cards = $stmt->fetchAll();
        $gateways = db()->query("SELECT * FROM payment_gateways WHERE enabled = 1 AND code IN ('paytr','iyzico') ORDER BY sort_order")->fetchAll();
        echo $this->render('client/cards', ['title' => 'Kayıtlı Kartlarım', 'cards' => $cards, 'gateways' => $gateways], 'client');
    }

    public function cardAdd(): void
    {
        $this->validateCsrf();
        $uid = $this->userId();
        $gatewayCode = $this->input('gateway', '');
        $gw = gateway($gatewayCode);
        if (!$gw) {
            flash('error', 'Geçersiz veya devre dışı ödeme kuruluşu.');
            redirect(url('client/cards'));
        }

        $card = [
            'holder' => trim($this->input('holder', '')),
            'number' => preg_replace('/\D/', '', $this->input('number', '')),
            'expiry_month' => $this->input('expiry_month', ''),
            'expiry_year' => $this->input('expiry_year', ''),
            'cvv' => $this->input('cvv', ''),
            'email' => auth()->user()['email'],
        ];
        if (strlen($card['number']) < 15 || $card['holder'] === '') {
            flash('error', 'Lütfen kart bilgilerini eksiksiz girin.');
            redirect(url('client/cards'));
        }

        $result = $gw->saveCard($card);
        if (empty($result['success'])) {
            payment_log($uid, null, $gatewayCode, 'card.save', 'failed', $result['message'] ?? 'Kart kaydedilemedi.');
            flash('error', $result['message'] ?? 'Kart kaydedilemedi.');
            redirect(url('client/cards'));
        }

        $count = db()->prepare('SELECT COUNT(*) FROM saved_cards WHERE user_id = ?');
        $count->execute([$uid]);
        $isDefault = (int)$count->fetchColumn() === 0 ? 1 : (int)$this->input('set_default', 0);

        if ($isDefault) {
            db()->prepare('UPDATE saved_cards SET is_default = 0 WHERE user_id = ?')->execute([$uid]);
        }

        db()->prepare('INSERT INTO saved_cards (user_id, gateway, card_token, card_user_key, last4, brand, holder_name, expiry_month, expiry_year, is_default) VALUES (?,?,?,?,?,?,?,?,?,?)')
            ->execute([$uid, $gatewayCode, $result['token'], $result['card_user_key'] ?? '', $result['last4'] ?? '', $result['brand'] ?? '', $card['holder'], $card['expiry_month'], $card['expiry_year'], $isDefault]);

        payment_log($uid, null, $gatewayCode, 'card.save', 'success', 'Kart kaydedildi (•••• ' . ($result['last4'] ?? '') . ').');
        flash('success', 'Kartınız başarıyla kaydedildi.');
        redirect(url('client/cards'));
    }
    public function cardDelete(string $id): void
    {
        $this->validateCsrf();
        $uid = $this->userId();
        $stmt = db()->prepare('SELECT * FROM saved_cards WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $uid]);
        $card = $stmt->fetch();
        if ($card) {
            db()->prepare('DELETE FROM saved_cards WHERE id = ? AND user_id = ?')->execute([$id, $uid]);
            db()->prepare('UPDATE services SET card_id = NULL WHERE card_id = ? AND user_id = ?')->execute([$id, $uid]);
            payment_log($uid, null, $card['gateway'], 'card.delete', 'info', 'Kart silindi (•••• ' . $card['last4'] . ').');
            flash('success', 'Kart silindi.');
        }
        redirect(url('client/cards'));
    }

    public function cardDefault(string $id): void
    {
        $this->validateCsrf();
        $uid = $this->userId();
        db()->prepare('UPDATE saved_cards SET is_default = 0 WHERE user_id = ?')->execute([$uid]);
        db()->prepare('UPDATE saved_cards SET is_default = 1 WHERE id = ? AND user_id = ?')->execute([$id, $uid]);
        flash('success', 'Varsayılan kart güncellendi.');
        redirect(url('client/cards'));
    }

    public function paymentLogs(): void
    {
        $uid = $this->userId();
        $stmt = db()->prepare('SELECT * FROM payment_logs WHERE user_id = ? ORDER BY id DESC LIMIT 200');
        $stmt->execute([$uid]);
        $logs = $stmt->fetchAll();
        echo $this->render('client/payment_logs', ['title' => 'Ödeme Geçmişim', 'logs' => $logs], 'client');
    }

    public function bankTransfer(string $id): void
    {
        $this->validateCsrf();
        $uid = $this->userId();
        $stmt = db()->prepare('SELECT * FROM invoices WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $uid]);
        $invoice = $stmt->fetch();
        if (!$invoice || $invoice['status'] === 'paid') {
            flash('error', 'Fatura bulunamadı veya zaten ödenmiş.');
            redirect(url('client/invoices'));
        }

        $reference = trim($this->input('reference', ''));
        $bankAccountId = (int)$this->input('bank_account_id', 0);

        $stmt = db()->prepare('INSERT INTO transactions (invoice_id, user_id, amount, gateway, transaction_id, status) VALUES (?, ?, ?, ?, ?, "pending")');
        $stmt->execute([$id, $uid, $invoice['total'], 'bank_transfer', $reference ?: ('BT-' . uniqid())]);

        payment_log($uid, (int)$id, 'bank_transfer', 'bank_transfer.submitted', 'info', 'Havale bildirimi alındı, onay bekleniyor.' . ($bankAccountId ? ' (Banka #' . $bankAccountId . ')' : ''), (float)$invoice['total'], $reference);

        flash('info', 'Havale bildiriminiz alındı. Ödemeniz onaylandığında faturanız otomatik ödenecek.');
        redirect(url('client/invoices/' . $id));
    }

    public function serviceAutorenew(string $id): void
    {
        $this->validateCsrf();
        $uid = $this->userId();
        $autoRenew = (int)$this->input('auto_renew', 0);
        $cardId = (int)$this->input('card_id', 0);

        $stmt = db()->prepare('UPDATE services SET auto_renew = ?, card_id = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([$autoRenew, $cardId ?: null, $id, $uid]);

        payment_log($uid, null, 'recurring', $autoRenew ? 'autorenew.enabled' : 'autorenew.disabled', 'info', 'Hizmet #' . $id . ' için otomatik ödeme ' . ($autoRenew ? 'açıldı' : 'kapatıldı') . '.');
        flash('success', $autoRenew ? 'Otomatik ödeme etkinleştirildi.' : 'Otomatik ödeme kapatıldı.');
        redirect(url('client/services/' . $id));
    }
}


