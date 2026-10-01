<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryStyle extends Model
{
    protected $table = 'category_styles';

    protected $fillable = [
        'category',
        'bg',
        'label',
    ];
}
