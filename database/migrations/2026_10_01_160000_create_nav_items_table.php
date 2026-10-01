<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Top-level items of the site navbar. An item with dropdown links opens a
        // mega-menu panel; one without (e.g. Contact) is a plain link.
        Schema::create('nav_items', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('href');
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            // Mega-menu panel
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('image')->nullable();
            $table->json('links')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nav_items');
    }
};
