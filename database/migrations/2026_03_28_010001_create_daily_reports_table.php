<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->decimal('budgeting', 15, 2)->default(0);
            $table->decimal('spent', 15, 2)->default(0);
            $table->decimal('revenue', 15, 2)->default(0);
            $table->integer('chat_in')->default(0);
            $table->integer('chat_consul')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
