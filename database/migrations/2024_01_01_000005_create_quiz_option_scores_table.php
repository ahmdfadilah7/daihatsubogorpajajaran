<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_option_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_option_id')->constrained('quiz_options')->cascadeOnDelete();
            $table->string('car_model', 100);
            $table->tinyInteger('points')->unsigned();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_option_scores');
    }
};
