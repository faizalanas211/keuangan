<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('perjalanan_dinas_pegawai', function (Blueprint $table) {
            $table->foreignId('kelompok_id')
                ->nullable()
                ->constrained('kelompok_perjalanan')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perjalanan_dinas_pegawai', function (Blueprint $table) {
            //
        });
    }
};
