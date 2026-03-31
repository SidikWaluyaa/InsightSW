<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_targets', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('week');
            $table->date('month');
            $table->decimal('target_revenue', 15, 2)->default(0);
            $table->integer('target_chat_consul')->default(0);
            $table->decimal('target_roas', 8, 2)->default(0);
            $table->timestamps();

            $table->unique(['week', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_targets');
    }
};
