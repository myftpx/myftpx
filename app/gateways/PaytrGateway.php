<?php
namespace App\Gateways;

/**
 * PayTR gateway — card storage & recurring payment.
 * Real endpoints: https://www.paytr.com/odeme/api/*
 * Falls back to a deterministic simulation when api_mode = test or keys are empty.
 */
class PaytrGateway extends Gateway
{
    private string $merchantId;
    private string $merchantKey;
    private string $merchantSalt;

    public function __construct(string $code, array $config)
    {
        parent::__construct($code, $config);
        $this->merchantId = $config['merchant_id'] ?? '';
        $this->merchantKey = $config['merchant_key'] ?? '';
        $this->merchantSalt = $config['merchant_salt'] ?? '';
    }

    public function isConfigured(): bool
    {
        return $this->merchantId !== '' && $this->merchantKey !== '' && $this->merchantSalt !== '';
    }

    private function simulate(): bool
    {
        return !$this->isLive() || !$this->isConfigured();
    }

    public function saveCard(array $card): array
    {
        if ($this->simulate()) {
            $token = 'paytr_test_' . bin2hex(random_bytes(20));
            return [
                'success' => true,
                'token' => $token,
                'card_user_key' => '',
                'last4' => $this->last4($card['number'] ?? ''),
                'brand' => $this->detectBrand($card['number'] ?? ''),
                'message' => 'Kart test modunda kaydedildi (simülasyon).',
            ];
        }

        // Real PayTR card storage flow:
        // 1. get token  ->  POST https://www.paytr.com/odeme/api/token
        // 2. store card ->  POST https://www.paytr.com/odeme/api/card-store
        $tokenRes = $this->http('POST', 'https://www.paytr.com/odeme/api/token', ['Content-Type: application/x-www-form-urlencoded'], [
            'merchant_id' => $this->merchantId,
            'merchant_key' => $this->merchantKey,
            'merchant_salt' => $this->merchantSalt,
        ]);
        $tokenData = json_decode($tokenRes['body'] ?? '{}', true);
        if (($tokenData['status'] ?? '') !== 'success' || empty($tokenData['data']['token'])) {
            return ['success' => false, 'message' => 'PayTR token alınamadı: ' . ($tokenData['reason'] ?? $tokenRes['error'] ?? 'bilinmeyen hata')];
        }

        $res = $this->http('POST', 'https://www.paytr.com/odeme/api/card-store', ['Content-Type: application/x-www-form-urlencoded'], [
            'token' => $tokenData['data']['token'],
            'cc_owner' => $card['holder'] ?? '',
            'card_number' => $card['number'] ?? '',
            'expiry_month' => $card['expiry_month'] ?? '',
            'expiry_year' => $card['expiry_year'] ?? '',
            'cvv' => $card['cvv'] ?? '',
        ]);
        $data = json_decode($res['body'] ?? '{}', true);
        if (($data['status'] ?? '') !== 'success') {
            return ['success' => false, 'message' => 'PayTR kart kaydı başarısız: ' . ($data['reason'] ?? 'bilinmeyen hata')];
        }
        return [
            'success' => true,
            'token' => $data['data']['ctoken'] ?? '',
            'card_user_key' => '',
            'last4' => $data['data']['last4'] ?? $this->last4($card['number'] ?? ''),
            'brand' => $this->detectBrand($card['number'] ?? ''),
            'message' => 'Kart başarıyla kaydedildi.',
        ];
    }

    public function chargeToken(string $token, ?string $cardUserKey, float $amount, string $description): array
    {
        if ($this->simulate()) {
            return [
                'success' => true,
                'transaction_id' => 'PAYTR-TEST-' . strtoupper(substr(md5(uniqid('', true)), 0, 16)),
                'message' => 'Test modunda ödeme başarılı (simülasyon).',
            ];
        }
        $res = $this->http('POST', 'https://www.paytr.com/odeme/api/payment', ['Content-Type: application/x-www-form-urlencoded'], [
            'merchant_id' => $this->merchantId,
            'merchant_key' => $this->merchantKey,
            'merchant_salt' => $this->merchantSalt,
            'ctoken' => $token,
            'amount' => (int)round($amount * 100),
            'currency' => $this->currency,
            'description' => $description,
        ]);
        $data = json_decode($res['body'] ?? '{}', true);
        if (($data['status'] ?? '') !== 'success') {
            return ['success' => false, 'message' => 'PayTR ödeme başarısız: ' . ($data['reason'] ?? 'bilinmeyen hata')];
        }
        return ['success' => true, 'transaction_id' => $data['data']['transaction_id'] ?? uniqid('PAYTR-'), 'message' => 'Ödeme başarılı.'];
    }

    public function charge(array $card, float $amount, string $description): array
    {
        $saved = $this->saveCard($card);
        if (!$saved['success']) {
            return $saved;
        }
        $result = $this->chargeToken($saved['token'], $saved['card_user_key'] ?? '', $amount, $description);
        $result['token'] = $saved['token'];
        $result['last4'] = $saved['last4'] ?? '';
        $result['brand'] = $saved['brand'] ?? '';
        return $result;
    }

    public function refund(string $transactionId, float $amount): array
    {
        if ($this->simulate()) {
            return ['success' => true, 'transaction_id' => $transactionId, 'message' => 'Test modunda iade yapıldı (simülasyon).'];
        }
        $res = $this->http('POST', 'https://www.paytr.com/odeme/api/refund', ['Content-Type: application/x-www-form-urlencoded'], [
            'merchant_id' => $this->merchantId,
            'merchant_key' => $this->merchantKey,
            'merchant_salt' => $this->merchantSalt,
            'transaction_id' => $transactionId,
            'amount' => (int)round($amount * 100),
        ]);
        $data = json_decode($res['body'] ?? '{}', true);
        return ['success' => ($data['status'] ?? '') === 'success', 'message' => $data['reason'] ?? 'İade tamamlandı.'];
    }
}
