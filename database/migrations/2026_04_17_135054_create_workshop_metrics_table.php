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
        Schema::create('workshop_metrics', function (Blueprint $table) {
            $table->id();
            // Snapshot Metrics
            $table->integer('in_progress')->default(0);
            $table->integer('urgent')->default(0);
            $table->integer('overdue')->default(0);
            
            // Historical Metrics (Filtered)
            $table->decimal('total_revenue', 15, 2)->default(0);
            $table->integer('throughput')->default(0);
            $table->decimal('avg_lead_time', 5, 2)->default(0);
            $table->decimal('qc_pass_rate', 5, 2)->default(0);
            
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshop_metrics');
    }
};
