<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guest reviews. Submitted from /reviews as "pending" and shown once approved in the admin.
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_page_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable(); // private, never shown on the site
            $table->string('country')->nullable();
            $table->string('trip_date')->nullable(); // free text, e.g. "August 2026"
            $table->unsignedTinyInteger('rating');
            $table->string('title');
            $table->text('body');
            $table->json('photos')->nullable();
            $table->string('status')->default('pending')->index();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
