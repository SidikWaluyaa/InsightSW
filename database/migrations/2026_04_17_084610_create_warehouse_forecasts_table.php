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
        Schema::create('warehouse_forecasts', function (Blueprint $table) {
            $table->id();
            $table->integer('item_id')->unique();
            $table->string('item_name');
            $table->integer('total_needed')->default(0);
            $table->integer('current_stock')->default(0);
            $table->integer('forecast_remaining')->default(0);
            $table->timestamp('source_last_updated')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_forecasts');
    }
};
