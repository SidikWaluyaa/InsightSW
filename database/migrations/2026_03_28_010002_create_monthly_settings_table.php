<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_settings', function (Blueprint $table) {
            $table->id();
            $table->date('month')->unique();
            $table->decimal('target_revenue', 15, 2)->default(0);
            $table->decimal('total_budget', 15, 2)->default(0);
            $table->integer('total_days')->default(30);
            $table->integer('total_holidays')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_settings');
    }
};
