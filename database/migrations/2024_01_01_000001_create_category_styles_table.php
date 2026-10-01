<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_styles', function (Blueprint $table) {
            $table->id();
            $table->string('category', 20)->unique();
            $table->char('bg', 7);
            $table->string('label', 40);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_styles');
    }
};
