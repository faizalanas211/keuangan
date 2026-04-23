<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelompok_perjalanan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('perjalanan_dinas_id')
                ->constrained('perjalanan_dinas')
                ->cascadeOnDelete();

            $table->string('nama_kelompok'); // Panitia / Peserta / Narasumber
            $table->string('nomor_st')->nullable();
            $table->date('tanggal_st')->nullable();

            $table->timestamp('created_at')->useCurrent();
            // sengaja ga pakai updated_at sesuai requirement
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelompok_perjalanan');
    }
};