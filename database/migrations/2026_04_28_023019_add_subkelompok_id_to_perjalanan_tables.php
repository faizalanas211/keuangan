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
            $table->foreignId('subkelompok_id')->nullable()->after('kelompok_id');
        });

        Schema::table('nonpegawai', function (Blueprint $table) {
            $table->foreignId('subkelompok_id')->nullable()->after('kelompok_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perjalanan_tables', function (Blueprint $table) {
            //
        });
    }
};
