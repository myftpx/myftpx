<?php
namespace App\Core;

/**
 * SMS / WhatsApp sender abstraction.
 * Supported gateways: whatsapp (CallMeBot / WhatsApp Business API), netgsm, twilio.
 * Falls back to simulation when not configured (dev mode).
 */
class Sms
{
    public static function send(string $phone, string $message): bool
    {
        $gateway = setting('sms_gateway', 'whatsapp');
        $apiKey = setting('sms_api_key', '');
        $apiSecret = setting('sms_api_secret', '');
        $sender = setting('sms_sender', '');

        // Normalize phone to digits
        $phone = preg_replace('/\D/', '', $phone);

        if ($apiKey === '') {
            // Simulation mode (no gateway configured)
            error_log("[SMS simulation -> {$phone}] {$message}");
            return true;
        }

        return match ($gateway) {
            'callmebot' => self::callmebot($phone, $message, $apiKey),
            'twilio' => self::twilio($phone, $message, $apiKey, $apiSecret, $sender),
            'netgsm' => self::netgsm($phone, $message, $apiKey, $apiSecret, $sender),
            default => self::callmebot($phone, $message, $apiKey),
        };
    }

    private static function callmebot(string $phone, string $message, string $apiKey): bool
    {
        // CallMeBot WhatsApp gateway: GET https://api.callmebot.com/whatsapp.php?phone=...&text=...&apikey=...
        $url = 'https://api.callmebot.com/whatsapp.php?' . http_build_query([
            'phone' => $phone, 'text' => $message, 'apikey' => $apiKey,
        ]);
        $res = @file_get_contents($url);
        return $res !== false && !str_contains((string)$res, 'ERROR');
    }

    private static function twilio(string $phone, string $message, string $sid, string $token, string $from): bool
    {
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_USERPWD => $sid . ':' . $token,
            CURLOPT_POSTFIELDS => http_build_query(['To' => '+' . $phone, 'From' => $from, 'Body' => $message]),
        ]);
        curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code >= 200 && $code < 300;
    }

    private static function netgsm(string $phone, string $message, string $user, string $pass, string $from): bool
    {
        $url = 'https://api.netgsm.com.tr/sms/send/get';
        $body = http_build_query([
            'usercode' => $user, 'password' => $pass, 'gsmno' => $phone,
            'message' => $message, 'msgheader' => $from ?: '0850xxxxxxx',
        ]);
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => $body]);
        $res = (string)curl_exec($ch);
        curl_close($ch);
        return str_contains($res, '00');
    }
}
