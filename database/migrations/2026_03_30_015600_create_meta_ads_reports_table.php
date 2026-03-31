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
        Schema::create('meta_ads_reports', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            
            // Names
            $table->string('campaign_name');
            $table->string('adset_name');
            $table->string('ad_name');

            // IDs (For Integrity)
            $table->string('campaign_id');
            $table->string('adset_id');
            $table->string('ad_id');

            // Basic Metrics
            $table->integer('impressions')->default(0);
            $table->integer('reach')->default(0);
            $table->integer('clicks')->default(0);
            $table->decimal('spend', 12, 2)->default(0);

            // Calculation Metrics
            $table->decimal('ctr', 10, 4)->default(0);
            $table->decimal('cpc', 12, 4)->default(0);
            $table->decimal('cpm', 12, 4)->default(0);
            $table->decimal('frequency', 10, 4)->default(0);

            // Conversion Metrics
            $table->integer('results')->default(0);
            $table->decimal('cost_per_result', 12, 4)->default(0);

            // Engagement Metrics
            $table->integer('link_click')->default(0);
            $table->integer('video_view')->default(0);
            $table->integer('page_engagement')->default(0);
            $table->integer('post_engagement')->default(0);

            // Video Retention Metrics
            $table->integer('video_p25')->default(0);
            $table->integer('video_p50')->default(0);
            $table->integer('video_p75')->default(0);
            $table->integer('video_p100')->default(0);

            // ID Objects Metadata (New)
            $table->decimal('budget', 12, 2)->nullable();
            $table->string('status')->nullable();
            $table->string('stop_time')->nullable();

            // Calculation Metrics (Mirroring Meta 'All')
            $table->integer('clicks_all')->default(0);
            $table->decimal('ctr_all', 10, 4)->default(0);
            $table->decimal('cpc_all', 12, 4)->default(0);

            $table->softDeletes();
            $table->timestamps();

            // Prevent duplicates for same ad on same date
            $table->unique(['date', 'ad_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meta_ads_reports');
    }
};
