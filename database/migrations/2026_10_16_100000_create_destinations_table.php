<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Parks, mountains and islands shown on /destinations and /destinations/{slug}.
        Schema::create('destinations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('region')->nullable();         // Northern Circuit, Southern Circuit…
            $table->string('tagline')->nullable();        // one line on the card
            $table->text('description')->nullable();      // paragraphs on the detail page
            $table->json('highlights')->nullable();       // ["Great Migration", …]
            $table->string('best_time')->nullable();      // "June – October"
            $table->json('places')->nullable();           // config/places.php keys, to find packages
            $table->string('image')->nullable();          // public-disk path or full URL
            $table->string('meta_description')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destinations');
    }
};
