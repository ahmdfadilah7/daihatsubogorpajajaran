<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CornerImage extends Model
{
    protected $fillable = [
        'src',
        'alt',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];
}
