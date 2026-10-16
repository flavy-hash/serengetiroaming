<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Lodges, camps and hotels: shown on /accommodations and picked in package itineraries.
        Schema::create('accommodations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->index(); // free text, e.g. "Serengeti"
            $table->string('type')->nullable(); // e.g. Lodge, Tented camp
            $table->string('level')->nullable(); // budget, mid-range, luxury
            $table->text('description')->nullable();
            $table->string('image')->nullable(); // public-disk path or full URL
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accommodations');
    }
};
