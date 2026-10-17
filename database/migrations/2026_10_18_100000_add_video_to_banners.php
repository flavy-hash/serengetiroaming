<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // An optional looping background video for a page banner / package hero.
        // The banner image is still used as the poster and for visitors who reduce motion.
        Schema::table('page_banners', function (Blueprint $table) {
            $table->string('video')->nullable()->after('image');
        });

        Schema::table('tour_pages', function (Blueprint $table) {
            $table->string('banner_video')->nullable()->after('banner_image');
        });
    }

    public function down(): void
    {
        Schema::table('page_banners', fn (Blueprint $table) => $table->dropColumn('video'));
        Schema::table('tour_pages', fn (Blueprint $table) => $table->dropColumn('banner_video'));
    }
};
