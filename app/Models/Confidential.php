<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Confidential extends Model
{
    protected $fillable = [
        'text_en', 'text_ka',
    ];
}
