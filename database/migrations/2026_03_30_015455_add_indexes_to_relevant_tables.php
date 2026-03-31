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
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->index('date');
        });
        Schema::table('monthly_settings', function (Blueprint $table) {
            $table->index('month');
        });
        Schema::table('budget_transfers', function (Blueprint $table) {
            $table->index('date');
        });
        Schema::table('weekly_targets', function (Blueprint $table) {
            $table->index('month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropIndex(['date']);
        });
        Schema::table('monthly_settings', function (Blueprint $table) {
            $table->dropIndex(['month']);
        });
        Schema::table('budget_transfers', function (Blueprint $table) {
            $table->dropIndex(['date']);
        });
        Schema::table('weekly_targets', function (Blueprint $table) {
            $table->dropIndex(['month']);
        });
    }
};
