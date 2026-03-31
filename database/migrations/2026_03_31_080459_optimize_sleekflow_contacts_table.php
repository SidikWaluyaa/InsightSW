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
            $table->index('status_chat');
            $table->index('contact_owner_name');
            $table->index('waktu_awal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sleekflow_contacts', function (Blueprint $table) {
            $table->dropIndex(['status_chat']);
            $table->dropIndex(['contact_owner_name']);
            $table->dropIndex(['waktu_awal']);
        });
    }
};
