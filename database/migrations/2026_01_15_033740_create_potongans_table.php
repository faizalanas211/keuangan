<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('potongans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pegawai_id')
                  ->constrained('pegawai')
                  ->cascadeOnDelete();

            // Periode
            $table->date('tanggal');

            // Potongan
            $table->decimal('potongan_wajib', 15, 2)->default(0);
            $table->decimal('potongan_pajak', 15, 2)->default(0);
            $table->decimal('potongan_bpjs', 15, 2)->default(0);
            $table->decimal('potongan_bpjs_lain', 15, 2)->default(0);
            $table->decimal('dana_sosial', 15, 2)->default(0);
            $table->decimal('bank_jateng', 15, 2)->default(0);
            $table->decimal('bank_bjb', 15, 2)->default(0);
            $table->decimal('parcel', 15, 2)->default(0);
            $table->decimal('kop_sayuk_rukun', 15, 2)->default(0);
            $table->decimal('kop_mitra_lingua', 15, 2)->default(0);

            // Total
            $table->decimal('total_potongan', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('potongans');
    }
};
