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
        Schema::create('warehouse_sortirs', function (Blueprint $table) {
            $table->id();
            $table->string('spk_number')->unique();
            $table->integer('days_in_sortir')->default(0);
            $table->boolean('is_sla_violated')->default(false);
            $table->string('sortir_category')->nullable();
            $table->string('technician_name')->nullable();
            $table->timestamp('source_last_updated')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_sortirs');
    }
};
