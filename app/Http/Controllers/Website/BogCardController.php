<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use Bog\Payment\Models\BogCard;
use Bog\Payment\Services\BogAuthService;
use Bog\Payment\Services\BogPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BogCardController extends Controller
{
    protected BogAuthService $bogAuth;

    protected BogPaymentService $bogPayment;

    public function __construct()
    {
        $this->bogAuth = new BogAuthService;
        $this->bogPayment = new BogPaymentService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Add a new card manually
     */
    public function addCard(Request $request)
    {
        try {
            $validated = $request->validate([
                'card_number' => ['required', 'string', 'min:13', 'max:19'],
                'card_holder_name' => ['required', 'string', 'max:255'],
                'expiry_month' => ['required', 'string', 'size:2', 'regex:/^(0[1-9]|1[0-2])$/'],
                'expiry_year' => ['required', 'string', 'size:4', 'regex:/^20[2-9][0-9]$/'],
                'cvv' => ['required', 'string', 'size:3', 'regex:/^[0-9]{3}$/'],
                'card_type' => ['nullable', 'string', 'in:visa,mastercard,amex,bog,other'],
                'is_default' => ['nullable', 'boolean'],
            ]);

            $user = $request->user('sanctum');
            $cardType = $validated['card_type'] ?? $this->detectCardType($validated['card_number']);
            $cardBrand = $this->detectCardBrand($validated['card_number']);
            $cardMask = $this->maskCardNumber($validated['card_number']);
            $cardToken = $this->generateCardToken($validated['card_number'], $user->id);

            // Check if card already exists
            if (BogCard::where('user_id', $user->id)->where('card_mask', $cardMask)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This card is already saved',
                ], 400);
            }

            // Check if card is expired
            $expiryDate = \Carbon\Carbon::createFromDate($validated['expiry_year'], $validated['expiry_month'], 1)->endOfMonth();
            if ($expiryDate->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Card has expired',
                ], 400);
            }

            // If this is set as default, remove default from other cards
            if ($validated['is_default'] ?? false) {
                BogCard::where('user_id', $user->id)->update(['is_default' => false]);
            }

            $card = BogCard::create([
                'user_id' => $user->id,
                'card_token' => $cardToken,
                'card_mask' => $cardMask,
                'card_type' => $cardType,
                'card_holder_name' => strtoupper($validated['card_holder_name']),
                'card_brand' => $cardBrand,
                'expiry_month' => $validated['expiry_month'],
                'expiry_year' => $validated['expiry_year'],
                'is_default' => $validated['is_default'] ?? ! BogCard::where('user_id', $user->id)->exists(),
                'metadata' => [
                    'added_manually' => true,
                    'added_at' => now()->toIso8601String(),
                ],
            ]);

            Log::info('Card added manually', [
                'user_id' => $user->id,
                'card_id' => $card->id,
                'card_mask' => $cardMask,
                'card_type' => $cardType,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Card added successfully',
                'card' => [
                    'id' => $card->id,
                    'card_mask' => $card->card_mask,
                    'card_type' => $card->card_type,
                    'card_brand' => $card->card_brand,
                    'card_holder_name' => $card->card_holder_name,
                    'expiry_month' => $card->expiry_month,
                    'expiry_year' => $card->expiry_year,
                    'formatted_expiry' => $card->formatted_expiry,
                    'is_default' => $card->is_default,
                    'created_at' => $card->created_at->toIso8601String(),
                ],
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error adding card', [
                'error' => $e->getMessage(),
                'user_id' => $request->user('sanctum')?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add card',
            ], 500);
        }
    }

    /**
     * Save card for future payments (from payment flow)
     */
    public function saveCard(Request $request, $orderId)
    {
        try {
            $validated = $request->validate([
                'idempotency_key' => ['nullable', 'uuid'],
            ]);

            $user = $request->user('sanctum');

            // Get authentication token
            $token = $this->bogAuth->getAccessToken();
            if (! $token || empty($token['access_token'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to authenticate with BOG',
                ], 500);
            }

            // Save card using package service
            $result = $this->bogPayment->saveCard($token['access_token'], $orderId, $validated);

            if ($result !== null && is_array($result) && ($result['success'] ?? false) && isset($result['data']) && is_array($result['data'])) {
                // Save card details to database
                $cardData = $result['data'];

                $card = BogCard::create([
                    'user_id' => $user->id,
                    'parent_order_id' => $orderId,
                    'card_token' => $cardData['card_token'] ?? null,
                    'card_mask' => $cardData['card_mask'] ?? '****',
                    'card_type' => $cardData['card_type'] ?? 'unknown',
                    'card_holder_name' => $cardData['card_holder_name'] ?? null,
                    'card_brand' => $cardData['card_brand'] ?? 'Unknown',
                    'expiry_month' => $cardData['expiry_month'] ?? null,
                    'expiry_year' => $cardData['expiry_year'] ?? null,
                    'is_default' => ! BogCard::where('user_id', $user->id)->exists(),
                    'metadata' => [
                        'saved_from_payment' => true,
                        'order_id' => $orderId,
                        'saved_at' => now()->toIso8601String(),
                    ],
                ]);

                Log::info('Card saved from payment', [
                    'user_id' => $user->id,
                    'card_id' => $card->id,
                    'order_id' => $orderId,
                    'card_mask' => $card->card_mask,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Card saved successfully',
                    'card' => [
                        'id' => $card->id,
                        'card_mask' => $card->card_mask,
                        'card_type' => $card->card_type,
                        'card_brand' => $card->card_brand,
                        'is_default' => $card->is_default,
                    ],
                ]);
            }

            return response()->json($result, is_array($result) ? ($result['status'] ?? 400) : 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error saving card from payment', [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
                'user_id' => $request->user('sanctum')?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save card',
            ], 500);
        }
    }

    /**
     * List all saved cards for user
     */
    public function listCards(Request $request)
    {
        try {
            $user = $request->user('sanctum');

            $cards = BogCard::where('user_id', $user->id)
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($card) {
                    return [
                        'id' => $card->id,
                        'card_mask' => $card->card_mask,
                        'card_type' => $card->card_type,
                        'card_brand' => $card->card_brand,
                        'card_holder_name' => $card->card_holder_name,
                        'expiry_month' => $card->expiry_month,
                        'expiry_year' => $card->expiry_year,
                        'formatted_expiry' => $card->formatted_expiry,
                        'is_default' => $card->is_default,
                        'is_expired' => $card->is_expired,
                        'created_at' => $card->created_at->toIso8601String(),
                        'metadata' => $card->metadata,
                    ];
                });

            return response()->json([
                'success' => true,
                'cards' => $cards,
                'total' => $cards->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error listing cards', [
                'error' => $e->getMessage(),
                'user_id' => $request->user('sanctum')?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cards',
            ], 500);
        }
    }

    /**
     * Delete a saved card
     */
    public function deleteCard(Request $request, $cardId)
    {
        try {
            $user = $request->user('sanctum');
            $card = BogCard::where('id', $cardId)->where('user_id', $user->id)->firstOrFail();

            // If deleting default card, set another card as default
            if ($card->is_default) {
                $newDefaultCard = BogCard::where('user_id', $user->id)
                    ->where('id', '!=', $cardId)
                    ->first();

                if ($newDefaultCard) {
                    $newDefaultCard->update(['is_default' => true]);
                }
            }

            $cardMask = $card->card_mask;
            $card->delete();

            Log::info('Card deleted', [
                'user_id' => $user->id,
                'card_id' => $cardId,
                'card_mask' => $cardMask,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Card deleted successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Card not found',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting card', [
                'error' => $e->getMessage(),
                'card_id' => $cardId,
                'user_id' => $request->user('sanctum')?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete card',
            ], 500);
        }
    }

    /**
     * Set default card
     */
    public function setDefaultCard(Request $request, $cardId)
    {
        try {
            $user = $request->user('sanctum');

            // Remove default from all cards
            BogCard::where('user_id', $user->id)->update(['is_default' => false]);

            // Set new default card
            $card = BogCard::where('id', $cardId)->where('user_id', $user->id)->firstOrFail();
            $card->update(['is_default' => true]);

            Log::info('Default card updated', [
                'user_id' => $user->id,
                'card_id' => $cardId,
                'card_mask' => $card->card_mask,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Default card updated successfully',
                'card' => [
                    'id' => $card->id,
                    'card_mask' => $card->card_mask,
                    'is_default' => true,
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Card not found',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error setting default card', [
                'error' => $e->getMessage(),
                'card_id' => $cardId,
                'user_id' => $request->user('sanctum')?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to set default card',
            ], 500);
        }
    }

    /**
     * Get card details
     */
    public function getCard(Request $request, $cardId)
    {
        try {
            $user = $request->user('sanctum');
            $card = BogCard::where('id', $cardId)->where('user_id', $user->id)->firstOrFail();

            return response()->json([
                'success' => true,
                'card' => [
                    'id' => $card->id,
                    'card_mask' => $card->card_mask,
                    'card_type' => $card->card_type,
                    'card_brand' => $card->card_brand,
                    'card_holder_name' => $card->card_holder_name,
                    'expiry_month' => $card->expiry_month,
                    'expiry_year' => $card->expiry_year,
                    'formatted_expiry' => $card->formatted_expiry,
                    'is_default' => $card->is_default,
                    'is_expired' => $card->is_expired,
                    'created_at' => $card->created_at->toIso8601String(),
                    'metadata' => $card->metadata,
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Card not found',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error retrieving card', [
                'error' => $e->getMessage(),
                'card_id' => $cardId,
                'user_id' => $request->user('sanctum')?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve card',
            ], 500);
        }
    }

    /**
     * Detect card type from card number
     */
    private function detectCardType(string $cardNumber): string
    {
        $cardNumber = preg_replace('/\s+/', '', $cardNumber);

        if (preg_match('/^4/', $cardNumber)) {
            return 'visa';
        }
        if (preg_match('/^5[1-5]/', $cardNumber) || preg_match('/^2(22[1-9]|2[3-9][0-9]|[3-6][0-9]{2}|7[0-1][0-9]|720)/', $cardNumber)) {
            return 'mastercard';
        }
        if (preg_match('/^3[47]/', $cardNumber)) {
            return 'amex';
        }
        if (preg_match('/^6/', $cardNumber)) {
            return 'bog';
        }

        return 'other';
    }

    /**
     * Detect card brand from card number
     */
    private function detectCardBrand(string $cardNumber): string
    {
        $cardNumber = preg_replace('/\s+/', '', $cardNumber);

        if (preg_match('/^4/', $cardNumber)) {
            return 'Visa';
        }
        if (preg_match('/^5[1-5]/', $cardNumber)) {
            return 'Mastercard';
        }
        if (preg_match('/^3[47]/', $cardNumber)) {
            return 'American Express';
        }
        if (preg_match('/^6/', $cardNumber)) {
            return 'BOG';
        }

        return 'Unknown';
    }

    /**
     * Mask card number for display
     */
    private function maskCardNumber(string $cardNumber): string
    {
        $cleaned = preg_replace('/\s+/', '', $cardNumber);

        return '****'.substr($cleaned, -4);
    }

    /**
     * Generate card token
     */
    private function generateCardToken(string $cardNumber, int $userId): string
    {
        return 'card_'.hash('sha256', $cardNumber.$userId.time().config('app.key'));
    }
}
