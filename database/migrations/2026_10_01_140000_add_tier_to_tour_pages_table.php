<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_pages', function (Blueprint $table) {
            // Safari budget level (budget, classic, mid-range, luxury); unused by other sections.
            $table->string('tier')->nullable()->after('category')->index();
        });
    }

    public function down(): void
    {
        Schema::table('tour_pages', function (Blueprint $table) {
            $table->dropIndex(['tier']);
            $table->dropColumn('tier');
        });
    }
};
