<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('refund_bank')->nullable()->after('status');
            $table->string('refund_account')->nullable()->after('refund_bank');
            $table->string('refund_name')->nullable()->after('refund_account');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['refund_bank', 'refund_account', 'refund_name']);
        });
    }
};