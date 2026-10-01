<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('model', 100);
            $table->string('type', 120);
            $table->string('category', 20);
            $table->smallInteger('year')->unsigned();
            $table->bigInteger('price')->unsigned();
            $table->string('transmission', 20);
            $table->string('fuel', 30);
            $table->tinyInteger('seats')->unsigned();
            $table->string('badge', 40)->nullable();
            $table->char('accent1', 7);
            $table->char('accent2', 7);
            $table->string('img', 500);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
