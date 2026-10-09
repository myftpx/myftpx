<?php
namespace App\Gateways;

/**
 * Base class for all payment gateways.
 * Each gateway returns a standardized result array:
 *   ['success'=>bool, 'token'=>?, 'card_user_key'=>?, 'last4'=>?, 'brand'=>?,
 *    'transaction_id'=>?, 'message'=>?, 'raw'=>?]
 */
abstract class Gateway
{
    protected string $code;
    protected array $config;
    protected string $currency;

    public function __construct(string $code, array $config)
    {
        $this->code = $code;
        $this->config = $config;
        $this->currency = $config['currency'] ?? 'TRY';
    }

    public function code(): string
    {
        return $this->code;
    }

    /** Whether the gateway is configured for live processing. */
    public function isLive(): bool
    {
        $mode = strtolower($this->config['api_mode'] ?? 'test');
        return $mode === 'live' || $mode === 'prod' || $mode === 'production';
    }

    public function isConfigured(): bool
    {
        return false;
    }

    /** Save a card (tokenize) — returns token so it can be charged later. */
    abstract public function saveCard(array $card): array;

    /** Charge an amount using a previously saved card token. */
    abstract public function chargeToken(string $token, ?string $cardUserKey, float $amount, string $description): array;

    /** One-time charge with raw card data (optionally also saves the card). */
    abstract public function charge(array $card, float $amount, string $description): array;

    /** Refund a previous transaction. */
    abstract public function refund(string $transactionId, float $amount): array;

    /** POST/PUT helper via cURL. */
    protected function http(string $method, string $url, array $headers = [], $body = null): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($body) ? http_build_query($body) : $body);
        }
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ['status' => $code, 'body' => $response, 'error' => $error];
    }

    /** Mask a card number, keep last4 + detect brand. */
    protected function last4(string $number): string
    {
        return substr(preg_replace('/\D/', '', $number), -4);
    }

    protected function detectBrand(string $number): string
    {
        $n = preg_replace('/\D/', '', $number);
        if (preg_match('/^4/', $n)) return 'Visa';
        if (preg_match('/^(5[1-5]|2[2-7])/', $n)) return 'Mastercard';
        if (preg_match('/^3[47]/', $n)) return 'Amex';
        if (preg_match('/^(9792|6062|6500|6501|6502)/', $n)) return 'Troy';
        return 'Card';
    }
}
