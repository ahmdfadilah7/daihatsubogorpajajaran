<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizOptionScore extends Model
{
    protected $fillable = [
        'quiz_option_id',
        'car_model',
        'points',
    ];

    protected $casts = [
        'points' => 'integer',
    ];

    public function option(): BelongsTo
    {
        return $this->belongsTo(QuizOption::class, 'quiz_option_id');
    }
}
