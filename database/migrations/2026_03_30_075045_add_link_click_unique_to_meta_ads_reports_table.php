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
        Schema::table('meta_ads_reports', function (Blueprint $table) {
            $table->integer('link_click_unique')->default(0)->after('link_click');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meta_ads_reports', function (Blueprint $table) {
            $table->dropColumn('link_click_unique');
        });
    }
};
