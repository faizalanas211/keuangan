<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perjalanan_dinas', function (Blueprint $table) {
            $table->id();

            $table->string('tingkat_perjalanan')->nullable();
            $table->string('alat_angkutan')->nullable();
            $table->string('dari_kota');
            $table->string('tujuan_kota');

            $table->text('akun_biaya')->nullable();
            $table->string('kode_mak')->nullable();

            $table->date('tanggal_terima')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_akhir');

            $table->text('nama_kegiatan');

            // user yg input
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perjalanan_dinas');
    }
};
