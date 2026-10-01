<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $fillable = [
        'model',
        'type',
        'category',
        'year',
        'price',
        'transmission',
        'fuel',
        'seats',
        'badge',
        'description',
        'features',
        'accent1',
        'accent2',
        'img',
        'sort_order',
    ];

    protected $casts = [
        'year' => 'integer',
        'price' => 'integer',
        'seats' => 'integer',
        'sort_order' => 'integer',
        'features' => 'array',
    ];

    /**
     * The two accent colors as a 2-element array [accent1, accent2].
     */
    protected function accent(): Attribute
    {
        return Attribute::make(
            get: fn () => [$this->accent1, $this->accent2],
        );
    }
}
