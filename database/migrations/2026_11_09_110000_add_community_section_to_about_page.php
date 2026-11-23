<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** A "Supporting Local Communities" section on the About page (heading and text). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('about_pages', function (Blueprint $table) {
            $table->string('community_heading')->nullable()->after('closing_text');
            $table->text('community_text')->nullable()->after('community_heading');
        });
    }

    public function down(): void
    {
        Schema::table('about_pages', function (Blueprint $table) {
            $table->dropColumn(['community_heading', 'community_text']);
        });
    }
};
