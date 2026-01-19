<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('penghasilans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pegawai_id')
                  ->constrained('pegawai')
                  ->cascadeOnDelete();

            $table->decimal('gaji_induk', 15, 2)->default(0);
            $table->decimal('tunj_suami_istri', 15, 2)->default(0);
            $table->decimal('tunj_anak', 15, 2)->default(0);
            $table->decimal('tunj_umum', 15, 2)->default(0);
            $table->decimal('tunj_struktural', 15, 2)->default(0);
            $table->decimal('tunj_fungsional', 15, 2)->default(0);
            $table->decimal('tunj_beras', 15, 2)->default(0);
            $table->decimal('tunj_pajak', 15, 2)->default(0);
            $table->decimal('pembulatan', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penghasilans');
    }
};
