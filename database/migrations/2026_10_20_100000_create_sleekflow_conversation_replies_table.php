<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Balasan pertama per percakapan per hari (WIB), dihitung dari API pesan Sleekflow (conversation/message):
     * balasan AI greeting (deliveryType AiAgentAction), pesan otomatis (AutomatedMessage), dan staf CS (Normal / QuickReply).
     * Analitik harian Sleekflow tidak menghitung balasan AI & otomatis, jadi angka ini diambil dari pesannya langsung.
     */
    public function up(): void
    {
        Schema::create('sleekflow_conversation_replies', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->comment('Tanggal (WIB) pesan masuk pertama customer di hari itu');
            $table->string('conversation_id', 64);
            $table->string('channel', 40)->nullable()->comment('whatsappcloudapi, instagram, ...');
            $table->dateTime('first_in_at')->comment('Pesan customer pertama di hari itu (WIB)');
            $table->dateTime('first_ai_at')->nullable()->comment('Balasan AI pertama setelah first_in_at');
            $table->dateTime('first_auto_at')->nullable()->comment('Pesan otomatis pertama setelah first_in_at');
            $table->dateTime('first_staff_at')->nullable()->comment('Balasan staf CS pertama setelah first_in_at');
            $table->unsignedInteger('ai_seconds')->nullable();
            $table->unsignedInteger('auto_seconds')->nullable();
            $table->unsignedInteger('staff_seconds')->nullable();
            $table->unsignedInteger('msg_in')->default(0)->comment('Jumlah pesan masuk di hari itu');
            $table->unsignedInteger('msg_ai')->default(0);
            $table->unsignedInteger('msg_auto')->default(0);
            $table->unsignedInteger('msg_staff')->default(0);
            $table->timestamps();

            $table->unique(['conversation_id', 'tanggal']);
            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sleekflow_conversation_replies');
    }
};
