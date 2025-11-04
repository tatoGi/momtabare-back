<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use Bog\Payment\Models\BogCard;
use Bog\Payment\Models\BogPayment;
use Bog\Payment\Services\BogAuthService;
use Bog\Payment\Services\BogPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Jobs\PostProcessProducts;

class BogPaymentController extends Controller
{
    protected BogAuthService $bogAuth;

    protected BogPaymentService $bogPayment;

    public function __construct()
    {
        $this->bogAuth = new BogAuthService;
        $this->bogPayment = new BogPaymentService;
    }

    /**
     * Create BOG payment order
     */
    public function createOrder(Request $request)
    {
        try {
            $validated = $request->validate([
                'amount' => ['required', 'numeric', 'min:0.01'],
                'currency' => ['nullable', 'string', 'size:3'],
                'callback_url' => ['required', 'url'],
                'external_order_id' => ['nullable', 'string', 'max:255'],
                'save_card' => ['nullable', 'boolean'],
                'user_id' => ['nullable', 'integer', 'exists:web_users,id'],
                'basket' => ['nullable', 'array'],
                'basket.*.product_id' => ['required_with:basket', 'string'],
                'basket.*.name' => ['required_with:basket', 'string', 'max:255'],
                'basket.*.quantity' => ['required_with:basket', 'integer', 'min:1'],
                'basket.*.unit_price' => ['required_with:basket', 'numeric', 'min:0.01'],
                'redirect_urls' => ['nullable', 'array'],
                'redirect_urls.success' => ['nullable', 'url'],
                'redirect_urls.fail' => ['nullable', 'url'],
                'language' => ['nullable', 'string', 'size:2'],
                'promo_code' => ['nullable', 'string', 'max:50'],
                'discount_amount' => ['nullable', 'numeric', 'min:0'],
            ]);

            // Clean up user_id to ensure it's properly handled
            if (isset($validated['user_id']) && ($validated['user_id'] === '' || $validated['user_id'] === true)) {
                unset($validated['user_id']);
            }

            // Get authentication token
            $token = $this->bogAuth->getAccessToken();
            if (! $token || empty($token['access_token'])) {
                Log::error('BOG Authentication failed');

                return response()->json([
                    'success' => false,
                    'message' => 'Unable to authenticate with BOG payment gateway',
                ], 500);
            }

            // Prepare payload for BOG API
            $payload = $this->prepareOrderPayload($validated);

            // Create order using package service
            $result = $this->bogPayment->createOrder($token['access_token'], $payload);

            // Log the raw response for debugging
            Log::info('BOG Order creation response', [
                'payload_sent' => $payload,
                'response_received' => $result,
                'response_type' => gettype($result),
            ]);

            // Check if result is valid
            if (! $result) {
                Log::error('BOG Order creation returned null/empty result');
                return response()->json([
                    'success' => false,
                    'message' => 'No response received from payment backend',
                    'error_code' => 'no_response',
                ], 500);
            }

            // Handle different response formats from BOG
            $orderData = null;
            $redirectUrl = null;

            if (is_array($result)) {
                // Check for various possible field names (including nested _links structure)
                $redirectUrl = $result['redirect_url'] ?? $result['payment_url'] ?? $result['checkout_url'] ??
                              $result['action_url'] ?? $result['url'] ??
                              // Check nested _links structure (BOG format)
                              ($result['_links']['redirect']['href'] ?? null) ??
                              // Alternative _links structure
                              ($result['links']['redirect']['href'] ?? null);

                $orderData = $result;
            } elseif (is_object($result)) {
                // Handle object responses
                $redirectUrl = $result->redirect_url ?? $result->payment_url ?? $result->checkout_url ??
                              $result->action_url ?? $result->url ??
                              // Check nested _links structure (BOG format)
                              ($result->_links->redirect->href ?? null) ??
                              // Alternative _links structure
                              ($result->links->redirect->href ?? null);

                $orderData = (array) $result;
            }

            // If no redirect URL found, log detailed info and return error
            if (! $redirectUrl) {
                Log::error('No redirect URL found in BOG response', [
                    'result' => $result,
                    'result_keys' => is_array($result) ? array_keys($result) : 'not_array',
                    'payload' => $payload,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'No redirect URL returned by payment backend',
                    'error_code' => 'no_redirect_url',
                    'data' => [
                        'result_type' => gettype($result),
                        'result_keys' => is_array($result) ? array_keys($result) : 'not_array',
                        'received_data' => $result,
                    ],
                ], 500);
            }

            // Save payment record to database if not already saved
            try {
                // Generate external order ID if not provided
                $externalOrderId = $validated['external_order_id'] ?? Str::uuid();
                $bogOrderId = $orderData['order_id'] ?? $orderData['id'] ?? $externalOrderId;

                // Check if payment record already exists (prevent duplicates)
                $existingPayment = BogPayment::where('bog_order_id', $bogOrderId)->first();

                if ($existingPayment) {
                    Log::info('BOG Payment record already exists, updating instead of creating', [
                        'payment_id' => $existingPayment->id,
                        'bog_order_id' => $bogOrderId,
                    ]);

                    // Update existing payment record
                    $existingPayment->update([
                        'external_order_id' => $externalOrderId,
                        'amount' => $validated['amount'],
                        'currency' => $validated['currency'] ?? 'GEL',
                        'redirect_url' => $redirectUrl,
                        'payload_data' => $payload,
                        'response_data' => $orderData,
                        'promo_code' => $validated['promo_code'] ?? null,
                        'discount_amount' => $validated['discount_amount'] ?? null,
                        'original_amount' => isset($validated['discount_amount']) ? ($validated['amount'] + $validated['discount_amount']) : $validated['amount'],
                        'updated_at' => now(),
                    ]);
                } else {
                    // Create new payment record
                    $payment = BogPayment::create([
                        'bog_order_id' => $bogOrderId,
                        'external_order_id' => $externalOrderId,
                        'user_id' => $validated['user_id'] ?? $request->user('sanctum')?->id ?? null,
                        'amount' => $validated['amount'],
                        'currency' => $validated['currency'] ?? 'GEL',
                        'status' => 'pending',
                        'save_card_requested' => $validated['save_card'] ?? false,
                        'callback_url' => $validated['callback_url'],
                        'redirect_url' => $redirectUrl,
                        'payload_data' => $payload,
                        'response_data' => $orderData,
                        'promo_code' => $validated['promo_code'] ?? null,
                        'discount_amount' => $validated['discount_amount'] ?? null,
                        'original_amount' => isset($validated['discount_amount']) ? ($validated['amount'] + $validated['discount_amount']) : $validated['amount'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    Log::info('BOG Payment record created successfully', [
                        'payment_id' => $payment->id,
                        'bog_order_id' => $payment->bog_order_id,
                        'external_order_id' => $payment->external_order_id,
                    ]);
                }

            } catch (\Exception $e) {
                Log::warning('Failed to save payment record, but continuing', [
                    'error' => $e->getMessage(),
                ]);
            }

            // Return success with redirect URL
            Log::info('BOG Payment order created successfully', [
                'redirect_url' => $redirectUrl,
                'order_data' => $orderData,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'redirect_url' => $redirectUrl,
                    'order_id' => $orderData['order_id'] ?? $orderData['id'] ?? null,
                    'status' => $orderData['status'] ?? 'pending',
                    'raw_response' => $orderData,
                ],
                'message' => 'Payment order created successfully',
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('BOG Order creation validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('BOG Order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment order',
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => app()->environment('local') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    /**
     * Handle BOG payment callback
     * This method receives callbacks from BOG payment system
     * and should return HTTP 200 to confirm successful receipt
     */
    public function handleCallback(Request $request)
    {
        try {
            // Get raw request body for signature verification
            $rawBody = $request->getContent();

            Log::info('BOG Payment Callback received', [
                'headers' => $request->headers->all(),
                'raw_body' => $rawBody,
                'parsed_body' => $request->all(),
            ]);

            // Verify signature before processing
            if (! $this->verifyCallbackSignature($rawBody, $request->header('Callback-Signature'))) {
                Log::error('BOG Callback signature verification failed', [
                    'signature' => $request->header('Callback-Signature'),
                    'raw_body_length' => strlen($rawBody),
                ]);

                // Still return 200 but log the failure for monitoring
                // According to documentation, if callback fails, business should use Get Payment Details
                Log::warning('BOG Callback signature verification failed - will need to verify manually');
            }

            // Parse the callback JSON structure as per documentation
            $callbackData = json_decode($rawBody, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('BOG Callback JSON parsing failed', [
                    'json_error' => json_last_error_msg(),
                    'raw_body' => $rawBody,
                ]);

                return response('', 200); // Return 200 as per documentation requirement
            }

            // Validate required fields according to documentation structure
            if (! isset($callbackData['event']) || $callbackData['event'] !== 'order_payment') {
                Log::error('BOG Callback invalid event type', [
                    'event' => $callbackData['event'] ?? 'missing',
                    'expected' => 'order_payment',
                ]);

                return response('', 200);
            }

            if (! isset($callbackData['body']['order_id'])) {
                Log::error('BOG Callback missing order_id in body', [
                    'callback_data' => $callbackData,
                ]);

                return response('', 200);
            }

            // Extract payment details from body
            $paymentData = $callbackData['body'];
            $orderId = $paymentData['order_id'];
            $status = $paymentData['status'] ?? 'unknown';

            // Find the payment record
            $payment = BogPayment::where('bog_order_id', $orderId)->first();

            if (! $payment) {
                Log::warning('BOG Callback - Payment not found', [
                    'order_id' => $orderId,
                    'available_fields' => array_keys($paymentData),
                ]);

                // Return 200 as per documentation, but log for manual verification
                Log::info('BOG Callback - Payment not found, will need manual verification');
                return response('', 200);
            }

            // Update payment status with full callback data
            $payment->update([
                'status' => $status,
                'transaction_id' => $paymentData['transaction_id'] ?? null,
                'amount' => $paymentData['amount'] ?? $payment->amount,
                'currency' => $paymentData['currency'] ?? $payment->currency,
                'callback_data' => $callbackData,
                'callback_received_at' => now(),
                'updated_at' => now(),
            ]);

            // Handle successful payment
            if (in_array($status, ['completed', 'success', 'paid'])) {
                $this->handleSuccessfulPayment($payment, $paymentData);
            }

            // Handle failed/cancelled payments
            if (in_array($status, ['failed', 'cancelled', 'declined', 'error'])) {
                $this->handleFailedPayment($payment, $paymentData);
            }

            // Handle refunds
            if (in_array($status, ['refunded', 'partially_refunded'])) {
                $this->handleRefundPayment($payment, $paymentData);
            }

            Log::info('BOG Payment Callback processed successfully', [
                'order_id' => $orderId,
                'status' => $status,
                'payment_id' => $payment->id,
                'event' => $callbackData['event'],
                'zoned_request_time' => $callbackData['zoned_request_time'] ?? 'unknown',
            ]);

            // Return HTTP 200 as per documentation requirement
            return response('', 200);

        } catch (\Exception $e) {
            Log::error('BOG Callback processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            // Still return 200 as per documentation - callback system will retry if needed
            Log::warning('BOG Callback failed - will need manual verification');
            return response('', 200);
        }
    }

    // Get BOG order details
    public function orderDetails(Request $request, $orderId)
    {
        $token = $this->bogAuth->getAccessToken();
        if (! $token || empty($token['access_token'])) {
            return response()->json(['success' => false, 'message' => 'Unable to authenticate with BOG'], 500);
        }
        $result = $this->bogPayment->getOrderDetails($token['access_token'], $orderId);

        if ($result === null) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve order details',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    // Save card for future payments
    /**
     * Save Card for Recurring Payments
     *
     * According to BOG documentation:
     * POST /payments/v1/ecommerce/orders/:order_id
     *
     * The bank offers businesses the option to save a customer's card information,
     * with the customer's consent, to enable future payments without having to
     * enter card details again.
     *
     * Returns 202 ACCEPTED when successful
     */
    public function saveCard(Request $request, $orderId)
    {
        $validated = $request->validate([
            'idempotency_key' => 'nullable|uuid',
        ]);

        $token = $this->bogAuth->getAccessToken();
        if (! $token || empty($token['access_token'])) {
            return response()->json(['success' => false, 'message' => 'Unable to authenticate with BOG'], 500);
        }

        Log::info('BOG Save Card Request', [
            'order_id' => $orderId,
            'user_id' => $request->user('sanctum')?->id,
            'idempotency_key' => $validated['idempotency_key'] ?? null,
        ]);

        $result = $this->bogPayment->saveCard($token['access_token'], $orderId, $validated['idempotency_key'] ?? null);

        // Handle the array response from the service
        if (isset($result['success'])) {
            $statusCode = $result['status'] ?? ($result['success'] ? 202 : 400);
            $statusCode = is_numeric($statusCode) ? (int) $statusCode : 400;

            return response()->json($result, $statusCode);
        }

        // Fallback for unexpected response format
        Log::warning('BOG Save Card - Unexpected response format', [
            'order_id' => $orderId,
            'response' => $result,
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Unexpected response format',
            'status' => 500,
        ], 500);
    }

    // Charge saved card for payment
    public function chargeCard(Request $request, $parentOrderId)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|size:3',
            'callback_url' => 'nullable|url',
            'external_order_id' => 'nullable|string|max:255',
            'save_card' => 'nullable|boolean',
            'pre_authorize' => 'nullable|boolean',
        ]);
        $token = $this->bogAuth->getAccessToken();
        if (! $token || empty($token['access_token'])) {
            return response()->json(['success' => false, 'message' => 'Unable to authenticate with BOG'], 500);
        }
        $result = $this->bogPayment->chargeCard($token['access_token'], $parentOrderId, $validated);

        // Ensure we always return a valid HTTP status code
        $statusCode = 400; // default
        if (is_array($result)) {
            $statusCode = $result['status'] ?? ($result['success'] ? 200 : 400);
            $statusCode = is_numeric($statusCode) ? (int) $statusCode : 400;
        }

        return response()->json($result, $statusCode);
    }

    /**
     * Get user's payment history
     */
    public function getUserPayments(Request $request)
    {
        try {
            $user = $request->user('sanctum');

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                ], 401);
            }

            $perPage = $request->get('per_page', 15);
            $status = $request->get('status');
            $fromDate = $request->get('from_date');
            $toDate = $request->get('to_date');

            $query = BogPayment::where('user_id', $user->id)
                ->orderBy('created_at', 'desc');

            // Apply filters
            if ($status) {
                $query->where('status', $status);
            }

            if ($fromDate) {
                $query->whereDate('created_at', '>=', $fromDate);
            }

            if ($toDate) {
                $query->whereDate('created_at', '<=', $toDate);
            }

            $payments = $query->paginate($perPage);

            $formattedPayments = $payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'bog_order_id' => $payment->bog_order_id,
                    'external_order_id' => $payment->external_order_id,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'status' => $payment->status,
                    'transaction_id' => $payment->transaction_id,
                    'save_card_requested' => $payment->save_card_requested,
                    'created_at' => $payment->created_at->toIso8601String(),
                    'updated_at' => $payment->updated_at->toIso8601String(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedPayments,
                'pagination' => [
                    'current_page' => $payments->currentPage(),
                    'last_page' => $payments->lastPage(),
                    'per_page' => $payments->perPage(),
                    'total' => $payments->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve user payments', [
                'error' => $e->getMessage(),
                'user_id' => $request->user('sanctum')?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment history',
            ], 500);
        }
    }

    // Pay with saved card
    /**
     * Payment by the Saved Card
     *
     * According to BOG documentation:
     * POST /payments/v1/ecommerce/orders/:parent_order_id
     *
     * This method allows businesses to enable customers to make payments on the
     * bank's webpage without entering their card details again. This feature is
     * useful when a customer has previously saved their card information during
     * a successful card payment transaction for future use.
     */
    public function payWithSavedCard(Request $request, $parentOrderId)
    {
        try {
            $validated = $request->validate([
                'callback_url' => 'required|url',
                'total_amount' => 'required|numeric|min:0.01',
                'basket' => 'required|array',
                'currency' => 'nullable|string|size:3',
                'language' => 'nullable|string|size:2',
            ]);

            $token = $this->bogAuth->getAccessToken();
            if (! $token || empty($token['access_token'])) {
                Log::error('BOG Authentication failed for payWithSavedCard');

                return response()->json([
                    'success' => false,
                    'message' => 'Unable to authenticate with BOG payment gateway',
                    'error_code' => 'auth_failed',
                ], 500);
            }

            // Ensure 'amount' is set for vendor compatibility
            $validated['amount'] = $validated['total_amount'];
            $result = $this->bogPayment->payWithSavedCard($token['access_token'], $parentOrderId, $validated);

            // Log the raw response for debugging
            Log::info('BOG Payment with saved card response', [
                'parent_order_id' => $parentOrderId,
                'response_received' => $result,
                'response_type' => gettype($result),
            ]);

            // Check if result is valid
            if (! $result || ! is_array($result)) {
                Log::error('BOG Payment with saved card returned invalid result');

                return response()->json([
                    'success' => false,
                    'message' => 'No response received from payment backend',
                    'error_code' => 'no_response',
                ], 500);
            }

            // Check for success
            if (! ($result['success'] ?? false)) {
                Log::error('BOG Payment with saved card failed', [
                    'result' => $result,
                    'parent_order_id' => $parentOrderId,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Payment with saved card failed',
                    'error_code' => 'payment_failed',
                    'data' => [
                        'result' => $result,
                        'details' => $result['data'] ?? null,
                    ],
                ], $result['status'] ?? 400);
            }

            // Handle different response formats from BOG
            $orderData = null;
            $redirectUrl = null;

            // Extract order data and redirect URL
            $responseData = $result['data'] ?? $result;

            if (is_array($responseData)) {
                $redirectUrl = $responseData['redirect_url'] ?? $responseData['payment_url'] ?? $responseData['checkout_url'] ??
                              $responseData['action_url'] ?? $responseData['url'] ??
                              ($responseData['_links']['redirect']['href'] ?? null) ??
                              ($responseData['links']['redirect']['href'] ?? null);
                $orderData = $responseData;
            } elseif (is_object($responseData)) {
                $redirectUrl = $responseData->redirect_url ?? $responseData->payment_url ?? $responseData->checkout_url ??
                              $responseData->action_url ?? $responseData->url ??
                              ($responseData->_links->redirect->href ?? null) ??
                              ($responseData->links->redirect->href ?? null);
                $orderData = (array) $responseData;
            }

            // If no redirect URL found, it may be a background payment flow
            // Return the response as-is and let the frontend handle it
            if (! $redirectUrl) {
                Log::info('BOG Payment with saved card - no redirect URL (may be background payment)', [
                    'parent_order_id' => $parentOrderId,
                    'response_data' => $responseData,
                ]);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'order_id' => $orderData['order_id'] ?? $orderData['id'] ?? $parentOrderId,
                        'status' => $orderData['status'] ?? 'pending',
                        'redirect_url' => null,
                        'raw_response' => $orderData,
                    ],
                    'message' => 'Payment with saved card initiated successfully',
                ], 201);
            }

            // Return success with redirect URL and order info
            Log::info('BOG Payment with saved card created successfully', [
                'redirect_url' => $redirectUrl,
                'order_data' => $orderData,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'redirect_url' => $redirectUrl,
                    'order_id' => $orderData['order_id'] ?? $orderData['id'] ?? $parentOrderId,
                    'status' => $orderData['status'] ?? 'pending',
                    'raw_response' => $orderData,
                ],
                'message' => 'Payment order created successfully',
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('BOG Payment with saved card validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('BOG Payment with saved card failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment with saved card',
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => app()->environment('local') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    // Process automatic payment with saved card
    public function processAutomaticPayment(Request $request, $parentOrderId)
    {
        $validated = $request->validate([
            'callback_url' => 'nullable|url',
            'external_order_id' => 'nullable|string|max:255',
            'idempotency_key' => 'nullable|uuid',
        ]);
        $token = $this->bogAuth->getAccessToken();
        if (! $token || empty($token['access_token'])) {
            return response()->json(['success' => false, 'message' => 'Unable to authenticate with BOG'], 500);
        }
        $result = $this->bogPayment->processAutomaticPayment($token['access_token'], $parentOrderId, $validated);

        // Ensure we always return a valid HTTP status code
        $statusCode = 400; // default
        if (is_array($result)) {
            $statusCode = $result['status'] ?? ($result['success'] ? 200 : 400);
            $statusCode = is_numeric($statusCode) ? (int) $statusCode : 400;
        }

        return response()->json($result, $statusCode);
    }

    // Confirm pre-authorization
    public function confirmPreAuthorization(Request $request, $orderId)
    {
        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'idempotency_key' => 'nullable|uuid',
        ]);
        $token = $this->bogAuth->getAccessToken();
        if (! $token || empty($token['access_token'])) {
            return response()->json(['success' => false, 'message' => 'Unable to authenticate with BOG'], 500);
        }
        $result = $this->bogPayment->confirmPreAuthorization($token['access_token'], $orderId, $validated);

        // Ensure we always return a valid HTTP status code
        $statusCode = 400; // default
        if (is_array($result)) {
            $statusCode = $result['status'] ?? ($result['success'] ? 200 : 400);
            $statusCode = is_numeric($statusCode) ? (int) $statusCode : 400;
        }

        return response()->json($result, $statusCode);
    }

    // Reject pre-authorization
    public function rejectPreAuthorization(Request $request, $orderId)
    {
        $validated = $request->validate([
            'description' => 'nullable|string|max:255',
            'idempotency_key' => 'nullable|uuid',
        ]);
        $token = $this->bogAuth->getAccessToken();
        if (! $token || empty($token['access_token'])) {
            return response()->json(['success' => false, 'message' => 'Unable to authenticate with BOG'], 500);
        }
        $result = $this->bogPayment->rejectPreAuthorization($token['access_token'], $orderId, $validated);

        // Ensure we always return a valid HTTP status code
        $statusCode = 400; // default
        if (is_array($result)) {
            $statusCode = $result['status'] ?? ($result['success'] ? 200 : 400);
            $statusCode = is_numeric($statusCode) ? (int) $statusCode : 400;
        }

        return response()->json($result, $statusCode);
    }


    public function bulkUpdateRentalStatus(Request $request)
    {
        $validated = $request->validate([
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|integer|exists:products,id',
            'products.*.rental_start_date' => 'nullable|date',
            'products.*.rental_end_date' => 'nullable|date|after_or_equal:products.*.rental_start_date',
            'products.*.quantity' => 'nullable|integer|min:1',
            'payment_id' => 'nullable|integer|exists:bog_payments,id',
            'idempotency_key' => 'nullable|string',
        ]);

        $user = $request->user('sanctum');
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'User not authenticated'], 401);
        }

        // Optional idempotency: fast-return if same operation already processed recently
        if (! empty($validated['idempotency_key'])) {
            $cacheKey = 'bulk_rental_status:' . $validated['idempotency_key'];
            if (! Cache::add($cacheKey, 1, 60 * 60)) { // 1 hour
                return response()->json(['success' => true, 'message' => 'Already processed'], 200);
            }
        }

        $productDatas = $validated['products'];
        $productIds = collect($productDatas)->pluck('product_id')->unique()->values()->all();

        $tries = 3;
        $attempt = 0;
        $updatedProducts = [];
        $errors = [];

        while (true) {
            $attempt++;
            try {
                $result = DB::transaction(function () use ($productIds, $productDatas, $user, $validated, &$updatedProducts, &$errors) {
                    // Lock rows for update to prevent concurrent modifications
                    $products = \App\Models\Product::whereIn('id', $productIds)
                        ->lockForUpdate()
                        ->get()
                        ->keyBy('id');

                    foreach ($productDatas as $pData) {
                        $pid = (int) $pData['product_id'];
                        try {
                            if (! isset($products[$pid])) {
                                $errors[] = "Product {$pid} not found";
                                continue;
                            }

                            $product = $products[$pid];

                            $updateData = [
                                'is_ordered' => true,
                                'ordered_at' => now(),
                                'ordered_by' => $user->id,
                            ];

                            if (! empty($pData['rental_start_date']) && ! empty($pData['rental_end_date'])) {
                                $updateData['is_rented'] = true;
                                $updateData['rented_at'] = now();
                                $updateData['rental_start_date'] = $pData['rental_start_date'];
                                $updateData['rental_end_date'] = $pData['rental_end_date'];
                                $updateData['rented_by'] = null; // keep compatibility with vendor note
                            }

                            // Example inventory change if quantity provided
                            if (! empty($pData['quantity'])) {
                                $requestedQty = (int) $pData['quantity'];
                                $available = (int) ($product->available_quantity ?? 0);
                                $product->available_quantity = max(0, $available - $requestedQty);
                            }

                            $product->fill($updateData);
                            $product->save();

                            // Ensure pivot insertion is idempotent: use insertOrIgnore
                            if (! empty($validated['payment_id'])) {
                                DB::table('bog_payment_product')->insertOrIgnore([
                                    'bog_payment_id' => $validated['payment_id'],
                                    'product_id' => $pid,
                                    'quantity' => $pData['quantity'] ?? 1,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }

                            $updatedProducts[] = [
                                'product_id' => $product->id,
                                'status' => 'updated',
                                'is_rented' => $product->is_rented ?? false,
                                'rental_start_date' => $product->rental_start_date ?? null,
                                'rental_end_date' => $product->rental_end_date ?? null,
                            ];

                            Log::info('Product rental status updated via bulk endpoint', [
                                'product_id' => $product->id,
                                'user_id' => $user->id,
                                'is_rented' => $product->is_rented ?? false,
                                'rental_dates' => [
                                    'start' => $product->rental_start_date ?? null,
                                    'end' => $product->rental_end_date ?? null,
                                ],
                            ]);
                        } catch (\Exception $e) {
                            $errors[] = "Failed to update product {$pid}: {$e->getMessage()}";
                            Log::error('Failed to update product rental status', ['product_id' => $pid, 'error' => $e->getMessage()]);
                        }
                    }

                    return ['updated' => $updatedProducts, 'errors' => $errors];
                }, 5);

                // success
                break;
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = $e->getMessage();
                $isDeadlock = Str::contains($msg, ['Deadlock', 'deadlock', '1213', '40001']);
                Log::warning('bulkUpdateRentalStatus transaction failed attempt '.$attempt, ['exception' => $msg]);
                if ($attempt >= $tries || ! $isDeadlock) {
                    return response()->json(['success' => false, 'message' => 'Failed to update products', 'error' => $msg], 500);
                }
                // backoff
                usleep(100000 * $attempt);
                continue;
            } catch (\Exception $e) {
                Log::error('bulkUpdateRentalStatus unexpected error', ['exception' => $e->getMessage()]);
                return response()->json(['success' => false, 'message' => 'Failed to update products', 'error' => $e->getMessage()], 500);
            }
        }

        // Dispatch post-processing job after commit (if payment_id provided)
        if (! empty($validated['payment_id'])) {
            try {
                PostProcessProducts::dispatch($validated['payment_id'], $productIds, $user->id);
            } catch (\Exception $e) {
                Log::warning('Failed to dispatch PostProcessProducts job', ['error' => $e->getMessage()]);
            }
        }

        return response()->json([
            'success' => count($updatedProducts) > 0,
            'message' => count($updatedProducts) > 0 ? 'Product rental status updated successfully' : 'No products were updated',
            'updated_products' => $updatedProducts,
            'errors' => $errors,
            'summary' => [
                'total_requested' => count($productDatas),
                'successfully_updated' => count($updatedProducts),
                'failed' => count($errors),
            ],
        ], 200);
    }



    /**
     * Prepare order payload for BOG API
     */
    private function prepareOrderPayload(array $validated): array
    {
        $payload = [
            'external_order_id' => $validated['external_order_id'] ?? Str::uuid(),
            'user_id' => $validated['user_id'] ?? Auth::id(),
            'callback_url' => $validated['callback_url'],
            'save_card' => $validated['save_card'] ?? false,
            'language' => $validated['language'] ?? 'en',
        ];

        // Prepare purchase units
        if (isset($validated['basket']) && is_array($validated['basket'])) {
            // Process basket items and calculate rental pricing
            $processedBasket = [];
            $totalAmount = 0;

            foreach ($validated['basket'] as $item) {
                $processedItem = $item;

                // Calculate rental pricing if rental dates are provided
                if (isset($item['start_date']) && isset($item['end_date'])) {
                    $startDate = new \DateTime($item['start_date']);
                    $endDate = new \DateTime($item['end_date']);

                    // Calculate number of days (inclusive)
                    $days = $startDate->diff($endDate)->days + 1;
                    $dailyRate = $item['unit_price'];
                    $rentalTotal = $dailyRate * $days;

                    $processedItem['rental_days'] = $days;
                    $processedItem['daily_rate'] = $dailyRate;
                    $processedItem['rental_total'] = $rentalTotal;
                    $processedItem['total_amount'] = $rentalTotal;

                    Log::info('Rental pricing calculated', [
                        'product_id' => $item['product_id'],
                        'start_date' => $item['start_date'],
                        'end_date' => $item['end_date'],
                        'days' => $days,
                        'daily_rate' => $dailyRate,
                        'rental_total' => $rentalTotal,
                    ]);

                    $totalAmount += $rentalTotal;
                } else {
                    // Regular pricing for non-rental items
                    $processedItem['total_amount'] = $item['unit_price'] * $item['quantity'];
                    $totalAmount += $processedItem['total_amount'];
                }

                $processedBasket[] = $processedItem;
            }

            $payload['purchase_units'] = [
                'total_amount' => $totalAmount,
                'currency' => $validated['currency'] ?? 'GEL',
                'basket' => $processedBasket,
            ];

            Log::info('BOG Order payload prepared with rental calculations', [
                'total_amount' => $totalAmount,
                'currency' => $validated['currency'] ?? 'GEL',
                'basket_count' => count($processedBasket),
                'has_rental_items' => collect($processedBasket)->contains(function($item) {
                    return isset($item['rental_days']);
                }),
            ]);
        } else {
            $payload['purchase_units'] = [
                'total_amount' => $validated['amount'],
                'currency' => $validated['currency'] ?? 'GEL',
                'basket' => [
                    [
                        'product_id' => 'general_payment',
                        'name' => 'Payment',
                        'quantity' => 1,
                        'unit_price' => $validated['amount'],
                        'total_amount' => $validated['amount'],
                    ],
                ],
            ];
        }

        // Add redirect URLs if provided
        if (isset($validated['redirect_urls'])) {
            $payload['redirect_urls'] = $validated['redirect_urls'];
        }

        return $payload;
    }

    /**
     * Handle successful payment
     */
    private function handleSuccessfulPayment(BogPayment $payment, array $callbackData): void
    {
        try {
            // Update payment status
            $payment->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // If save card was requested and user is authenticated
            if ($payment->save_card_requested && $payment->user_id) {
                $this->handleCardSaving($payment, $callbackData);
            }

            // Trigger any business logic for successful payment
            // You can add your custom logic here (e.g., send confirmation email, update inventory, etc.)

            Log::info('BOG Payment completed successfully', [
                'payment_id' => $payment->id,
                'bog_order_id' => $payment->bog_order_id,
                'amount' => $payment->amount,
                'user_id' => $payment->user_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error handling successful payment', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle failed payment
     */
    private function handleFailedPayment(BogPayment $payment, array $callbackData): void
    {
        try {
            $payment->update([
                'status' => 'failed',
                'failed_at' => now(),
                'failure_reason' => $callbackData['status'] ?? 'unknown',
            ]);

            Log::info('BOG Payment failed', [
                'payment_id' => $payment->id,
                'bog_order_id' => $payment->bog_order_id,
                'status' => $callbackData['status'],
                'user_id' => $payment->user_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error handling failed payment', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Verify callback signature using SHA256withRSA algorithm
     *
     * @param string $rawBody The raw request body content
     * @param string|null $signature The signature from Callback-Signature header
     * @return bool True if signature is valid, false otherwise
     */
    private function verifyCallbackSignature(string $rawBody, ?string $signature): bool
    {
        if (empty($signature)) {
            Log::warning('BOG Callback signature is empty');
            return false;
        }

        // BOG Public Key from documentation
        $publicKey = <<<EOD
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAu4RUyAw3+CdkS3ZNILQh
zHI9Hemo+vKB9U2BSabppkKjzjjkf+0Sm76hSMiu/HFtYhqWOESryoCDJoqffY0Q
1VNt25aTxbj068QNUtnxQ7KQVLA+pG0smf+EBWlS1vBEAFbIas9d8c9b9sSEkTrr
TYQ90WIM8bGB6S/KLVoT1a7SnzabjoLc5Qf/SLDG5fu8dH8zckyeYKdRKSBJKvhx
tcBuHV4f7qsynQT+f2UYbESX/TLHwT5qFWZDHZ0YUOUIvb8n7JujVSGZO9/+ll/g
4ZIWhC1MlJgPObDwRkRd8NFOopgxMcMsDIZIoLbWKhHVq67hdbwpAq9K9WMmEhPn
PwIDAQAB
-----END PUBLIC KEY-----
EOD;

        try {
            // Verify the signature using SHA256withRSA
            $isValid = openssl_verify(
                $rawBody,
                base64_decode($signature),
                $publicKey,
                OPENSSL_ALGO_SHA256
            );

            if ($isValid === 1) {
                Log::info('BOG Callback signature verified successfully');
                return true;
            } elseif ($isValid === 0) {
                Log::warning('BOG Callback signature verification failed - invalid signature');
                return false;
            } else {
                Log::error('BOG Callback signature verification error', [
                    'openssl_error' => openssl_error_string(),
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('BOG Callback signature verification exception', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Handle refund payment
     */
    private function handleRefundPayment(BogPayment $payment, array $callbackData): void
    {
        try {
            $payment->update([
                'status' => $callbackData['status'],
                'refunded_at' => now(),
                'refund_amount' => $callbackData['refund_amount'] ?? null,
                'refund_reason' => $callbackData['refund_reason'] ?? null,
            ]);

            Log::info('BOG Payment refunded', [
                'payment_id' => $payment->id,
                'bog_order_id' => $payment->bog_order_id,
                'refund_amount' => $callbackData['refund_amount'] ?? null,
                'status' => $callbackData['status'],
                'user_id' => $payment->user_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error handling refund payment', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle card saving after successful payment
     */
    private function handleCardSaving(BogPayment $payment, array $callbackData): void
    {
        try {
            // Check if card data is available in callback
            if (isset($callbackData['card_token']) || isset($callbackData['card_mask'])) {
                // Save card to database
                BogCard::create([
                    'user_id' => $payment->user_id,
                    'parent_order_id' => $payment->bog_order_id,
                    'card_token' => $callbackData['card_token'] ?? null,
                    'card_mask' => $callbackData['card_mask'] ?? '****',
                    'card_type' => $callbackData['card_type'] ?? 'unknown',
                    'card_holder_name' => $callbackData['card_holder_name'] ?? null,
                    'expiry_month' => $callbackData['expiry_month'] ?? null,
                    'expiry_year' => $callbackData['expiry_year'] ?? null,
                    'is_default' => ! BogCard::where('user_id', $payment->user_id)->exists(),
                    'metadata' => [
                        'saved_from_payment' => true,
                        'payment_id' => $payment->id,
                    ],
                ]);

                Log::info('Card saved from payment', [
                    'payment_id' => $payment->id,
                    'user_id' => $payment->user_id,
                    'card_mask' => $callbackData['card_mask'] ?? '****',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error saving card from payment', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
