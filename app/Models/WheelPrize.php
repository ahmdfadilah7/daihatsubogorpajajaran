<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WheelPrize extends Model
{
    protected $fillable = [
        'label',
        'short',
        'color',
        'weight',
        'msg',
        'sort_order',
    ];

    protected $casts = [
        'weight' => 'integer',
        'sort_order' => 'integer',
    ];
}
