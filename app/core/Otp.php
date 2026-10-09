<?php
namespace App\Core;

/**
 * OTP (one-time password) generation, storage and delivery (email / SMS / WhatsApp).
 */
class Otp
{
    public static function generate(string $email, string $type = 'email'): string
    {
        $code = (string)random_int(100000, 999999);
        db()->prepare('DELETE FROM otp_codes WHERE email = ? AND type = ?')->execute([$email, $type]);
        db()->prepare('INSERT INTO otp_codes (email, code, type, expires_at) VALUES (?, ?, ?, ?)')
            ->execute([$email, $code, $type, date('Y-m-d H:i:s', strtotime('+10 minutes'))]);
        return $code;
    }

    public static function verify(string $email, string $code, string $type = 'email'): bool
    {
        $stmt = db()->prepare('SELECT * FROM otp_codes WHERE email = ? AND type = ? AND used = 0 AND expires_at > ? ORDER BY id DESC LIMIT 1');
        $stmt->execute([$email, $type, now()]);
        $row = $stmt->fetch();
        if (!$row || !hash_equals($row['code'], (string)$code)) return false;
        db()->prepare('UPDATE otp_codes SET used = 1 WHERE id = ?')->execute([$row['id']]);
        return true;
    }

    public static function sendEmail(string $email, string $code): bool
    {
        $site = setting('site_name', 'RCVXTR');
        $body = "Doğrulama kodunuz: {$code}\n\nBu kod 10 dakika geçerlidir. Eğer bu işlemi siz yapmadıysanız bu e-postayı dikkate almayın.\n\n{$site}";
        return Mailer::send($email, 'Doğrulama Kodunuz — ' . $site, $body);
    }

    public static function sendSms(string $phone, string $code): bool
    {
        $site = setting('site_name', 'RCVXTR');
        return Sms::send($phone, "{$site} doğrulama kodunuz: {$code}");
    }

    /** Deliver OTP via email and/or SMS depending on what's configured. */
    public static function deliver(string $email, string $phone, string $code): array
    {
        $sent = [];
        $sent['email'] = self::sendEmail($email, $code);
        if ((int)setting('sms_enabled', 0) === 1 && $phone !== '') {
            $sent['sms'] = self::sendSms($phone, $code);
        }
        return $sent;
    }
}
