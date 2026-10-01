<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Holds a single row — the About Us page.
        Schema::create('about_pages', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_published')->default(false);
            $table->string('meta_description')->nullable();

            $table->string('title')->default('About Us');
            $table->string('banner_text')->nullable();
            $table->string('banner_image')->nullable();

            $table->string('intro_heading')->nullable();
            $table->text('intro_body')->nullable();
            $table->string('intro_image')->nullable();

            $table->json('highlights')->nullable();
            $table->json('team')->nullable();
            $table->json('reviews')->nullable();
            $table->json('faqs')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_pages');
    }
};
