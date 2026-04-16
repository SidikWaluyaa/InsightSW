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
        Schema::create('warehouse_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id')->unique();
            $table->string('type')->index(); // IN, OUT, ADJUSTMENT
            $table->text('notes')->nullable();
            $table->longText('details')->nullable();
            $table->timestamp('transaction_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_transactions');
    }
};
