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
        Schema::table('workshop_metrics', function (Blueprint $table) {
            $table->json('pipeline')->nullable();
            $table->json('trends')->nullable();
            $table->json('workload')->nullable();
            $table->json('service_mix')->nullable();
            $table->json('leaderboard')->nullable();
            $table->json('urgent_orders')->nullable();
            $table->json('stock_alerts')->nullable();
            $table->json('recent_activity')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workshop_metrics', function (Blueprint $table) {
            $table->dropColumn([
                'pipeline', 'trends', 'workload', 'service_mix', 
                'leaderboard', 'urgent_orders', 'stock_alerts', 'recent_activity'
            ]);
        });
    }
};
