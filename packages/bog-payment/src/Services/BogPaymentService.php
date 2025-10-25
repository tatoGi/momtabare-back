<?php

namespace Bog\Payment\Services;

use Bog\Payment\Models\BogPayment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BogPaymentService
{
    protected $lastHttpStatus = null;
    protected $lastError = null;

    public function getLastHttpStatus(): ?int
    {
        return $this->lastHttpStatus;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function payWithSavedCard(string $accessToken, string $parentOrderId, array $paymentData): array
    {
        $endpoint = "/v1/orders/{$parentOrderId}/payments";
        $payload = [
            'callback_url' => $paymentData['callback_url'],
            'amount' => $paymentData['amount'],
            'basket' => $paymentData['basket'],
            'language' => $paymentData['language'] ?? 'ka',
        ];
        $response = $this->makeRequest('POST', $endpoint, $accessToken, $payload);

        if (isset($response['error'])) {
            Log::error('BOG Payment with saved card failed', [
                'error' => $response['error'],
                'parent_order_id' => $parentOrderId,
            ]);
            return ['success' => false, 'message' => $response['error']['message'] ?? 'Payment failed', 'status' => $this->lastHttpStatus ?? 400];
        }
        return ['success' => true, 'data' => $response, 'message' => 'Payment initiated successfully'];
    }

    protected function makeRequest(string $method, string $url, string $accessToken, array $data = [], array $headers = []): ?array
    {
        $this->lastHttpStatus = null;
        $this->lastError = null;

        try {
            $baseUrl = config('bog-payment.api_base_url', 'https://api.bog.ge/payments');
            $fullUrl = str_starts_with($url, 'http') ? $url : $baseUrl . $url;
            $http = Http::withToken($accessToken)->withHeaders($headers)->withOptions(['debug' => config('app.debug')])->acceptJson();
            $response = $http->$method($fullUrl, $data);
            $this->lastHttpStatus = $response->status();

            if ($response->successful()) {
                return $response->json();
            }

            $this->lastError = $response->body();
            Log::error('BOG API Error', ['status' => $response->status(), 'url' => $fullUrl]);
            return null;
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            Log::error('BOG API Exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function createOrder(string $accessToken, array $payload, ?string $idempotencyKey = null, ?string $acceptLanguage = 'en')
    {
        try {
            $tempOrderId = 'temp_' . uniqid();
            $bogPayment = new BogPayment([
                'bog_order_id' => $tempOrderId,
                'external_order_id' => $payload['external_order_id'] ?? null,
                'user_id' => $payload['user_id'] ?? null,
                'amount' => $payload['purchase_units'][0]['amount']['value'] ?? 0,
                'currency' => $payload['purchase_units'][0]['amount']['currency_code'] ?? 'GEL',
                'status' => 'pending',
                'request_payload' => $payload,
                'save_card_requested' => $payload['save_card'] ?? false,
            ]);

            if (! $bogPayment->save()) {
                throw new \Exception('Failed to save payment to database');
            }

            $transformedPayload = $this->transformPayloadForBogApi($payload);
            $response = Http::withHeaders(['Content-Type' => 'application/json', 'Accept' => 'application/json', 'Authorization' => 'Bearer ' . $accessToken])
                ->withOptions(['debug' => config('app.debug') ? fopen('php://stderr', 'w') : false, 'verify' => config('app.env') === 'production'])
                ->timeout(30)
                ->post(config('bog-payment.orders_url', 'https://api.bog.ge/payments/v1/ecommerce/orders'), $transformedPayload);

            $responseBody = $response->json() ?? $response->body();
            $this->lastHttpStatus = $response->status();

            if (! $response->successful()) {
                throw new \Exception($responseBody['error_description'] ?? $responseBody['message'] ?? 'Unknown error');
            }

            $bogPayment->update([
                'bog_order_id' => $responseBody['id'] ?? $tempOrderId,
                'response_data' => $responseBody,
                'redirect_url' => $responseBody['_links']['redirect']['href'] ?? null,
                'status' => 'created',
            ]);

            return $responseBody;
        } catch (\Exception $e) {
            Log::error('BOG Payment - Error in createOrder', ['error' => $e->getMessage()]);
            if (isset($bogPayment) && $bogPayment instanceof BogPayment) {
                $bogPayment->update(['status' => 'failed', 'response_data' => ['error' => $e->getMessage()]]);
            }
            throw $e;
        }
    }

    private function transformPayloadForBogApi(array $payload): array
    {
        if (isset($payload['purchase_units']) && is_array($payload['purchase_units'])) {
            $purchaseUnits = $payload['purchase_units'];
            if (array_keys($purchaseUnits) === range(0, count($purchaseUnits) - 1)) {
                if (isset($purchaseUnits[0]['amount'], $purchaseUnits[0]['items'])) {
                    $firstUnit = $purchaseUnits[0];
                    return array_merge($payload, ['purchase_units' => [
                        'total_amount' => $firstUnit['amount']['value'] ?? 0,
                        'currency' => $firstUnit['amount']['currency_code'] ?? 'GEL',
                        'basket' => array_map(fn($item) => [
                            'product_id' => $item['sku'] ?? $item['product_id'] ?? uniqid(),
                            'name' => $item['name'] ?? 'Product',
                            'quantity' => $item['quantity'] ?? 1,
                            'unit_price' => $item['unit_price'] ?? 0,
                            'total_amount' => ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0),
                        ], $firstUnit['items'] ?? []),
                    ]]);
                }
            } elseif (isset($purchaseUnits['basket']) && is_array($purchaseUnits['basket'])) {
                return array_merge($payload, ['purchase_units' => [
                    'total_amount' => $purchaseUnits['total_amount'] ?? 0,
                    'currency' => $purchaseUnits['currency'] ?? 'GEL',
                    'basket' => array_map(fn($item) => [
                        'product_id' => $item['product_id'] ?? $item['sku'] ?? uniqid(),
                        'name' => $item['name'] ?? 'Product',
                        'quantity' => $item['quantity'] ?? 1,
                        'unit_price' => $item['unit_price'] ?? 0,
                        'total_amount' => ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0),
                    ], $purchaseUnits['basket']),
                ]]);
            }
        }
        return $payload;
    }

    public function getOrderDetails(string $accessToken, string $orderId): ?array
    {
        $url = config('bog-payment.orders_url', 'https://api.bog.ge/payments/v1/ecommerce/orders') . '/' . $orderId;
        $response = $this->makeRequest('get', $url, $accessToken);
        if (! $response) {
            Log::error('BOG API - Failed to get order details', ['order_id' => $orderId]);
        }
        return $response;
    }

    public function saveCard(string $accessToken, string $orderId, ?string $idempotencyKey = null): array
    {
        $url = config('bog-payment.orders_url', 'https://api.bog.ge/payments/v1/ecommerce/orders') . "/{$orderId}/save-card";
        $headers = ['Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $accessToken];
        if ($idempotencyKey) {
            $headers['Idempotency-Key'] = $idempotencyKey;
        }
        $response = $this->makeRequest('POST', $url, $accessToken, [], $headers);
        if (! $response) {
            return ['success' => false, 'message' => 'Failed to save card', 'status' => $this->lastHttpStatus ?? 500];
        }
        return ['success' => true, 'data' => $response, 'message' => 'Card saved successfully'];
    }

    public function chargeCard(string $accessToken, string $parentOrderId, array $paymentData): array
    {
        $url = config('bog-payment.orders_url', 'https://api.bog.ge/payments/v1/ecommerce/orders') . "/{$parentOrderId}/payments";
        $payload = [
            'amount' => $paymentData['amount'],
            'currency' => $paymentData['currency'] ?? 'GEL',
            'capture_method' => 'AUTOMATIC',
            'callback_url' => $paymentData['callback_url'] ?? null,
        ];
        $response = $this->makeRequest('POST', $url, $accessToken, $payload);
        if (! $response) {
            return ['success' => false, 'message' => 'Failed to charge card', 'status' => $this->lastHttpStatus ?? 500];
        }
        return ['success' => true, 'data' => $response, 'message' => 'Card charged successfully'];
    }
}
