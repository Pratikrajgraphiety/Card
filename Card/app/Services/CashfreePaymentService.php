<?php

namespace App\Services;

final class CashfreePaymentService
{
    private array $config;

    public function __construct()
    {
        $this->config = config('payment.cashfree', []);
    }

    public function isConfigured(): bool
    {
        return trim((string) ($this->config['app_id'] ?? '')) !== ''
            && trim((string) ($this->config['secret_key'] ?? '')) !== '';
    }

    public function mode(): string
    {
        return strtolower((string) ($this->config['environment'] ?? 'sandbox')) === 'production'
            ? 'production'
            : 'sandbox';
    }

    public function sdkUrl(): string
    {
        return (string) ($this->config['sdk_url'] ?? 'https://sdk.cashfree.com/js/v3/cashfree.js');
    }

    public function createOrder(array $user, array $plan, string $orderId): array
    {
        $payload = [
            'order_id' => $orderId,
            'order_amount' => round((float) $plan['price'], 2),
            'order_currency' => strtoupper((string) ($plan['currency'] ?? 'INR')),
            'customer_details' => $this->customerDetails($user),
            'order_meta' => [
                'return_url' => url('payment/return?order_id={order_id}'),
            ],
            'order_note' => 'AstitvaHub ' . ($plan['name'] ?? 'plan') . ' upgrade',
            'order_tags' => [
                'user_id' => (string) ($user['id'] ?? ''),
                'plan_slug' => (string) ($plan['slug'] ?? ''),
            ],
        ];

        return $this->request('POST', '/orders', $payload);
    }

    public function fetchOrder(string $orderId): array
    {
        return $this->request('GET', '/orders/' . rawurlencode($orderId));
    }

    private function customerDetails(array $user): array
    {
        $phone = preg_replace('/\D+/', '', (string) ($user['phone'] ?? ''));
        if (strlen((string) $phone) > 10) {
            $phone = substr((string) $phone, -10);
        }
        if (strlen((string) $phone) < 10) {
            $fallback = preg_replace('/\D+/', '', (string) (config('app.contact.phones.0') ?? '9999999999'));
            $phone = strlen((string) $fallback) >= 10 ? substr((string) $fallback, -10) : '9999999999';
        }

        $details = [
            'customer_id' => 'USER_' . (int) ($user['id'] ?? 0),
            'customer_phone' => $phone,
        ];

        if (!empty($user['name'])) {
            $details['customer_name'] = mb_substr((string) $user['name'], 0, 80);
        }
        if (!empty($user['email'])) {
            $details['customer_email'] = mb_substr((string) $user['email'], 0, 190);
        }

        return $details;
    }

    private function request(string $method, string $path, ?array $payload = null): array
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('Cashfree credentials are not configured.');
        }

        $url = rtrim($this->baseUrl(), '/') . $path;
        $headers = [
            'Content-Type: application/json',
            'x-api-version: ' . ($this->config['api_version'] ?? '2025-01-01'),
            'x-client-id: ' . $this->config['app_id'],
            'x-client-secret: ' . $this->config['secret_key'],
        ];

        if (function_exists('curl_init')) {
            $body = $this->curlRequest($method, $url, $headers, $payload, $statusCode);
        } else {
            $body = $this->streamRequest($method, $url, $headers, $payload, $statusCode);
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Cashfree returned an unreadable response.');
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            $message = (string) ($decoded['message'] ?? $decoded['code'] ?? 'Cashfree request failed.');
            throw new \RuntimeException($message);
        }

        return $decoded;
    }

    private function curlRequest(string $method, string $url, array $headers, ?array $payload, ?int &$statusCode): string
    {
        $handle = curl_init($url);
        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
        ]);

        if ($payload !== null) {
            curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($payload, JSON_THROW_ON_ERROR));
        }

        $body = curl_exec($handle);
        $statusCode = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $error = curl_error($handle);
        curl_close($handle);

        if ($body === false) {
            throw new \RuntimeException($error ?: 'Cashfree request failed.');
        }

        return (string) $body;
    }

    private function streamRequest(string $method, string $url, array $headers, ?array $payload, ?int &$statusCode): string
    {
        $context = stream_context_create([
            'http' => [
                'method' => strtoupper($method),
                'header' => implode("\r\n", $headers),
                'content' => $payload === null ? '' : json_encode($payload, JSON_THROW_ON_ERROR),
                'ignore_errors' => true,
                'timeout' => 30,
            ],
        ]);

        $body = file_get_contents($url, false, $context);
        $statusCode = 0;
        foreach ($http_response_header ?? [] as $header) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#', $header, $matches)) {
                $statusCode = (int) $matches[1];
                break;
            }
        }

        if ($body === false) {
            throw new \RuntimeException('Cashfree request failed.');
        }

        return (string) $body;
    }

    private function baseUrl(): string
    {
        if ($this->mode() === 'production') {
            return (string) ($this->config['production_api_url'] ?? 'https://api.cashfree.com/pg');
        }

        return (string) ($this->config['sandbox_api_url'] ?? 'https://sandbox.cashfree.com/pg');
    }
}
