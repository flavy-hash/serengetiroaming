<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Anonymous page views: no IP addresses, no cookies (see LogPageView).
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->char('visitor_hash', 16);
            $table->string('path', 500);
            $table->string('page_type', 50)->nullable();
            $table->foreignId('package_id')->nullable()->constrained('tour_pages')->nullOnDelete();
            $table->string('device', 10)->nullable(); // mobile | tablet | desktop
            $table->string('referrer_host')->nullable();
            $table->char('country', 2)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
            $table->index(['package_id', 'created_at']);
            $table->index(['country', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_views');
    }
};
