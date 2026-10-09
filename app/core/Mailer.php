<?php
namespace App\Core;

/**
 * Minimal SMTP mailer (no external dependencies) + template-based email sending.
 */
class Mailer
{
    public static function isConfigured(): bool
    {
        return (int)setting('smtp_enabled', 0) === 1 && setting('smtp_host', '') !== '';
    }

    public static function send(string $to, string $subject, string $body, ?string $fromEmail = null, ?string $fromName = null): bool
    {
        $fromEmail = $fromEmail ?: setting('smtp_from_email', setting('admin_email', 'noreply@localhost'));
        $fromName = $fromName ?: setting('smtp_from_name', setting('site_name', 'RCVXTR'));
        $method = setting('mail_method', 'php');

        // Always log a copy for debugging / audit
        self::logMail($to, $subject, $body, $method);

        try {
            if ($method === 'log') {
                return true; // dev/test mode: only log
            }
            if ($method === 'smtp' && self::isConfigured()) {
                return self::smtpSend(setting('smtp_host'), (int)setting('smtp_port', 587), setting('smtp_encryption', 'tls'), setting('smtp_user', ''), setting('smtp_pass', ''), $fromEmail, $fromName, $to, $subject, $body);
            }
            // fallback to PHP mail()
            $headers = 'From: ' . $fromName . ' <' . $fromEmail . ">\r\n" .
                       'MIME-Version: 1.0' . "\r\n" .
                       'Content-Type: text/plain; charset=UTF-8';
            return @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);
        } catch (\Throwable $t) {
            error_log('Mailer error: ' . $t->getMessage());
            return false;
        }
    }

    private static function logMail(string $to, string $subject, string $body, string $method): void
    {
        try {
            $dir = dirname(__DIR__, 2) . '/storage/logs';
            if (!is_dir($dir)) @mkdir($dir, 0755, true);
            @file_put_contents($dir . '/mail.log', '[' . date('Y-m-d H:i:s') . "] TO: {$to} | METHOD: {$method} | SUBJECT: {$subject}\n" . $body . "\n\n", FILE_APPEND);
        } catch (\Throwable $t) {
            // ignore
        }
    }

    /** Send using an email template with variable substitution. */
    public static function sendTemplate(string $code, string $to, array $vars): bool
    {
        $stmt = db()->prepare('SELECT * FROM email_templates WHERE code = ? AND enabled = 1');
        $stmt->execute([$code]);
        $tpl = $stmt->fetch();
        if (!$tpl) return false;

        $vars['site_name'] = $vars['site_name'] ?? setting('site_name', 'RCVXTR');
        $subject = self::interpolate($tpl['subject'], $vars);
        $body = self::interpolate($tpl['body'], $vars);
        return self::send($to, $subject, $body);
    }

    public static function interpolate(string $text, array $vars): string
    {
        foreach ($vars as $k => $v) {
            $text = str_replace('{' . $k . '}', (string)$v, $text);
        }
        return $text;
    }

    private static function smtpSend(string $host, int $port, string $encryption, string $user, string $pass, string $fromEmail, string $fromName, string $to, string $subject, string $body): bool
    {
        $remote = ($encryption === 'ssl' ? 'ssl://' : '') . $host;
        // Allow self-signed / mismatched certs (common on DirectAdmin/shared hosting)
        $ctx = stream_context_create(['ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ]]);
        $fp = @stream_socket_client("{$remote}:{$port}", $errno, $errstr, 20, STREAM_CLIENT_CONNECT, $ctx);
        if (!$fp) {
            error_log('SMTP connect failed: ' . ($errstr ?: 'unknown'));
            return false;
        }
        stream_set_timeout($fp, 20);

        $read = function () use ($fp) {
            $data = '';
            while ($line = fgets($fp, 515)) {
                $data .= $line;
                if (isset($line[3]) && $line[3] === ' ') break;
            }
            return $data;
        };

        $read(); // greeting
        fwrite($fp, "EHLO localhost\r\n"); $read();
        if ($encryption === 'tls') {
            fwrite($fp, "STARTTLS\r\n"); $read();
            stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            fwrite($fp, "EHLO localhost\r\n"); $read();
        }
        if ($user !== '') {
            fwrite($fp, "AUTH LOGIN\r\n"); $read();
            fwrite($fp, base64_encode($user) . "\r\n"); $read();
            fwrite($fp, base64_encode($pass) . "\r\n"); $read();
        }
        fwrite($fp, "MAIL FROM:<{$fromEmail}>\r\n"); $read();
        fwrite($fp, "RCPT TO:<{$to}>\r\n"); $read();
        fwrite($fp, "DATA\r\n"); $read();
        $message = "From: {$fromName} <{$fromEmail}>\r\nTo: <{$to}>\r\nSubject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n" .
            "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\n" . $body . "\r\n.";
        fwrite($fp, $message . "\r\n"); $read();
        fwrite($fp, "QUIT\r\n");
        fclose($fp);
        return true;
    }
}
