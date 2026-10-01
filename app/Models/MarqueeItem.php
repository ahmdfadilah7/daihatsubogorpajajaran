<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarqueeItem extends Model
{
    protected $fillable = [
        'text',
        'icon',
        'color',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];
}
