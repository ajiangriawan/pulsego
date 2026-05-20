<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Nullable untuk walk-in
            $table->foreignId('field_id')->constrained();
            $table->foreignId('promo_id')->nullable()->constrained();
            $table->string('booking_code')->unique();
            $table->date('booking_date');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->enum('payment_type', ['dp', 'full']);
            $table->enum('status', ['pending', 'dp_paid', 'paid', 'cancelled'])->default('pending');
            $table->string('midtrans_snap_token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
