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
            ]);

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

            // Log successful order creation


            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Payment order created successfully',
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
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
     */
    public function handleCallback(Request $request)
    {
        try {
            Log::info('BOG Payment Callback received', [
                'headers' => $request->headers->all(),
                'body' => $request->all(),
            ]);

            // Validate callback data
            $validated = $request->validate([
                'order_id' => ['required', 'string'],
                'status' => ['required', 'string'],
                'amount' => ['nullable', 'numeric'],
                'currency' => ['nullable', 'string'],
                'transaction_id' => ['nullable', 'string'],
                'signature' => ['nullable', 'string'],
            ]);

            // Find the payment record
            $payment = BogPayment::where('bog_order_id', $validated['order_id'])->first();

            if (! $payment) {
                Log::warning('BOG Callback - Payment not found', [
                    'order_id' => $validated['order_id'],
                ]);

                return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
            }

            // Update payment status
            $payment->update([
                'status' => $validated['status'],
                'transaction_id' => $validated['transaction_id'] ?? null,
                'callback_data' => $validated,
                'updated_at' => now(),
            ]);

            // Handle successful payment
            if ($validated['status'] === 'completed') {
                $this->handleSuccessfulPayment($payment, $validated);
            }

            // Handle failed payment
            if (in_array($validated['status'], ['failed', 'cancelled', 'declined'])) {
                $this->handleFailedPayment($payment, $validated);
            }

            Log::info('BOG Payment Callback processed successfully', [
                'order_id' => $validated['order_id'],
                'status' => $validated['status'],
                'payment_id' => $payment->id,
            ]);

            return response()->json(['success' => true, 'message' => 'Callback processed successfully']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('BOG Callback validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);

            return response()->json(['success' => false, 'message' => 'Invalid callback data'], 400);
        } catch (\Exception $e) {
            Log::error('BOG Callback processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json(['success' => false, 'message' => 'Callback processing failed'], 500);
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
    public function saveCard(Request $request, $orderId)
    {
        $validated = $request->validate([
            'idempotency_key' => 'nullable|uuid',
        ]);
        $token = $this->bogAuth->getAccessToken();
        if (! $token || empty($token['access_token'])) {
            return response()->json(['success' => false, 'message' => 'Unable to authenticate with BOG'], 500);
        }
        $result = $this->bogPayment->saveCard($token['access_token'], $orderId, $validated);

        return response()->json($result, $result['status'] ?? ($result['success'] ? 200 : 400));
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

        return response()->json($result, $result['status'] ?? ($result['success'] ? 200 : 400));
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
    public function payWithSavedCard(Request $request, $parentOrderId)
    {
        $validated = $request->validate([
            'callback_url' => 'required|url',
            'amount' => 'required|numeric|min:0.01',
            'basket' => 'required|array',
            'language' => 'nullable|string|size:2',
        ]);
        $token = $this->bogAuth->getAccessToken();
        if (! $token || empty($token['access_token'])) {
            return response()->json(['success' => false, 'message' => 'Unable to authenticate with BOG'], 500);
        }
        $result = $this->bogPayment->payWithSavedCard($token['access_token'], $parentOrderId, $validated);

        return response()->json($result, $result['status'] ?? ($result['success'] ? 200 : 400));
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

        return response()->json($result, $result['status'] ?? ($result['success'] ? 200 : 400));
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

        return response()->json($result, $result['status'] ?? ($result['success'] ? 200 : 400));
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

        return response()->json($result, $result['status'] ?? ($result['success'] ? 200 : 400));
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
            $payload['purchase_units'] = [
                'total_amount' => $validated['amount'],
                'currency' => $validated['currency'] ?? 'GEL',
                'basket' => $validated['basket'],
            ];
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
