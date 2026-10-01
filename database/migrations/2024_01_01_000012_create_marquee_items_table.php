<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marquee_items', function (Blueprint $table) {
            $table->id();
            $table->string('text', 100);
            $table->string('icon', 50);
            $table->char('color', 7);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marquee_items');
    }
};
