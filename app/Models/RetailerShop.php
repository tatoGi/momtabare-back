<?php

namespace App\Models;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetailerShop extends Model
{
    use Translatable;

    public $translatedAttributes = ['name', 'description'];

    protected $fillable = [
        'user_id',
        'avatar',
        'cover_image',
        'location',
        'contact_person',
        'contact_phone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the shop.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(WebUser::class);
    }

    /**
     * Get the products for the shop.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'retailer_id');
    }

    /**
     * Get the URL for the avatar image.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? asset('storage/'.$this->avatar) : null;
    }

    /**
     * Get the URL for the cover image.
     */
    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover_image ? asset('storage/'.$this->cover_image) : null;
    }
}

// Translation Model
class RetailerShopTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'description'];
}
