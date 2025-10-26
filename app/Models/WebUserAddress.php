<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebUserAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'web_user_id',
        'name',
        'city',
        'address',
        'lat',
        'lng',
        'is_default',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'is_default' => 'boolean',
    ];

    /**
     * Get the web user that owns the address
     */
    public function webUser(): BelongsTo
    {
        return $this->belongsTo(WebUser::class);
    }
}
