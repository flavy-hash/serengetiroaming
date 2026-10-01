<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_pages', function (Blueprint $table) {
            $table->id();
            $table->string('category')->index();
            $table->string('title');
            $table->string('slug');
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('meta_description')->nullable();

            // Banner
            $table->string('banner_image')->nullable();
            $table->string('banner_badge')->nullable();
            $table->string('banner_text')->nullable();
            $table->string('location')->nullable();

            // Package overview + price box
            $table->string('package_name');
            $table->string('duration')->nullable();
            $table->string('overview_heading')->default('Package Overview');
            $table->text('overview_body')->nullable();
            $table->unsignedInteger('price_from')->nullable();
            $table->string('price_note')->nullable();
            $table->json('facts')->nullable();

            $table->json('itinerary')->nullable();
            $table->json('included')->nullable();
            $table->json('excluded')->nullable();

            $table->string('experiences_heading')->nullable();
            $table->json('experiences')->nullable();

            $table->timestamps();

            $table->unique(['category', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_pages');
    }
};
