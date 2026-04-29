<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sub_kelompok_perjalanan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('kelompok_perjalanan_id')
                ->constrained('kelompok_perjalanan')
                ->cascadeOnDelete();

            $table->string('nomor_st')->nullable();
            $table->date('tanggal_st')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_kelompok_perjalanan');
    }
};