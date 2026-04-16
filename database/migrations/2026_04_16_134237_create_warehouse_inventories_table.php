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
        Schema::create('warehouse_inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id')->unique();
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('sub_category')->nullable();
            $table->string('unit')->nullable();
            $table->integer('current_stock')->default(0);
            $table->integer('reserved_stock')->default(0);
            $table->integer('min_stock')->default(0);
            $table->integer('available_stock')->default(0);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('total_valuation', 15, 2)->default(0);
            $table->string('status')->nullable();
            $table->timestamp('source_last_updated')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_inventories');
    }
};
