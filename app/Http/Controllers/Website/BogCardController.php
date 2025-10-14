<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\BogCard;
use App\Services\Frontend\BogAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BogCardController extends Controller
{
    protected $bogAuth;

    public function __construct(BogAuthService $bogAuth)
    {
        $this->bogAuth = $bogAuth;
        $this->middleware('auth:sanctum');
    }

    /**
     * Add a new card manually (BOG, Mastercard, Visa, etc.)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addCard(Request $request)
    {
        $request->validate([
            'card_number' => ['required', 'string', 'min:13', 'max:19'],
            'card_holder_name' => ['required', 'string', 'max:255'],
            'expiry_month' => ['required', 'string', 'size:2', 'regex:/^(0[1-9]|1[0-2])$/'],
            'expiry_year' => ['required', 'string', 'size:4', 'regex:/^20[2-9][0-9]$/'],
            'cvv' => ['required', 'string', 'size:3', 'regex:/^[0-9]{3}$/'],
            'card_type' => ['nullable', 'string', 'in:visa,mastercard,amex,bog,other'],
        ]);

        try {
            $user = $request->user('sanctum');

            // Detect card type from card number if not provided
            $cardType = $request->card_type ?? $this->detectCardType($request->card_number);

            // Detect card brand (more specific than type)
            $cardBrand = $this->detectCardBrand($request->card_number);

            // Mask the card number (show only last 4 digits)
            $cardMask = $this->maskCardNumber($request->card_number);

            // In production, you would tokenize the card with your payment gateway
            // For now, we'll create a simple token (DO NOT store real card numbers!)
            $cardToken = $this->generateCardToken($request->card_number, $user->id);

            // Check if card already exists
            $existingCard = BogCard::where('user_id', $user->id)
                ->where('card_mask', $cardMask)
                ->first();

            if ($existingCard) {
                return response()->json([
                    'success' => false,
                    'message' => 'This card is already saved',
                ], 400);
            }

            // Validate expiry date
            $expiryDate = \Carbon\Carbon::createFromDate($request->expiry_year, $request->expiry_month, 1)->endOfMonth();
            if ($expiryDate->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Card has expired',
                ], 400);
            }

            // Create the card
            $card = BogCard::create([
                'user_id' => $user->id,
                'card_token' => $cardToken,
                'card_mask' => $cardMask,
                'card_type' => $cardType,
                'card_holder_name' => strtoupper($request->card_holder_name),
                'card_brand' => $cardBrand,
                'expiry_month' => $request->expiry_month,
                'expiry_year' => $request->expiry_year,
                'is_default' => !BogCard::where('user_id', $user->id)->exists(),
                'metadata' => [
                    'added_manually' => true,
                ],
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
                    'is_default' => $card->is_default,
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error adding card: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to add card',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Detect card type from card number
     *
     * @param  string  $cardNumber
     * @return string
     */
    private function detectCardType($cardNumber)
    {
        $cardNumber = preg_replace('/\s+/', '', $cardNumber);

        // Visa: starts with 4
        if (preg_match('/^4/', $cardNumber)) {
            return 'visa';
        }

        // Mastercard: starts with 51-55 or 2221-2720
        if (preg_match('/^5[1-5]/', $cardNumber) || preg_match('/^2(22[1-9]|2[3-9][0-9]|[3-6][0-9]{2}|7[0-1][0-9]|720)/', $cardNumber)) {
            return 'mastercard';
        }

        // American Express: starts with 34 or 37
        if (preg_match('/^3[47]/', $cardNumber)) {
            return 'amex';
        }

        // BOG (Bank of Georgia) - you can define specific patterns
        if (preg_match('/^6/', $cardNumber)) {
            return 'bog';
        }

        return 'other';
    }

    /**
     * Detect specific card brand from card number (more detailed than type)
     *
     * @param  string  $cardNumber
     * @return string
     */
    private function detectCardBrand($cardNumber)
    {
        $cardNumber = preg_replace('/\s+/', '', $cardNumber);

        // Visa variations
        if (preg_match('/^4/', $cardNumber)) {
            if (preg_match('/^4026|417500|4405|4508|4844|4913|4917/', $cardNumber)) {
                return 'Visa Electron';
            }
            return 'Visa';
        }

        // Mastercard variations
        if (preg_match('/^5[1-5]/', $cardNumber) || preg_match('/^2(22[1-9]|2[3-9][0-9]|[3-6][0-9]{2}|7[0-1][0-9]|720)/', $cardNumber)) {
            if (preg_match('/^5018|5020|5038|5893|6304|6759|6761|6762|6763/', $cardNumber)) {
                return 'Maestro';
            }
            return 'Mastercard';
        }

        // American Express
        if (preg_match('/^3[47]/', $cardNumber)) {
            return 'American Express';
        }

        // Discover
        if (preg_match('/^6(?:011|5)/', $cardNumber)) {
            return 'Discover';
        }

        // JCB
        if (preg_match('/^35/', $cardNumber)) {
            return 'JCB';
        }

        // Diners Club
        if (preg_match('/^3(?:0[0-5]|[68])/', $cardNumber)) {
            return 'Diners Club';
        }

        // UnionPay
        if (preg_match('/^62/', $cardNumber)) {
            return 'UnionPay';
        }

        // BOG (Bank of Georgia) - customize as needed
        if (preg_match('/^6/', $cardNumber)) {
            return 'Bank of Georgia';
        }

        return 'Unknown';
    }

    /**
     * Mask card number showing only last 4 digits
     *
     * @param  string  $cardNumber
     * @return string
     */
    private function maskCardNumber($cardNumber)
    {
        $cardNumber = preg_replace('/\s+/', '', $cardNumber);
        $last4 = substr($cardNumber, -4);

        return '****' . $last4;
    }

    /**
     * Generate a secure card token
     * In production, use your payment gateway's tokenization
     *
     * @param  string  $cardNumber
     * @param  int  $userId
     * @return string
     */
    private function generateCardToken($cardNumber, $userId)
    {
        // DO NOT use this in production - use proper payment gateway tokenization
        return 'card_' . hash('sha256', $cardNumber . $userId . time() . config('app.key'));
    }

    /**
     * Save card details after successful payment
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveCard(Request $request)
    {
        $request->validate([
            'card_token' => 'required|string',
            'card_mask' => 'required|string',
            'card_type' => 'required|string',
            'expiry_month' => 'required|string|size:2',
            'expiry_year' => 'required|string|size:4',
        ]);

        try {
            $user = Auth::user();

            // Check if card already exists
            $existingCard = BogCard::where('user_id', $user->id)
                ->where('card_token', $request->card_token)
                ->first();

            if ($existingCard) {
                return response()->json([
                    'success' => true,
                    'message' => 'Card already saved',
                    'card' => $existingCard,
                ]);
            }

            $card = BogCard::create([
                'user_id' => $user->id,
                'card_token' => $request->card_token,
                'card_mask' => $request->card_mask,
                'card_type' => $request->card_type,
                'expiry_month' => $request->expiry_month,
                'expiry_year' => $request->expiry_year,
                'is_default' => ! BogCard::where('user_id', $user->id)->exists(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Card saved successfully',
                'card' => $card,
            ]);

        } catch (\Exception $e) {
            Log::error('Error saving card: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to save card',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List all saved cards for the authenticated user
     *
     * @return \Illuminate\Http\JsonResponse
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
                        'last_used_at' => $card->last_used_at ? $card->last_used_at->toIso8601String() : null,
                        'added_manually' => $card->metadata['added_manually'] ?? false,
                        'created_at' => $card->created_at->toIso8601String(),
                    ];
                });

            return response()->json([
                'success' => true,
                'cards' => $cards,
            ]);

        } catch (\Exception $e) {
            Log::error('Error listing cards: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve saved cards',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a saved card
     *
     * @param  string  $cardId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCard(Request $request, $cardId)
    {
        try {
            $user = $request->user('sanctum');
            $card = BogCard::where('id', $cardId)
                ->where('user_id', $user->id)
                ->firstOrFail();

            // If deleting default card, make another card default if available
            if ($card->is_default) {
                $newDefaultCard = BogCard::where('user_id', $user->id)
                    ->where('id', '!=', $cardId)
                    ->first();

                if ($newDefaultCard) {
                    $newDefaultCard->update(['is_default' => true]);
                }
            }

            $card->delete();

            return response()->json([
                'success' => true,
                'message' => 'Card deleted successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting card: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete card',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Set a card as default
     *
     * @param  string  $cardId
     * @return \Illuminate\Http\JsonResponse
     */
    public function setDefaultCard(Request $request, $cardId)
    {
        try {
            $user = $request->user('sanctum');

            // Reset all cards to non-default
            BogCard::where('user_id', $user->id)
                ->update(['is_default' => false]);

            // Set the selected card as default
            $card = BogCard::where('id', $cardId)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $card->update(['is_default' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Default card updated successfully',
                'card' => $card,
            ]);

        } catch (\Exception $e) {
            Log::error('Error setting default card: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to set default card',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
