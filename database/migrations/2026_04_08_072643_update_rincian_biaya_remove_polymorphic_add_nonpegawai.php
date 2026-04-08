<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rincian_biaya', function (Blueprint $table) {

            // hapus polymorphic
            if (Schema::hasColumn('rincian_biaya', 'peserta_tipe')) {
                $table->dropColumn('peserta_tipe');
            }

            if (Schema::hasColumn('rincian_biaya', 'peserta_id')) {
                $table->dropColumn('peserta_id');
            }

            // tambah relasi ke nonpegawai
            $table->unsignedBigInteger('nonpegawai_id')
                  ->nullable()
                  ->after('perjalanan_dinas_pegawai_id');

            // foreign key
            $table->foreign('nonpegawai_id')
                  ->references('id')
                  ->on('nonpegawai')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('rincian_biaya', function (Blueprint $table) {

            // hapus foreign key dulu
            $table->dropForeign(['nonpegawai_id']);
            $table->dropColumn('nonpegawai_id');

            // balikin polymorphic (optional)
            $table->enum('peserta_tipe', ['pegawai', 'nonpegawai'])->nullable();
            $table->unsignedBigInteger('peserta_id')->nullable();
        });
    }
};