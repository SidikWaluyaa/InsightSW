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
        Schema::create('warehouse_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_id')->unique();
            $table->string('spk_number')->nullable();
            $table->string('status')->nullable();
            $table->json('material_details')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('source_last_updated')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_requests');
    }
};
