<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // "Top activities" on the homepage (below the reviews).
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('kicker')->nullable();       // small line above the title: "06:45 EAT"
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image')->nullable();        // public-disk path or full URL
            $table->string('image_label')->nullable();  // caption on the photo: "Rufiji River · Nyerere"
            $table->string('link_url')->nullable();
            $table->string('link_label')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
