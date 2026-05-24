<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NetworkPaymentService
{
   
    protected string $apiVersion = "100";
    protected string $baseUrl;
    protected string $merchantId;
    protected string $merchantName;
    protected string $merchantPassword;

    public function __construct()
    {
        $this->baseUrl = config('network.base_url');
        $this->merchantId  = config('network.merchant_id');
        $this->merchantName  = config('network.merchant_name');
        $this->merchantPassword  = config('network.merchant_password');
    }

    /**
     * Create a payment session with the gateway
     *
     * @param Payment $payment
     * @return string $sessionId
     * @throws \Exception
     */
    public function createSession(Payment $payment, $description): string
    {
        try {
            // Load items if not already loaded (to get prices from payment items)
            if (!$payment->relationLoaded('items')) {
                $payment->load('items');
            }
            
            // Format amount to 2 decimal places to avoid floating point precision issues
            // Round to 2 decimal places and format as string to ensure proper precision
            $amount = round((float) $payment->amount, 2);
            
            $reference = (string) Str::uuid();
            $payload = [
                'apiOperation' => 'INITIATE_CHECKOUT',
                'interaction' => [
                    'operation' => 'PURCHASE',
                    'merchant' => ['name' => $this->merchantName],
                    'returnUrl' => env('APP_URL_API') . '/api/v1/payments/callback?trans_id=' . $payment->order_id,
                    'redirectMerchantUrl' => env('APP_URL_API') . '/api/v1/payments/callback?trans_id=' . $payment->order_id
                ],
                'order' => [
                    'id' => $payment->order_id,
                    'amount' => $amount,
                    'currency' => "USD",
                    'reference' => $reference,
                    'description' => $description
                ],
            ];

            // Make request to Network API
            $response = Http::withBasicAuth("merchant." . $this->merchantId, $this->merchantPassword)->acceptJson()
                ->post("{$this->baseUrl}/api/rest/version/{$this->apiVersion}/merchant/{$this->merchantId}/session", $payload);

            if ($response->failed()) {
                throw new \Exception('Payment gateway error: ' . $response->body());
            }

            $data = $response->json();

            if (!isset($data['session']['id'])) {
                throw new \Exception('Invalid gateway response: missing session_id');
            }

            // Return session_id for controller to store
            return $data['session']['id'];

        } catch (\Exception $e) {
            throw new \Exception('NetworkPaymentService::createSession failed - ' . $e->getMessage());
        }
    }

    public function getOrderDetails(string $orderId): array
    {
        try {
            $response = Http::withBasicAuth("merchant." . $this->merchantId, $this->merchantPassword)
            ->acceptJson()
            ->get("{$this->baseUrl}/api/rest/version/{$this->apiVersion}/merchant/{$this->merchantId}/order/{$orderId}");

            $data = $response->json();

            return $data;

        } catch (\Throwable $e) {
            throw new \Exception("Failed to process order details: {$e->getMessage()}");
        }
    }

    /**
     * Create a payment token from the checkout session (returns the token string).
     *
     * @throws \Exception
     */
    public function getToken(string $sessionId): string
    {
        $response = Http::withBasicAuth("merchant." . $this->merchantId, $this->merchantPassword)
            ->acceptJson()
            ->post("{$this->baseUrl}/api/rest/version/{$this->apiVersion}/merchant/{$this->merchantId}/token", [
                'session' => ['id' => $sessionId],
            ]);

        if ($response->failed()) {
            throw new \Exception('Token gateway error: ' . $response->body());
        }

        $data = $response->json();

        if (! is_array($data) || ($data['result'] ?? '') !== 'SUCCESS') {
            throw new \Exception('Token creation failed: ' . ($data['result'] ?? ($response->body() ?: 'invalid response')));
        }

        $token = (isset($data['token'])) ? $data['token'] : null;

        return $token;
    }
}
