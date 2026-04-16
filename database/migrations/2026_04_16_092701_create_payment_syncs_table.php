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
        Schema::create('payment_syncs', function (Blueprint $table) {
            $table->id();
            $table->string('spk_number');
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->string('payment_type'); // BEFORE / AFTER
            $table->decimal('total_bill_snapshot', 15, 2)->default(0);
            $table->decimal('balance_snapshot', 15, 2)->default(0);
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('source_created_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('spk_number');
            $table->index('payment_type');
            $table->index('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_syncs');
    }
};
