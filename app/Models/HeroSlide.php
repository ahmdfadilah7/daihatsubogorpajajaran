<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroSlide extends Model
{
    protected $fillable = [
        'name',
        'description',
        'tag',
        'price',
        'img',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];
}
