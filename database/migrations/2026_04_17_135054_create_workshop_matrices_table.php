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
        Schema::create('workshop_matrices', function (Blueprint $table) {
            $table->id();
            $table->string('phase'); // Persiapan, Sortir, Produksi, Post
            $table->string('sub_stage'); // Washing, Sol Repair, etc.
            $table->integer('count')->default(0);
            $table->integer('total_group_at_sync')->default(0);
            $table->boolean('is_bottleneck')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshop_matrices');
    }
};
