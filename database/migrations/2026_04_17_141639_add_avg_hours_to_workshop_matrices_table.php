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
        Schema::table('workshop_matrices', function (Blueprint $table) {
            $table->decimal('avg_hours', 8, 1)->default(0)->after('count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workshop_matrices', function (Blueprint $table) {
            $table->dropColumn('avg_hours');
        });
    }
};
