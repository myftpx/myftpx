<?php
namespace App\Core;

/**
 * Recurring / automatic billing engine.
 * Finds due services with auto_renew enabled and charges their saved card.
 */
class Billing
{
    public static function runDueServices(): array
    {
        $summary = ['processed' => 0, 'success' => 0, 'failed' => 0, 'details' => []];
        $today = date('Y-m-d');

        $stmt = db()->prepare("SELECT * FROM services WHERE status = 'active' AND auto_renew = 1 AND next_due_date IS NOT NULL AND next_due_date <= ?");
        $stmt->execute([$today]);
        $services = $stmt->fetchAll();

        foreach ($services as $service) {
            $result = self::processService($service);
            $summary['processed']++;
            if ($result['success']) {
                $summary['success']++;
            } else {
                $summary['failed']++;
            }
            $summary['details'][] = $result;
        }

        return $summary;
    }

    private static function processService(array $service): array
    {
        $userId = (int)$service['user_id'];
        $serviceId = (int)$service['id'];
        $amount = (float)$service['amount'];
        $domain = $service['domain'] ?: ('Hizmet #' . $serviceId);

        $stmt = db()->prepare('SELECT name FROM products WHERE id = ?');
        $stmt->execute([$service['product_id']]);
        $productName = $stmt->fetch()['name'] ?? 'Hizmet';

        $card = self::resolveCard($userId, $service['card_id']);
        if (!$card) {
            payment_log($userId, null, '', 'recurring.attempt', 'failed', 'Otomatik ödeme için kayıtlı kart bulunamadı.', $amount, 'service:' . $serviceId);
            return ['success' => false, 'service_id' => $serviceId, 'message' => 'Kayıtlı kart yok'];
        }

        $gw = \App\Gateways\GatewayFactory::make($card['gateway']);
        if (!$gw) {
            payment_log($userId, null, $card['gateway'], 'recurring.attempt', 'failed', 'Ödeme kuruluşu aktif değil.', $amount, 'service:' . $serviceId);
            return ['success' => false, 'service_id' => $serviceId, 'message' => 'Gateway aktif değil'];
        }

        $cycle = $service['billing_cycle'];
        $cycleLabel = self::cycleLabel($cycle);
        $invoiceId = (new \App\Controllers\StoreController())->createInvoice($userId, [
            ['description' => $productName . ' — ' . $cycleLabel . ' otomatik ödeme' . ($service['domain'] ? ' (' . $service['domain'] . ')' : ''), 'amount' => $amount],
        ]);

        $stmt = db()->prepare('SELECT total FROM invoices WHERE id = ?');
        $stmt->execute([$invoiceId]);
        $total = (float)$stmt->fetch()['total'];

        payment_log($userId, $invoiceId, $card['gateway'], 'recurring.attempt', 'info', 'Otomatik ödeme denemesi başlatıldı.', $total, 'service:' . $serviceId);

        $res = $gw->chargeToken($card['card_token'], $card['card_user_key'] ?: null, $total, $productName . ' — ' . $domain);

        if (!empty($res['success'])) {
            (new \App\Controllers\StoreController())->markInvoicePaid($invoiceId, $card['gateway']);

            $nextDue = date('Y-m-d', strtotime(self::cycleDuration($cycle)));
            db()->prepare('UPDATE services SET next_due_date = ? WHERE id = ?')->execute([$nextDue, $serviceId]);

            payment_log($userId, $invoiceId, $card['gateway'], 'recurring.success', 'success', 'Otomatik ödeme başarılı: ' . ($res['transaction_id'] ?? ''), $total, $res['transaction_id'] ?? '');

            return ['success' => true, 'service_id' => $serviceId, 'invoice_id' => $invoiceId, 'message' => 'Ödeme alındı'];
        }

        payment_log($userId, $invoiceId, $card['gateway'], 'recurring.failed', 'failed', 'Otomatik ödeme başarısız: ' . ($res['message'] ?? 'bilinmeyen hata'), $total);

        return ['success' => false, 'service_id' => $serviceId, 'invoice_id' => $invoiceId, 'message' => $res['message'] ?? 'Ödeme başarısız'];
    }

    private static function resolveCard(int $userId, $cardId): ?array
    {
        // Prefer service-specific card, then the user's default card
        if ($cardId) {
            $stmt = db()->prepare("SELECT * FROM saved_cards WHERE id = ? AND user_id = ? AND status = 'active'");
            $stmt->execute([$cardId, $userId]);
            $card = $stmt->fetch();
            if ($card) return $card;
        }
        $stmt = db()->prepare("SELECT * FROM saved_cards WHERE user_id = ? AND status = 'active' ORDER BY is_default DESC, id DESC LIMIT 1");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    public static function cycleLabel(string $cycle): string
    {
        return ['monthly' => 'Aylık', 'quarterly' => '3 Aylık', 'semi_annual' => '6 Aylık', 'annually' => 'Yıllık', 'biennially' => '2 Yıllık'][$cycle] ?? $cycle;
    }

    public static function cycleDuration(string $cycle): string
    {
        return ['monthly' => '+1 month', 'quarterly' => '+3 months', 'semi_annual' => '+6 months', 'annually' => '+1 year', 'biennially' => '+2 years'][$cycle] ?? '+1 month';
    }
}
