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
        Schema::table('payment_syncs', function (Blueprint $table) {
            $table->unique(['spk_number', 'paid_at'], 'spk_payment_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_syncs', function (Blueprint $table) {
            $table->dropUnique('spk_payment_unique');
        });
    }
};
