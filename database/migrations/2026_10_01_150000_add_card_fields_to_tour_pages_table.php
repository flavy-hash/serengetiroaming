<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // What the package card shows on /packages, /safaris and "More packages".
        Schema::table('tour_pages', function (Blueprint $table) {
            $table->string('card_tagline')->nullable()->after('duration');
            $table->string('card_highlight')->nullable()->after('card_tagline');
            $table->string('card_summary', 500)->nullable()->after('card_highlight');
        });
    }

    public function down(): void
    {
        Schema::table('tour_pages', function (Blueprint $table) {
            $table->dropColumn(['card_tagline', 'card_highlight', 'card_summary']);
        });
    }
};
