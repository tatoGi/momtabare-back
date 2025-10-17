<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BogPayment extends Model
{
    protected $fillable = [
        'bog_order_id',
        'external_order_id',
        'user_id',
        'amount',
        'currency',
        'status',
        'redirect_url',
        'request_payload',
        'response_data',
        'callback_data',
        'save_card_requested',
        'verified_at',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_data' => 'array',
        'callback_data' => 'array',
        'amount' => 'decimal:2',
        'save_card_requested' => 'boolean',
    ];

    public function cards()
    {
        return $this->hasMany(BogCard::class, 'parent_order_id', 'bog_order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function webUser()
    {
        return $this->belongsTo(WebUser::class, 'user_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'bog_payment_product')
            ->withPivot('quantity', 'unit_price', 'total_price')
            ->withTimestamps();
    }
}
