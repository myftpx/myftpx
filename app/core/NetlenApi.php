<?php
namespace App\Core;

/**
 * Netlen.com.tr REST API v2 client (domain reseller).
 * Base URL: https://api.netlen.com.tr/v2
 * Auth: Authorization: Bearer <api_key>
 */
class NetlenApi
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct(?string $apiKey = null, ?string $baseUrl = null)
    {
        $this->apiKey = $apiKey ?: setting('netlen_api_key', '');
        $this->baseUrl = rtrim($baseUrl ?: setting('netlen_api_url', 'https://api.netlen.com.tr/v2'), '/');
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    public function isEnabled(): bool
    {
        return (int)setting('netlen_enabled', 0) === 1 && $this->isConfigured();
    }

    private function request(string $method, string $path, ?array $body = null, array $extraHeaders = []): array
    {
        $url = $this->baseUrl . $path;
        $ch = curl_init($url);
        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
        ];
        foreach ($extraHeaders as $h) {
            $headers[] = $h;
        }
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 40,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        return ['status' => $httpCode, 'data' => json_decode($response, true), 'error' => $error];
    }

    public function listDomains(int $page = 1, int $perPage = 50): array
    {
        return $this->normalize($this->request('GET', "/domains?page={$page}&per_page={$perPage}"));
    }

    public function checkAvailability(string $domain): array
    {
        return $this->normalize($this->request('GET', '/domains/check?domain=' . urlencode($domain)));
    }

    public function getPricing(?string $tld = null): array
    {
        $path = '/domains/pricing' . ($tld ? '?tld=' . urlencode(ltrim($tld, '.')) : '');
        return $this->normalize($this->request('GET', $path));
    }

    public function registerDomain(string $domain, int $years, array $contact, array $nameservers = [], bool $whoisPrivacy = false, bool $autoRenew = false): array
    {
        $payload = ['domain' => $domain, 'years' => $years, 'whois_privacy' => $whoisPrivacy, 'auto_renew' => $autoRenew];
        if ($nameservers) $payload['nameservers'] = $nameservers;
        if ($contact) $payload['contact'] = $contact;
        return $this->normalize($this->request('POST', '/domains', $payload, ['Idempotency-Key: ' . $this->idempotencyKey()]));
    }

    public function transferDomain(string $domain, string $eppCode, array $contact = [], array $nameservers = []): array
    {
        $payload = ['domain' => $domain, 'epp_code' => $eppCode];
        if ($contact) $payload['contact'] = $contact;
        if ($nameservers) $payload['nameservers'] = $nameservers;
        return $this->normalize($this->request('POST', '/domains/transfer', $payload, ['Idempotency-Key: ' . $this->idempotencyKey()]));
    }

    public function renewDomain(string $domain, int $years = 1): array
    {
        return $this->normalize($this->request('POST', '/domains/renew', ['domain' => $domain, 'years' => $years], ['Idempotency-Key: ' . $this->idempotencyKey()]));
    }

    public function getNameservers(string $domain): array
    {
        return $this->normalize($this->request('GET', '/domains/' . urlencode($domain) . '/nameservers'));
    }

    public function setNameservers(string $domain, array $nameservers): array
    {
        return $this->normalize($this->request('PUT', '/domains/' . urlencode($domain) . '/nameservers', ['nameservers' => $nameservers]));
    }

    public function setAutoRenew(string $domain, bool $enabled): array
    {
        return $this->normalize($this->request('PUT', '/domains/' . urlencode($domain) . '/auto-renew', ['auto_renew' => $enabled]));
    }

    public function setWhoisPrivacy(string $domain, bool $enabled): array
    {
        return $this->normalize($this->request('PUT', '/domains/' . urlencode($domain) . '/whois-privacy', ['whois_privacy' => $enabled]));
    }

    public function getEppCode(string $domain): array
    {
        return $this->normalize($this->request('GET', '/domains/' . urlencode($domain) . '/epp-code'));
    }

    public function getBalance(): array
    {
        return $this->normalize($this->request('GET', '/billing/balance'));
    }

    private function idempotencyKey(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
    }

    private function normalize(array $res): array
    {
        $success = $res['status'] >= 200 && $res['status'] < 300;
        $data = $res['data'] ?? null;
        $errorMsg = null;
        if (!$success) {
            $err = $data['error'] ?? null;
            $errorMsg = is_array($err) ? ($err['message'] ?? $err['code'] ?? 'UPSTREAM_ERROR') : ($err ?: ('HTTP ' . $res['status']));
        }
        return [
            'success' => $success,
            'data' => $data['data'] ?? $data,
            'meta' => $data['meta'] ?? null,
            'message' => $errorMsg,
            'raw' => $res['data'],
        ];
    }
}
