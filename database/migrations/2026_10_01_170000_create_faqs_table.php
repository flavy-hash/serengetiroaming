<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Questions asked by visitors (or added by an admin), answered in the admin
        // and shown on /faq once published.
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_page_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->text('question');
            $table->text('answer')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
