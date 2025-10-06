<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class Product extends Model implements TranslatableContract
{
    use HasFactory,HasSEO,Translatable;

    protected $fillable = [
        'product_identify_id',
        'category_id',
        'retailer_id',
        'contact_person',
        'contact_phone',
        'size',
        'price',
        'currency',
        'rental_period',
        'rental_start_date',
        'rental_end_date',
        'active',
        'status',
        'approved_at',
        'sort_order',
        'is_favorite',
        'is_popular',
        'is_blocked',
        'is_rented',
        'rented_at',
        'rented_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'rental_start_date' => 'datetime',
        'rental_end_date' => 'datetime',
        'rented_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public $translatedAttributes = ['title', 'slug', 'description', 'brand', 'location', 'color'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function retailer()
    {
        return $this->belongsTo(WebUser::class, 'retailer_id');
    }

    public function renter()
    {
        return $this->belongsTo(User::class, 'rented_by');
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('is_blocked', false)
            ->where('is_rented', false)
            ->where('active', true);
    }

    public function scopeBlocked($query)
    {
        return $query->where('is_blocked', true);
    }

    public function scopeRented($query)
    {
        return $query->where('is_rented', true);
    }

    public function scopeNotBlocked($query)
    {
        return $query->where('is_blocked', false);
    }

    public function pages()
    {
        return $this->belongsToMany(Page::class, 'page_product')
            ->withPivot('sort')
            ->withTimestamps()
            ->orderBy('page_product.sort');
    }

    public function comments()
    {
        return $this->hasMany(ProductComment::class);
    }

    public function approvedComments()
    {
        return $this->hasMany(ProductComment::class)->approved();
    }

    public function getAverageRatingAttribute()
    {
        return $this->approvedComments()->withRating()->avg('rating');
    }

    public function getTotalCommentsAttribute()
    {
        return $this->approvedComments()->count();
    }

    public function getDynamicSEOData(): SEOData
    {
        $firstImage = $this->images()->first();

        $ogImage = $firstImage ? asset("storage/{$firstImage->image_name}") : null;

        return new SEOData($ogImage);
    }
}
