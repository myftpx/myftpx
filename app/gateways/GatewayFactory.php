<?php
namespace App\Gateways;

class GatewayFactory
{
    /** Instantiate a configured gateway by its code ('paytr', 'iyzico'). */
    public static function make(string $code): ?Gateway
    {
        $stmt = db()->prepare('SELECT code, config FROM payment_gateways WHERE code = ? AND enabled = 1');
        $stmt->execute([$code]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        $config = json_decode($row['config'], true) ?: [];

        return match ($code) {
            'paytr' => new PaytrGateway('paytr', $config),
            'iyzico' => new IyzicoGateway('iyzico', $config),
            default => null,
        };
    }

    public static function exists(string $code): bool
    {
        $stmt = db()->prepare('SELECT id FROM payment_gateways WHERE code = ? AND enabled = 1');
        $stmt->execute([$code]);
        return (bool)$stmt->fetch();
    }
}
