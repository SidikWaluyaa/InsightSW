<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hasil survei / sampling manual alasan customer drop-off (PRD halaman 2), diinput Tim Data di SleekFlow Activity.
     */
    public function up(): void
    {
        Schema::create('cs_dropoff_surveys', function (Blueprint $table) {
            $table->id();
            $table->date('periode_mulai')->comment('Awal periode chat yang disampling');
            $table->date('periode_selesai')->comment('Akhir periode chat yang disampling');
            $table->string('alasan', 100)->comment('Kategori alasan drop-off');
            $table->unsignedInteger('jumlah')->comment('Jumlah chat dengan alasan ini di sampel');
            $table->string('catatan', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['periode_mulai', 'periode_selesai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cs_dropoff_surveys');
    }
};
