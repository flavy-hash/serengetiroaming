<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The company's vehicles (safari 4×4s, transfer vans…), shown on /vehicles.
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('name');                       // Toyota Land Cruiser 4×4
            $table->string('category')->nullable();       // Safari 4×4, Airport transfer…
            $table->unsignedTinyInteger('seats')->nullable();
            $table->text('description')->nullable();
            $table->json('features')->nullable();         // ["Pop-up roof", "Fridge", …]
            $table->string('image')->nullable();          // public-disk path or full URL
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
