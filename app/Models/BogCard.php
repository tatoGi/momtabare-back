<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BogCard extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'card_token',
        'card_mask',
        'card_type',
        'expiry_month',
        'expiry_year',
        'is_default',
        'metadata',
        'parent_order_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_default' => 'boolean',
        'metadata' => 'array',
        'expiry_month' => 'string',
        'expiry_year' => 'string',
    ];

    /**
     * Get the user that owns the card.
     */
    public function payment()
    {
        return $this->belongsTo(BogPayment::class, 'parent_order_id', 'bog_order_id');
    }

    /**
     * Get the formatted card expiry date.
     *
     * @return string
     */
    public function getFormattedExpiryAttribute()
    {
        return $this->expiry_month.'/'.substr($this->expiry_year, -2);
    }

    /**
     * Scope a query to only include default cards.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($card) {
            // If this is the user's first card, set it as default
            if ($card->user_id) {
                $existingCardsCount = static::where('user_id', $card->user_id)->count();
                if ($existingCardsCount === 1) {
                    $card->is_default = true;
                    $card->save();
                }
            }
        });
    }

    /**
     * Create a new card with automatic default handling
     */
    public static function createCard(array $attributes = [])
    {
        $card = static::create($attributes);

        // If this is the user's first card, set it as default
        if ($card->user_id && ! $card->is_default) {
            $existingCardsCount = static::where('user_id', $card->user_id)->count();
            if ($existingCardsCount === 1) {
                $card->setAsDefault();
            }
        }

        return $card;
    }
}
