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
        Schema::table('sleekflow_contacts', function (Blueprint $table) {
            $table->dateTime('greeting_at')->nullable()->after('status_chat');
            $table->dateTime('konsul_at')->nullable()->after('greeting_at');
            $table->dateTime('followed_up_at')->nullable()->after('konsul_at');
            $table->dateTime('closing_at')->nullable()->after('followed_up_at');
            $table->dateTime('penerimaan_at')->nullable()->after('closing_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sleekflow_contacts', function (Blueprint $table) {
            $table->dropColumn([
                'greeting_at',
                'konsul_at',
                'followed_up_at',
                'closing_at',
                'penerimaan_at'
            ]);
        });
    }
};
