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
            $table->longText('pipeline')->nullable();
            $table->longText('trends')->nullable();
            $table->longText('workload')->nullable();
            $table->longText('service_mix')->nullable();
            $table->longText('leaderboard')->nullable();
            $table->longText('urgent_orders')->nullable();
            $table->longText('stock_alerts')->nullable();
            $table->longText('recent_activity')->nullable();
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
