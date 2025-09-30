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
        $this->middleware('auth:api');
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
    public function listCards()
    {
        try {
            $user = Auth::user();
            $cards = BogCard::where('user_id', $user->id)
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

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
    public function deleteCard($cardId)
    {
        try {
            $user = Auth::user();
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
    public function setDefaultCard($cardId)
    {
        try {
            $user = Auth::user();

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
