<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_perjalanan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('perjalanan_dinas_id')
                  ->constrained('perjalanan_dinas')
                  ->cascadeOnDelete();

            $table->string('nomor_sk')->nullable();
            $table->string('nomor_st')->nullable();
            $table->date('tanggal_st')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_perjalanan');
    }
};
