<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductComment extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'comment',
        'rating',
        'is_approved',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
        'rating' => 'integer',
    ];

    /**
     * Get the product that owns the comment
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user that wrote the comment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(WebUser::class, 'user_id');
    }

    /**
     * Get the admin user who approved the comment
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope to get only approved comments
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope to get comments with rating
     */
    public function scopeWithRating($query)
    {
        return $query->whereNotNull('rating');
    }

    /**
     * Scope to order by latest first
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
