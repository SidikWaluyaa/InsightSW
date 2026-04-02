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
        Schema::create('finance_syncs', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('IN_PROGRESS');
            $table->string('spk_number')->unique();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('status_pembayaran', 10)->nullable(); // L, BL, BB
            $table->string('spk_status')->nullable();
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('total_bill', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('remaining_balance', 15, 2)->default(0);
            $table->text('invoice_awal_url')->nullable();
            $table->text('invoice_akhir_url')->nullable();
            $table->timestamp('estimasi_selesai')->nullable();
            $table->timestamp('source_created_at')->nullable();
            $table->timestamp('source_updated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('status_pembayaran');
            $table->index('spk_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_syncs');
    }
};
