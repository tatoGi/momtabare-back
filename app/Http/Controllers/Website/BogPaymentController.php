<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\BogPayment;
use App\Services\Frontend\BogAuthService;
use App\Services\Frontend\BogPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BogPaymentController extends Controller
{
    protected $bogAuth;

    protected $bogPayment;

    public function __construct(BogAuthService $bogAuth, BogPaymentService $bogPayment)
    {
        $this->bogAuth = $bogAuth;
        $this->bogPayment = $bogPayment;
    }

    /**
     * Test endpoint to verify requests are reaching the controller
     */
    public function testEndpoint(Request $request)
    {
        Log::info('BOG Payment - Test endpoint reached', [
            'timestamp' => now(),
            'request_data' => $request->all(),
            'headers' => $request->headers->all(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Test endpoint reached successfully',
            'data' => $request->all(),
        ]);
    }

    /**
     * Obtain BOG OAuth access token using client credentials.
     */
    public function getToken()
    {
        $result = $this->bogAuth->getAccessToken();

        if (! $result || empty($result['access_token'])) {
            return response()->json(['success' => false, 'message' => 'Unable to get token'], 500);
        }

        return response()->json([
            'access_token' => $result['access_token'],
            'token_type' => $result['token_type'] ?? 'Bearer',
            'expires_in' => $result['expires_in'] ?? null,
        ]);
    }

    /**
     * Create order at BOG and return redirect link
     */
    /**
     * Create a new payment order with BOG
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createOrder(Request $request)
    {
        // Debug: Log when method is called

        try {
            // Debug: Log raw request data before validation

            $validated = $request->validate([
                'callback_url' => 'required|url',
                'purchase_units' => 'required|array',
                'purchase_units.total_amount' => 'required|numeric|min:0.01',
                'purchase_units.currency' => 'required|string|size:3',
                'purchase_units.basket' => 'required|array|min:1',
                'purchase_units.basket.*.product_id' => 'required|string',
                'purchase_units.basket.*.quantity' => 'required|integer|min:1',
                'purchase_units.basket.*.unit_price' => 'required|numeric|min:0.01',
                'purchase_units.basket.*.name' => 'required|string',
                'redirect_urls' => 'required|array',
                'redirect_urls.success' => 'required|url',
                'redirect_urls.fail' => 'required|url',
                'application_type' => 'sometimes|string|in:web,mobile',
                'capture' => 'sometimes|string|in:automatic,manual',
                'external_order_id' => 'nullable|string|max:100',
                'language' => 'sometimes|string|in:en,ka,ru',
                'save_card' => 'sometimes|boolean',
                'user_id' => 'sometimes|integer|exists:web_users,id', // Validate against web_users table
            ]);
             $user = $request->user('sanctum');
            // Custom validation: if save_card is true, user must be authenticated
            if (($validated['save_card'] ?? false) && ! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required to save card. Please log in first.',
                    'error_code' => 'authentication_required',
                ], 401);
            }

            // Get authentication token
            $tokenResult = $this->bogAuth->getAccessToken();
            if (! $tokenResult || empty($tokenResult['access_token'])) {
                throw new \Exception('Failed to authenticate with BOG');
            }

            // Prepare the payload for BOG
            $payload = [
                'application_type' => $validated['application_type'] ?? 'web',
                'capture' => $validated['capture'] ?? 'automatic',
                'callback_url' => $validated['callback_url'],
                'purchase_units' => [
                    'total_amount' => $validated['purchase_units']['total_amount'],
                    'currency' => $validated['purchase_units']['currency'] ?? 'GEL',
                    'basket' => array_map(function ($item) {
                        return [
                            'product_id' => $item['product_id'],
                            'name' => $item['name'],
                            'quantity' => $item['quantity'],
                            'unit_price' => $item['unit_price'],
                            'total_amount' => $item['quantity'] * $item['unit_price'],
                        ];
                    }, $validated['purchase_units']['basket']),
                ],
                'redirect_urls' => [
                    'success' => $validated['redirect_urls']['success'],
                    'fail' => $validated['redirect_urls']['fail'],
                ],
                'save_card' => $validated['save_card'] ?? false,
                'language' => $validated['language'] ?? 'en',
            ];

            // Add optional fields
            // Add optional fields
            if (! empty($validated['external_order_id'])) {
                $payload['external_order_id'] = $validated['external_order_id'];
            }

            // Add user_id if available (but don't make it required)
            if (Auth::check()) {
                $payload['user_id'] = Auth::id();
            } elseif ($validated['save_card'] ?? false) {
                // If user wants to save card but is not authenticated, return error
                Log::warning('BOG Payment - User attempted to save card without authentication', [
                    'save_card' => $validated['save_card'],
                    'user_authenticated' => false,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required to save card. Please log in first.',
                    'error_code' => 'authentication_required',
                ], 401);
            }

            // Create the order
            $response = $this->bogPayment->createOrder(
                $tokenResult['access_token'],
                $payload,
                (string) \Illuminate\Support\Str::uuid(), // idempotency key
                $validated['language'] ?? 'en',
            );

            if (! $response) {
                throw new \Exception($this->bogPayment->getLastError() ?? 'Failed to create order');
            }

            // Log successful order creation
            $orderId = $response['id'] ?? null;
            $redirectUrl = $response['_links']['redirect']['href'] ?? null;

            // Create payment record in bog_payments table
            try {
                $payment = \App\Models\BogPayment::create([
                    'bog_order_id' => $orderId,
                    'external_order_id' => $validated['external_order_id'] ?? null,
                    'user_id' => null, // FK constraint issue: points to users table, not web_users
                    'amount' => $validated['purchase_units']['total_amount'],
                    'currency' => $validated['purchase_units']['currency'] ?? 'GEL',
                    'status' => $response['status'] ?? 'created',
                    'request_payload' => [
                        'basket' => $validated['purchase_units']['basket'],
                        'callback_url' => $validated['callback_url'],
                        'redirect_urls' => $validated['redirect_urls'],
                        'web_user_id' => $validated['user_id'] ?? $user->id ?? null, // Store web_user_id from frontend
                        'save_card' => $validated['save_card'] ?? false,
                        'language' => $validated['language'] ?? 'en',
                    ],
                    'response_data' => $response,
                    'save_card_requested' => $validated['save_card'] ?? false,
                ]);

                Log::info('BOG Payment record created for new order', [
                    'payment_id' => $payment->id,
                    'bog_order_id' => $orderId,
                    'web_user_id' => $validated['user_id'] ?? $user->id ?? null,
                    'amount' => $validated['purchase_units']['total_amount'],
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create BOG payment record for new order', [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'bog_order_id' => $orderId,
                    'web_user_id' => $validated['user_id'] ?? $user->id ?? null,
                ]);
                // Don't fail the order creation if payment record fails
            }

            return response()->json([
                'success' => true,
                'order_id' => $orderId,
                'redirect_url' => $redirectUrl,
                'data' => $response,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Debug: Log validation errors
            Log::error('BOG Payment - Validation failed in createOrder', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                    'error_code' => 'validation_failed',
                ],
                422,
            );
        } catch (\Exception $e) {
            // Debug: Log any other errors
            Log::error('BOG Payment - Error in createOrder', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'error_code' => 'order_creation_failed',
                ],
                500,
            );
        }
    }

    /**
     * Proxy BOG receipt details by order id
     */
    public function orderDetails($orderId)
    {
        $tokenResult = $this->bogAuth->getAccessToken();
        if (! $tokenResult || empty($tokenResult['access_token'])) {
            return response()->json(['success' => false, 'message' => 'Unable to authenticate with BOG'], 500);
        }

        $details = $this->bogPayment->getOrderDetails($tokenResult['access_token'], $orderId);
        if (! $details) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch order details'], 500);
        }

        return response()->json($details);
    }

    /**
     * Handle BOG payment callback
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleCallback(Request $request)
    {
        // Get the order ID from the callback
        $orderId = $request->input('order_id');

        if (! $orderId) {
            Log::error('No order_id in BOG callback', $request->all());

            return response()->json(['status' => 'error', 'message' => 'No order_id provided']);
        }

        // Find the payment in database
        $payment = BogPayment::where('bog_order_id', $orderId)->first();

        if (! $payment) {
            Log::error('Payment not found for order_id: '.$orderId);

            return response()->json(['status' => 'error', 'message' => 'Payment not found']);
        }

        try {
            // Verify payment status with BOG API
            $token = $this->bogAuth->getAccessToken();

            if (! $token || empty($token['access_token'])) {
                throw new \Exception('Failed to get BOG access token');
            }

            // Get order details from BOG
            $orderDetails = $this->bogPayment->getOrderDetails($token['access_token'], $orderId);
            Log::info('BOG Payment - Order details handleCallback', ['order_id' => $orderId, 'details' => $orderDetails]);
            // Update payment status
            $payment->update([
                'status' => $orderDetails['status'] ?? 'failed',
                'response_data' => $orderDetails,
                'callback_data' => $request->all(),
                'verified_at' => now(),
            ]);
            // If payment was successful and user requested to save card, save it
            if (isset($orderDetails['status']) && in_array(strtolower($orderDetails['status']), ['completed', 'approved', 'succeeded']) && $payment->save_card_requested && $payment->user_id) {
                // Only save card if user is authenticated
                Log::info('BOG Payment - Attempting to save card after successful payment', [
                    'order_id' => $orderId,
                    'user_id' => $payment->user_id,
                    'save_card_requested' => $payment->save_card_requested,
                    'payment_status' => $orderDetails['status'],
                ]);

                try {
                    $saveCardResult = $this->bogPayment->saveCard($token['access_token'], $orderId);

                    if ($saveCardResult['success']) {
                        // Save card details to database
                        $cardData = $saveCardResult['data'];

                        Log::info('BOG Payment - Saving card details to database', [
                            'order_id' => $orderId,
                            'card_data_keys' => array_keys($cardData),
                            'card_token' => $cardData['card_token'] ?? null,
                            'card_mask' => $cardData['card_mask'] ?? null,
                        ]);

                        $savedCard = \App\Models\BogCard::createCard([
                            'user_id' => $payment->user_id,
                            'card_token' => $cardData['card_token'] ?? null,
                            'card_mask' => $cardData['card_mask'] ?? null,
                            'card_type' => $cardData['card_type'] ?? null,
                            'expiry_month' => $cardData['expiry_month'] ?? null,
                            'expiry_year' => $cardData['expiry_year'] ?? null,
                            'is_default' => false, // Set to false initially, user can change later
                            'metadata' => json_encode($cardData),
                            'parent_order_id' => $orderId,
                        ]);

                        Log::info('BOG Payment - Card successfully saved to database', [
                            'order_id' => $orderId,
                            'card_id' => $savedCard->id,
                            'card_mask' => $savedCard->card_mask,
                            'card_type' => $savedCard->card_type,
                        ]);
                    } else {
                        Log::error('Failed to save card after successful payment', [
                            'order_id' => $orderId,
                            'error' => $saveCardResult['message'],
                            'response' => $saveCardResult,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error saving card after successful payment', [
                        'order_id' => $orderId,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            } else {
                Log::info('Card saving skipped', [
                    'order_id' => $orderId,
                    'reason' => 'Payment not successful or save_card_requested is false or user not authenticated',
                    'status' => $orderDetails['status'] ?? 'unknown',
                    'save_card_requested' => $payment->save_card_requested ?? false,
                    'user_id' => $payment->user_id ?? null,
                    'user_authenticated' => ! empty($payment->user_id),
                ]);
            }

            Log::info('Payment status updated', [
                'order_id' => $orderId,
                'status' => $orderDetails['status'] ?? 'unknown',
            ]);

            // Mark products as ordered if payment is successful
            if (isset($orderDetails['status']) && in_array(strtolower($orderDetails['status']), ['completed', 'approved', 'succeeded'])) {
                $this->markProductsAsOrdered($payment);
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error processing BOG callback: '.$e->getMessage(), [
                'order_id' => $orderId,
                'exception' => $e,
            ]);

            $payment->update([
                'status' => 'error',
                'error_message' => $e->getMessage(),
            ]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    // Add this method to BogPaymentController.php

    /**
     * Save card for automatic payments (subscriptions)
     *
     * @param  string  $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveCardForAutomaticPayments(Request $request, $orderId)
    {
        $request->validate([
            'idempotency_key' => 'nullable|uuid',
        ]);

        $tokenResult = $this->bogAuth->getAccessToken();
        if (! $tokenResult || empty($tokenResult['access_token'])) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Unable to authenticate with BOG',
                ],
                500,
            );
        }

        $result = $this->bogPayment->saveCardForAutomaticPayments($tokenResult['access_token'], $orderId, $request->input('idempotency_key'));

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json($result, $result['status'] ?? 400);
    }

    /**
     * Reject pre-authorization
     *
     * @param  string  $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function rejectPreAuthorization(Request $request, $orderId)
    {
        $validated = $request->validate([
            'description' => 'nullable|string|max:255',
            'idempotency_key' => 'nullable|uuid',
        ]);

        $tokenResult = $this->bogAuth->getAccessToken();
        if (! $tokenResult || empty($tokenResult['access_token'])) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Unable to authenticate with BOG',
                ],
                500,
            );
        }

        $result = $this->bogPayment->rejectPreAuthorization($tokenResult['access_token'], $orderId, $validated);

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json($result, $result['status'] ?? 400);
    }

    /**
     * Confirm pre-authorization
     *
     * @param  string  $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmPreAuthorization(Request $request, $orderId)
    {
        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'idempotency_key' => 'nullable|uuid',
        ]);

        $tokenResult = $this->bogAuth->getAccessToken();
        if (! $tokenResult || empty($tokenResult['access_token'])) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Unable to authenticate with BOG',
                ],
                500,
            );
        }

        $result = $this->bogPayment->confirmPreAuthorization($tokenResult['access_token'], $orderId, $validated);

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json($result, $result['status'] ?? 400);
    }

    /**
     * Process automatic payment with saved card
     *
     * @param  string  $parentOrderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function processAutomaticPayment(Request $request, $parentOrderId)
    {
        $validated = $request->validate([
            'callback_url' => 'nullable|url',
            'external_order_id' => 'nullable|string|max:255',
            'idempotency_key' => 'nullable|uuid',
        ]);

        $tokenResult = $this->bogAuth->getAccessToken();
        if (! $tokenResult || empty($tokenResult['access_token'])) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Unable to authenticate with BOG',
                ],
                500,
            );
        }

        $result = $this->bogPayment->processAutomaticPayment($tokenResult['access_token'], $parentOrderId, $validated);

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json($result, $result['status'] ?? 400);
    }

    /**
     * Make payment with saved card
     *
     * @param  string  $parentOrderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function payWithSavedCard(Request $request, $parentOrderId)
    {
        $validated = $request->validate([
            'callback_url' => 'required|url',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'sometimes|string|in:GEL,USD,EUR',
            'basket' => 'required|array|min:1',
            'basket.*.quantity' => 'required|integer|min:1',
            'basket.*.unit_price' => 'required|numeric|min:0.01',
            'basket.*.product_id' => 'required|string',
            'external_order_id' => 'sometimes|string',
            'language' => 'sometimes|string|in:ka,en,ru',
        ]);

        $tokenResult = $this->bogAuth->getAccessToken();
        if (! $tokenResult || empty($tokenResult['access_token'])) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Unable to authenticate with BOG',
                ],
                500,
            );
        }

        // Get authenticated user
        $user = $request->user('sanctum');
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        // Call BOG API to process payment with saved card
        $result = $this->bogPayment->payWithSavedCard($tokenResult['access_token'], $parentOrderId, $validated);

        if ($result['success']) {
            // Create payment record in bog_payments table
            try {
                // Note: user_id FK points to users table, but we're using web_users
                // Store user_id anyway for tracking purposes
                $payment = \App\Models\BogPayment::create([
                    'bog_order_id' => $result['data']['order_id'] ?? $parentOrderId, // Use parent order ID if not returned
                    'external_order_id' => $validated['external_order_id'] ?? ('order_' . time()),
                    'user_id' => null, // FK constraint issue: points to users, not web_users
                    'amount' => $validated['amount'],
                    'currency' => $validated['currency'] ?? 'GEL',
                    'status' => $result['data']['status'] ?? 'created',
                    'request_payload' => [
                        'parent_order_id' => $parentOrderId,
                        'basket' => $validated['basket'],
                        'callback_url' => $validated['callback_url'],
                        'web_user_id' => $user->id, // Store web_user_id in payload
                    ],
                    'response_data' => $result['data'] ?? null,
                    'save_card_requested' => false, // Using existing saved card
                ]);

                Log::info('BOG Payment record created for saved card payment', [
                    'payment_id' => $payment->id,
                    'bog_order_id' => $payment->bog_order_id,
                    'web_user_id' => $user->id,
                    'parent_order_id' => $parentOrderId,
                ]);

                $result['payment_id'] = $payment->id;
            } catch (\Exception $e) {
                Log::error('Failed to create BOG payment record', [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                    'web_user_id' => $user->id,
                    'parent_order_id' => $parentOrderId,
                ]);
            }

            return response()->json($result);
        }

        return response()->json($result, $result['status'] ?? 400);
    }

    /**
     * Delete saved card
     *
     * @param  string  $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteSavedCard(Request $request, $orderId)
    {
        $request->validate([
            'idempotency_key' => 'nullable|uuid',
        ]);

        $tokenResult = $this->bogAuth->getAccessToken();
        if (! $tokenResult || empty($tokenResult['access_token'])) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Unable to authenticate with BOG',
                ],
                500,
            );
        }

        $result = $this->bogPayment->deleteSavedCard($tokenResult['access_token'], $orderId, $request->input('idempotency_key'));

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json($result, $result['status'] ?? 400);
    }

    public function getOrderDetails($orderId)
    {
        try {
            $tokenResult = $this->bogAuth->getAccessToken();
            if (! $tokenResult || empty($tokenResult['access_token'])) {
                throw new \Exception('Failed to authenticate with BOG');
            }

            $details = $this->bogPayment->getOrderDetails($tokenResult['access_token'], $orderId);

            if (! $details) {
                throw new \Exception($this->bogPayment->getLastError() ?? 'Failed to get order details');
            }

            return response()->json([
                'success' => true,
                'data' => $details,
            ]);
        } catch (\Exception $e) {
            Log::error('BOG Get Order Details Error', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'error_code' => 'order_details_failed',
                ],
                500,
            );
        }
    }

    /**
     * Save card details during payment process
     *
     * @param  string  $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveCard(Request $request, $orderId)
    {
        $request->validate([
            'idempotency_key' => 'nullable|uuid',
        ]);

        $tokenResult = $this->bogAuth->getAccessToken();
        if (! $tokenResult || empty($tokenResult['access_token'])) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Unable to authenticate with BOG',
                ],
                500,
            );
        }

        $result = $this->bogPayment->saveCard($tokenResult['access_token'], $orderId, $request->input('idempotency_key'));

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Card saved successfully',
                'data' => $result['data'],
            ]);
        }

        return response()->json(
            [
                'success' => false,
                'message' => 'Failed to save card',
                'error' => $result['error'] ?? 'Unknown error',
                'status' => $result['status'] ?? 500,
            ],
            $result['status'] ?? 500,
        );
    }

    /**
     * Charge a saved card for payment
     *
     * @param  string  $parentOrderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function chargeCard(Request $request, $parentOrderId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|size:3',
            'callback_url' => 'nullable|url',
            'external_order_id' => 'nullable|string|max:255',
            'save_card' => 'nullable|boolean',
            'pre_authorize' => 'nullable|boolean',
        ]);

        $tokenResult = $this->bogAuth->getAccessToken();
        if (! $tokenResult || empty($tokenResult['access_token'])) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Unable to authenticate with BOG',
                ],
                500,
            );
        }

        $paymentData = $request->only(['amount', 'currency', 'callback_url', 'external_order_id', 'save_card', 'pre_authorize']);

        $result = $this->bogPayment->chargeCard($tokenResult['access_token'], $parentOrderId, $paymentData);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Payment initiated successfully',
                'data' => $result['data'],
            ]);
        }

        return response()->json(
            [
                'success' => false,
                'message' => 'Failed to charge saved card',
                'error' => $result['error'] ?? 'Unknown error',
                'status' => $result['status'] ?? 500,
            ],
            $result['status'] ?? 500,
        );
    }

    public function checkOrderStatus($orderId)
    {
        try {
            $payment = \App\Models\BogPayment::where('bog_order_id', $orderId)->firstOrFail();

            return response()->json([
                'success' => true,
                'status' => $payment->status,
                'data' => $payment->response_data,
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking order status: '.$e->getMessage(), [
                'order_id' => $orderId,
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Order not found',
                    'status' => 404,
                ],
                404,
            );
        }
    }

    /**
     * Test BOG payment callback functionality
     * This method can be used to test if callbacks are being processed correctly
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function testCallback(Request $request)
    {
        // Test with sample data
        $testOrderId = 'test_'.time();
        $testData = [
            'order_id' => $testOrderId,
            'status' => 'completed',
            'transaction_id' => 'test_txn_'.time(),
            'amount' => 100.0,
            'currency' => 'GEL',
            'test' => true,
        ];

        try {
            // Create a test payment record
            $payment = BogPayment::create([
                'bog_order_id' => $testOrderId,
                'external_order_id' => 'test_external_'.time(),
                'amount' => 100.0,
                'currency' => 'GEL',
                'status' => 'pending',
                'request_payload' => $testData,
                'response_data' => $testData,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Test callback processed successfully',
                'test_order_id' => $testOrderId,
                'payment_id' => $payment->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Test callback failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Test callback failed: '.$e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Get all payments for authenticated user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserPayments(Request $request)
    {
        try {
            // Get authenticated user
            $user = $request->user('sanctum');

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                ], 401);
            }

            // Get pagination parameters
            $perPage = $request->get('per_page', 15);
            $page = $request->get('page', 1);

            // Get payments - since user_id has FK to users table but we use web_users,
            // we need to search in request_payload JSON
            $payments = BogPayment::query()
                ->with(['products' => function($query) {
                    $query->select('products.id', 'products.name_ka', 'products.name_en', 'products.name_ru',
                                   'products.slug', 'products.price', 'products.images');
                }])
                ->where(function($query) use ($user) {
                    // Try to match by user_id (if it was set)
                    $query->where('user_id', $user->id)
                        // Or by web_user_id stored in request_payload
                        ->orWhereRaw("JSON_EXTRACT(request_payload, '$.web_user_id') = ?", [$user->id]);
                })
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // Transform payments for frontend
            $transformedPayments = $payments->getCollection()->map(function ($payment) {
                // Transform products with pivot data
                $products = $payment->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name_ka' => $product->name_ka,
                        'name_en' => $product->name_en,
                        'name_ru' => $product->name_ru,
                        'slug' => $product->slug,
                        'price' => (float) $product->price,
                        'images' => $product->images,
                        'quantity' => $product->pivot->quantity,
                        'unit_price' => (float) $product->pivot->unit_price,
                        'total_price' => (float) $product->pivot->total_price,
                    ];
                });

                return [
                    'id' => $payment->id,
                    'bog_order_id' => $payment->bog_order_id,
                    'external_order_id' => $payment->external_order_id,
                    'amount' => (float) $payment->amount,
                    'currency' => $payment->currency,
                    'status' => $payment->status,
                    'payment_method' => $payment->save_card_requested ? 'new_card' : 'saved_card',
                    'products' => $products,
                    'basket' => $payment->request_payload['basket'] ?? [], // Keep for backward compatibility
                    'created_at' => $payment->created_at->format('Y-m-d H:i:s'),
                    'verified_at' => $payment->verified_at ? $payment->verified_at->format('Y-m-d H:i:s') : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedPayments,
                'pagination' => [
                    'current_page' => $payments->currentPage(),
                    'last_page' => $payments->lastPage(),
                    'per_page' => $payments->perPage(),
                    'total' => $payments->total(),
                    'from' => $payments->firstItem(),
                    'to' => $payments->lastItem(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Get user payments error', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payments',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Mark products as ordered after successful payment
     *
     * @param BogPayment $payment
     * @return void
     */
    protected function markProductsAsOrdered(BogPayment $payment)
    {
        try {
            // Get product IDs from the basket in request_payload
            $basket = $payment->request_payload['basket'] ?? [];

            if (empty($basket)) {
                Log::warning('No basket items found in payment', [
                    'payment_id' => $payment->id,
                    'bog_order_id' => $payment->bog_order_id,
                ]);
                return;
            }

            // Get web_user_id from request_payload
            $webUserId = $payment->request_payload['web_user_id'] ?? null;

            $productIds = [];
            $pivotData = [];

            // Prepare data for pivot table
            foreach ($basket as $item) {
                if (isset($item['product_id'])) {
                    $productId = $item['product_id'];
                    $quantity = $item['quantity'] ?? 1;
                    $unitPrice = $item['unit_price'] ?? 0;
                    $totalPrice = $quantity * $unitPrice;

                    $productIds[] = $productId;
                    $pivotData[$productId] = [
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                    ];
                }
            }

            if (empty($productIds)) {
                Log::warning('No product IDs found in basket', [
                    'payment_id' => $payment->id,
                    'basket' => $basket,
                ]);
                return;
            }

            // Attach products to payment with pivot data
            $payment->products()->attach($pivotData);

            // Update all products in the order
            $updatedCount = \App\Models\Product::whereIn('id', $productIds)
                ->update([
                    'is_ordered' => true,
                    'ordered_at' => now(),
                    'ordered_by' => $webUserId,
                ]);

            Log::info('Products marked as ordered and linked to payment', [
                'payment_id' => $payment->id,
                'bog_order_id' => $payment->bog_order_id,
                'product_ids' => $productIds,
                'updated_count' => $updatedCount,
                'ordered_by' => $webUserId,
                'pivot_entries' => count($pivotData),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to mark products as ordered', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
        }
    }
}
