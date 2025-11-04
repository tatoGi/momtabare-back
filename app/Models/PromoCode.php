<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'discount_percentage',
        'description',
        'usage_limit',
        'usage_count',
        'per_user_limit',
        'is_active',
        'valid_from',
        'valid_until',
        'applicable_products',
        'applicable_categories',
        'minimum_order_amount',
        'applies_to_discounted_products',
    ];

    protected $casts = [
        'applicable_products' => 'array',
        'applicable_categories' => 'array',
        'is_active' => 'boolean',
        'applies_to_discounted_products' => 'boolean',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'discount_percentage' => 'decimal:2',
        'minimum_order_amount' => 'decimal:2',
    ];

    /**
     * Check if promo code is valid for use
     */
    public function isValid(): bool
    {
        // Must be active
        if (!$this->is_active) {
            return false;
        }

        // Check usage limit
        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        // Check date validity
        $now = now();
        if ($this->valid_from && $now < $this->valid_from) {
            return false;
        }

        if ($this->valid_until && $now > $this->valid_until) {
            return false;
        }

        return true;
    }

    /**
     * Check if promo code applies to a specific product
     */
    public function appliesToProduct(int $productId): bool
    {
        // If no specific products are set, applies to all
        if (empty($this->applicable_products)) {
            return true;
        }

        return in_array($productId, $this->applicable_products);
    }

    /**
     * Check if promo code applies to a specific category
     */
    public function appliesToCategory(int $categoryId): bool
    {
        // If no specific categories are set, applies to all
        if (empty($this->applicable_categories)) {
            return true;
        }

        return in_array($categoryId, $this->applicable_categories);
    }

    /**
     * Check if minimum order amount is met
     */
    public function meetsMinimumOrderAmount(float $orderAmount): bool
    {
        if (!$this->minimum_order_amount) {
            return true;
        }

        return $orderAmount >= $this->minimum_order_amount;
    }

    /**
     * Calculate discount amount
     */
    public function calculateDiscount(float $amount): float
    {
        return ($amount * $this->discount_percentage) / 100;
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Get the products that this promo code applies to
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'promo_code_product');
    }

    /**
     * Get the categories that this promo code applies to
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'promo_code_category');
    }

    /**
     * Get the users that this promo code is assigned to
     */
    public function users()
    {
        return $this->belongsToMany(WebUser::class, 'promo_code_web_user', 'promo_code_id', 'web_user_id')->withTimestamps();
    }
}
