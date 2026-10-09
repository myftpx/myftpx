<?php
namespace App\Gateways;

/**
 * iyzico gateway — card storage (kayıtlı kart) & recurring payment.
 * Real endpoints: https://api.iyzipay.com / https://sandbox-api.iyzipay.com
 * Auth header: IYZWS <base64(api_key:secret_key)>
 */
class IyzicoGateway extends Gateway
{
    private string $apiKey;
    private string $secretKey;
    private string $baseUrl;

    public function __construct(string $code, array $config)
    {
        parent::__construct($code, $config);
        $this->apiKey = $config['api_key'] ?? '';
        $this->secretKey = $config['secret_key'] ?? '';
        $mode = strtolower($config['api_mode'] ?? 'sandbox');
        $this->baseUrl = in_array($mode, ['live', 'prod', 'production'], true)
            ? 'https://api.iyzipay.com'
            : 'https://sandbox-api.iyzipay.com';
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '' && $this->secretKey !== '';
    }

    private function simulate(): bool
    {
        return !$this->isLive() || !$this->isConfigured();
    }

    private function headers(): array
    {
        return [
            'Authorization: IYZWS ' . base64_encode($this->apiKey . ':' . $this->secretKey),
            'Content-Type: application/json',
            'Accept: application/json',
        ];
    }

    public function saveCard(array $card): array
    {
        if ($this->simulate()) {
            return [
                'success' => true,
                'token' => 'iyz_test_' . bin2hex(random_bytes(20)),
                'card_user_key' => 'iyz_user_' . bin2hex(random_bytes(16)),
                'last4' => $this->last4($card['number'] ?? ''),
                'brand' => $this->detectBrand($card['number'] ?? ''),
                'message' => 'Kart sandbox modunda kaydedildi (simülasyon).',
            ];
        }

        $body = json_encode([
            'locale' => 'tr',
            'conversationId' => uniqid('', true),
            'externalId' => $card['external_id'] ?? uniqid('ext', true),
            'email' => $card['email'] ?? '',
            'card' => [
                'cardAlias' => $card['alias'] ?? 'RCVXTR Kart',
                'cardHolderName' => $card['holder'] ?? '',
                'cardNumber' => $card['number'] ?? '',
                'expireMonth' => $card['expiry_month'] ?? '',
                'expireYear' => $card['expiry_year'] ?? '',
            ],
        ]);

        $res = $this->http('POST', $this->baseUrl . '/payment/card', $this->headers(), $body);
        $data = json_decode($res['body'] ?? '{}', true);

        if (($data['status'] ?? '') !== 'success') {
            return ['success' => false, 'message' => 'iyzico kart kaydı başarısız: ' . ($data['errorMessage'] ?? 'bilinmeyen hata')];
        }
        return [
            'success' => true,
            'token' => $data['cardToken'] ?? '',
            'card_user_key' => $data['cardUserKey'] ?? '',
            'last4' => $data['lastFourDigits'] ?? $this->last4($card['number'] ?? ''),
            'brand' => $data['cardAssociation'] ?? $this->detectBrand($card['number'] ?? ''),
            'message' => 'Kart başarıyla kaydedildi.',
        ];
    }

    public function chargeToken(string $token, ?string $cardUserKey, float $amount, string $description): array
    {
        if ($this->simulate()) {
            return [
                'success' => true,
                'transaction_id' => 'IYZ-TEST-' . strtoupper(substr(md5(uniqid('', true)), 0, 16)),
                'message' => 'Sandbox modunda ödeme başarılı (simülasyon).',
            ];
        }

        $body = json_encode([
            'locale' => 'tr',
            'conversationId' => uniqid('', true),
            'price' => number_format($amount, 2, '.', ''),
            'paidPrice' => number_format($amount, 2, '.', ''),
            'currency' => $this->currency,
            'installment' => 1,
            'basketId' => uniqid('basket', true),
            'paymentChannel' => 'WEB',
            'paymentGroup' => 'PRODUCT',
            'paymentCard' => ['cardToken' => $token, 'cardUserKey' => $cardUserKey ?? ''],
            'buyer' => $this->buyer(),
            'billingAddress' => $this->address(),
            'basketItems' => [[
                'id' => uniqid('item', true), 'name' => $description,
                'category1' => 'Hosting', 'itemType' => 'VIRTUAL',
                'price' => number_format($amount, 2, '.', ''),
            ]],
        ]);

        $res = $this->http('POST', $this->baseUrl . '/payment/auth', $this->headers(), $body);
        $data = json_decode($res['body'] ?? '{}', true);

        if (($data['status'] ?? '') !== 'success') {
            return ['success' => false, 'message' => 'iyzico ödeme başarısız: ' . ($data['errorMessage'] ?? 'bilinmeyen hata')];
        }
        return ['success' => true, 'transaction_id' => $data['paymentId'] ?? uniqid('IYZ-'), 'message' => 'Ödeme başarılı.'];
    }
    public function charge(array $card, float $amount, string $description): array
    {
        $saved = $this->saveCard($card);
        if (!$saved['success']) {
            return $saved;
        }
        $result = $this->chargeToken($saved['token'], $saved['card_user_key'] ?? '', $amount, $description);
        $result['token'] = $saved['token'];
        $result['card_user_key'] = $saved['card_user_key'] ?? '';
        $result['last4'] = $saved['last4'] ?? '';
        $result['brand'] = $saved['brand'] ?? '';
        return $result;
    }

    public function refund(string $transactionId, float $amount): array
    {
        if ($this->simulate()) {
            return ['success' => true, 'transaction_id' => $transactionId, 'message' => 'Sandbox modunda iade yapıldı (simülasyon).'];
        }
        $body = json_encode([
            'locale' => 'tr', 'conversationId' => uniqid('', true),
            'paymentTransactionId' => $transactionId,
            'price' => number_format($amount, 2, '.', ''),
            'currency' => $this->currency,
        ]);
        $res = $this->http('POST', $this->baseUrl . '/payment/refund', $this->headers(), $body);
        $data = json_decode($res['body'] ?? '{}', true);
        return ['success' => ($data['status'] ?? '') === 'success', 'message' => $data['errorMessage'] ?? 'İade tamamlandı.'];
    }

    private function buyer(): array
    {
        return [
            'id' => 'BUYER', 'name' => 'Müşteri', 'surname' => 'RCVXTR',
            'identityNumber' => '11111111111', 'email' => 'customer@example.com',
            'gsmNumber' => '+905000000000', 'registrationAddress' => 'N/A',
            'city' => 'İstanbul', 'country' => 'Türkiye',
        ];
    }

    private function address(): array
    {
        return ['contactName' => 'Müşteri', 'city' => 'İstanbul', 'country' => 'Türkiye', 'address' => 'N/A'];
    }
}
