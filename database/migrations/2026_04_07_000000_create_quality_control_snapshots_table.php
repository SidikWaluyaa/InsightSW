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
        Schema::create('quality_control_snapshots', function (Blueprint $col) {
            $col->id();
            $col->date('snapshot_date')->unique(); // Ensures only one snapshot per day
            $col->integer('baseline_count')->default(0);
            $col->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_control_snapshots');
    }
};
