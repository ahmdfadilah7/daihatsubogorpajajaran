<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wheel_prizes', function (Blueprint $table) {
            $table->id();
            $table->string('label', 80);
            $table->string('short', 60);
            $table->char('color', 7);
            $table->integer('weight')->unsigned();
            $table->string('msg', 160);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wheel_prizes');
    }
};
