<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The <title> shown in Google results; empty means "build one from the package name".
        Schema::table('tour_pages', function (Blueprint $table) {
            $table->string('seo_title')->nullable()->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('tour_pages', function (Blueprint $table) {
            $table->dropColumn('seo_title');
        });
    }
};
