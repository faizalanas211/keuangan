<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rincian_biaya', function (Blueprint $table) {
            $table->id();

            $table->foreignId('perjalanan_dinas_pegawai_id')
                  ->constrained('perjalanan_dinas_pegawai')
                  ->cascadeOnDelete();

            $table->foreignId('jenis_biaya_id')
                  ->constrained('jenis_biaya')
                  ->restrictOnDelete();

            $table->text('uraian')->nullable();

            $table->decimal('volume', 10, 2)->default(1);
            $table->string('satuan')->nullable(); // hr, tiket, kali, malam

            $table->decimal('tarif', 15, 2);
            $table->decimal('total', 15, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rincian_biaya');
    }
};
