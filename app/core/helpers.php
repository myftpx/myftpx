<?php
/**
 * RCVXTR — Global helper functions
 */

if (!function_exists('e')) {
    function e($value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('config')) {
    function config(?string $key = null, $default = null) {
        static $cfg = null;
        if ($cfg === null) {
            $path = dirname(__DIR__, 2) . '/config.php';
            $cfg = is_file($path) ? (include $path) : [];
            if (!is_array($cfg)) $cfg = [];
        }
        if ($key === null) return $cfg;
        return $cfg[$key] ?? $default;
    }
}

if (!function_exists('db')) {
    function db(): \PDO {
        return \App\Core\Database::instance();
    }
}

if (!function_exists('setting')) {
    function setting(string $key, $default = null) {
        static $cache = [];
        if (array_key_exists($key, $cache)) return $cache[$key];
        try {
            $stmt = db()->prepare('SELECT value FROM settings WHERE name = ?');
            $stmt->execute([$key]);
            $row = $stmt->fetch();
            $value = $row ? $row['value'] : $default;
        } catch (\Throwable $t) {
            $value = $default;
        }
        $cache[$key] = $value;
        return $value;
    }
}

if (!function_exists('set_setting')) {
    function set_setting(string $key, $value): void {
        $stmt = db()->prepare('INSERT INTO settings (name, value) VALUES (?, ?) ON CONFLICT(name) DO UPDATE SET value = excluded.value');
        $stmt->execute([$key, (string)$value]);
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): never {
        header('Location: ' . $url);
        exit;
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string {
        $base = rtrim(config('base_url', ''), '/');
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('current_theme')) {
    function current_theme(): string {
        $theme = setting('theme', 'rcvxtrwhite');
        return in_array($theme, ['rcvxtrwhite', 'rcvxtrdark'], true) ? $theme : 'rcvxtrwhite';
    }
}

if (!function_exists('flash')) {
    function flash(?string $type = null, ?string $message = null): ?array {
        if ($type !== null) {
            $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
            return null;
        }
        $f = $_SESSION['_flash'] ?? null;
        unset($_SESSION['_flash']);
        return $f;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        return \App\Core\Csrf::token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('auth')) {
    function auth(): ?\App\Core\Auth {
        return \App\Core\Auth::instance();
    }
}

if (!function_exists('money')) {
    function money($amount, ?string $currency = null): string {
        $currency = $currency ?: (setting('currency', 'TRY'));
        $symbols = [
            'TRY' => '₺', 'USD' => '$', 'EUR' => '€', 'GBP' => '£',
        ];
        $symbol = $symbols[$currency] ?? ($currency . ' ');
        $formatted = number_format((float)$amount, 2, ',', '.');
        return $symbol . $formatted;
    }
}

if (!function_exists('slug')) {
    function slug(string $text): string {
        $text = mb_strtolower(trim($text));
        $map = ['ı' => 'i', 'ğ' => 'g', 'ü' => 'u', 'ş' => 's', 'ö' => 'o', 'ç' => 'c'];
        $text = strtr($text, $map);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }
}

if (!function_exists('random_key')) {
    function random_key(int $length = 40): string {
        return bin2hex(random_bytes($length));
    }
}

if (!function_exists('time_ago')) {
    function time_ago($datetime): string {
        $time = is_numeric($datetime) ? $datetime : strtotime($datetime);
        $diff = time() - $time;
        if ($diff < 60) return 'az önce';
        if ($diff < 3600) return floor($diff / 60) . ' dk önce';
        if ($diff < 86400) return floor($diff / 3600) . ' saat önce';
        if ($diff < 2592000) return floor($diff / 86400) . ' gün önce';
        if ($diff < 31536000) return floor($diff / 2592000) . ' ay önce';
        return floor($diff / 31536000) . ' yıl önce';
    }
}

if (!function_exists('now')) {
    function now(): string {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('mask_email')) {
    function mask_email(string $email): string {
        [$user, $domain] = explode('@', $email, 2) + ['', ''];
        $len = strlen($user);
        $visible = min(2, $len);
        return substr($user, 0, $visible) . str_repeat('*', max(1, $len - $visible)) . '@' . $domain;
    }
}

if (!function_exists('active_nav')) {
    function active_nav(string $path, bool $exact = false): string {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $uri = explode('?', $uri)[0];
        if ($exact) return $uri === $path ? 'active' : '';
        return str_starts_with($uri, $path) ? 'active' : '';
    }
}

if (!function_exists('view')) {
    function view(string $template, array $data = []): string {
        return \App\Core\View::render($template, $data);
    }
}

if (!function_exists('gravatar')) {
    function gravatar(string $email, int $size = 80): string {
        $hash = md5(strtolower(trim($email)));
        return 'https://www.gravatar.com/avatar/' . $hash . '?s=' . $size . '&d=mp';
    }
}

if (!function_exists('gateway')) {
    function gateway(string $code): ?\App\Gateways\Gateway {
        return \App\Gateways\GatewayFactory::make($code);
    }
}

if (!function_exists('payment_log')) {
    function payment_log(?int $userId, ?int $invoiceId, string $gateway, string $action, string $status, string $message, float $amount = 0, string $reference = ''): void {
        try {
            $stmt = db()->prepare('INSERT INTO payment_logs (user_id, invoice_id, gateway, action, reference, amount, status, message, ip) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$userId, $invoiceId, $gateway, $action, $reference, $amount, $status, $message, $_SERVER['REMOTE_ADDR'] ?? '']);
        } catch (\Throwable $t) {
            // ignore logging failures
        }
    }
}

if (!function_exists('validate_tc_no')) {
    function validate_tc_no(string $tc): bool {
        if (!preg_match('/^[1-9][0-9]{10}$/', $tc)) return false;
        $d = str_split($tc);
        $odd = $d[0] + $d[2] + $d[4] + $d[6] + $d[8];
        $even = $d[1] + $d[3] + $d[5] + $d[7];
        if (($odd * 7 - $even) % 10 !== (int)$d[9]) return false;
        if (array_sum(array_slice($d, 0, 10)) % 10 !== (int)$d[10]) return false;
        return true;
    }
}

if (!function_exists('validate_tax_no')) {
    function validate_tax_no(string $tax): bool {
        return preg_match('/^[0-9]{10}$/', $tax) === 1;
    }
}

if (!function_exists('netlen')) {
    function netlen(): ?\App\Core\NetlenApi {
        $api = new \App\Core\NetlenApi();
        return $api->isConfigured() ? $api : null;
    }
}

if (!function_exists('generate_referral_code')) {
    function generate_referral_code(int $length = 10): string {
        return strtoupper(substr(bin2hex(random_bytes(8)), 0, $length));
    }
}

if (!function_exists('credit_affiliate')) {
    /** Credit an affiliate when a referred user pays their first invoice. */
    function credit_affiliate(int $referredUserId, float $amount, ?int $invoiceId = null): void {
        $stmt = db()->prepare('SELECT referred_by FROM users WHERE id = ?');
        $stmt->execute([$referredUserId]);
        $affiliateId = (int)$stmt->fetchColumn();
        if (!$affiliateId) return;

        if ($invoiceId) {
            $stmt = db()->prepare('SELECT id FROM affiliate_commissions WHERE referred_user_id = ? AND invoice_id = ?');
            $stmt->execute([$referredUserId, $invoiceId]);
            if ($stmt->fetch()) return; // already credited
        }

        $rate = (float)setting('affiliate_rate', 10);
        $commission = round($amount * $rate / 100, 2);
        if ($commission <= 0) return;

        db()->prepare('INSERT INTO affiliate_commissions (affiliate_id, referred_user_id, invoice_id, amount, status) VALUES (?, ?, ?, ?, "pending")')
            ->execute([$affiliateId, $referredUserId, $invoiceId, $commission]);
        db()->prepare('UPDATE users SET affiliate_balance = affiliate_balance + ? WHERE id = ?')->execute([$commission, $affiliateId]);
    }
}

if (!function_exists('validate_promo')) {
    /** Validate a promo code; returns promo row + computed discount for a given amount. */
    function validate_promo(string $code, float $amount): array {
        $code = strtoupper(trim($code));
        if ($code === '') return ['valid' => false, 'message' => ''];
        $stmt = db()->prepare('SELECT * FROM promotions WHERE code = ? AND status = 1');
        $stmt->execute([$code]);
        $promo = $stmt->fetch();
        if (!$promo) return ['valid' => false, 'message' => 'Geçersiz kupon kodu.'];
        $today = date('Y-m-d');
        if ($promo['valid_from'] && $promo['valid_from'] > $today) return ['valid' => false, 'message' => 'Kupon henüz geçerli değil.'];
        if ($promo['valid_until'] && $promo['valid_until'] < $today) return ['valid' => false, 'message' => 'Kupon süresi dolmuş.'];
        if ($promo['max_uses'] > 0 && (int)$promo['used'] >= (int)$promo['max_uses']) return ['valid' => false, 'message' => 'Kupon kullanım limiti dolmuş.'];

        $discount = $promo['discount_type'] === 'percent'
            ? round($amount * (float)$promo['discount_value'] / 100, 2)
            : min((float)$promo['discount_value'], $amount);

        return ['valid' => true, 'promo' => $promo, 'discount' => $discount];
    }
}

if (!function_exists('mail_actually_configured')) {
    /** True only when SMTP is enabled, host is set and method is smtp (real email delivery). */
    function mail_actually_configured(): bool {
        return setting('mail_method', 'php') === 'smtp'
            && (int)setting('smtp_enabled', 0) === 1
            && setting('smtp_host', '') !== '';
    }
}

if (!function_exists('show_otp_on_screen')) {
    /** Show OTP on screen as a fallback when email isn't actually deliverable. */
    function show_otp_on_screen(): bool {
        return !mail_actually_configured();
    }
}
