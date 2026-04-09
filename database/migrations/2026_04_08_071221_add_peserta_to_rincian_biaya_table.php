<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rincian_biaya', function (Blueprint $table) {

            // tipe peserta: pegawai / nonpegawai
            $table->enum('peserta_tipe', ['pegawai', 'nonpegawai'])
                  ->after('id');

            // id peserta (relatif ke tabel sesuai tipe)
            $table->unsignedBigInteger('peserta_id')
                  ->after('peserta_tipe');

            // optional index biar query cepat
            $table->index(['peserta_tipe', 'peserta_id']);
        });
    }

    public function down(): void
    {
        Schema::table('rincian_biaya', function (Blueprint $table) {

            $table->dropIndex(['peserta_tipe', 'peserta_id']);
            $table->dropColumn(['peserta_tipe', 'peserta_id']);
        });
    }
};