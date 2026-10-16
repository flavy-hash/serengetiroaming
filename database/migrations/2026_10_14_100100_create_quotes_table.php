<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->nullable()->unique(); // QT-2026-000001, set after insert
            $table->string('source')->default('admin');         // admin | website
            $table->string('status')->default('draft');         // draft | sent | accepted | declined
            $table->foreignId('package_id')->nullable()->constrained('tour_pages')->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();

            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->string('customer_country')->nullable();

            $table->unsignedSmallInteger('adults')->default(1);
            $table->unsignedSmallInteger('children')->default(0);
            $table->date('travel_start')->nullable();
            $table->date('travel_end')->nullable();

            $table->json('items')->nullable(); // [{description, quantity, unit_price}]
            $table->decimal('discount', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->boolean('show_bank_details')->default(true);
            $table->date('valid_until')->nullable();

            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
