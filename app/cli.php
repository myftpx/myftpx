<?php
/**
 * RCVXTR — CLI yardımcı araçları (SSH/terminal üzerinden)
 *
 * Kullanım:
 *   php app/cli.php otp:list      → bekleyen OTP kodlarını listeler
 *   php app/cli.php otp:screen    → OTP'yi ekranda gösterecek moda geçirir (mail ayarlanana kadar)
 *   php app/cli.php otp:disable   → OTP zorunluluğunu geçici kapatır (mail kurulana kadar)
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Bu betik yalnızca komut satırından çalıştırılabilir.');
}

require __DIR__ . '/bootstrap.php';

use App\Core\Database;

if (!Database::isInstalled()) {
    echo "Sistem kurulu değil. Önce /install ile kurun.\n";
    exit(1);
}

$cmd = $argv[1] ?? 'help';

switch ($cmd) {
    case 'otp:list':
        $rows = db()->query('SELECT email, type, code, expires_at, used FROM otp_codes WHERE used = 0 ORDER BY id DESC LIMIT 30')->fetchAll();
        if (!$rows) {
            echo "Bekleyen OTP kodu yok.\n";
        }
        foreach ($rows as $r) {
            echo str_pad($r['email'], 32) . " [" . $r['type'] . "] => " . $r['code'] . "  (son: " . $r['expires_at'] . ")\n";
        }
        break;

    case 'otp:screen':
        set_setting('mail_method', 'log');
        echo "OK — mail_method 'log' olarak ayarlandı. OTP kodları artık doğrulama ekranında görünür.\n";
        echo "Not: SMTP'yi kurduktan sonra Admin → Ayarlar'dan 'Gönderim Yöntemi'ni 'SMTP' yapın.\n";
        break;

    case 'otp:disable':
        set_setting('mail_method', 'log');
        echo "OK — mail_method 'log' yapıldı. OTP kodları ekranda görünür.\n";
        break;

    default:
        echo "RCVXTR CLI — Kullanılabilir komutlar:\n";
        echo "  php app/cli.php otp:list     → bekleyen OTP kodlarını listele\n";
        echo "  php app/cli.php otp:screen   → OTP'yi ekranda göster (mail ayarlanana kadar)\n";
        echo "  php app/cli.php otp:disable  → OTP zorunluluğunu geçici kapat\n";
        break;
}
