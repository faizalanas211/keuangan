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
        Schema::table('rincian_biaya', function (Blueprint $table) {

            $table->boolean('masuk_amplop')
                ->default(false)
                ->after('total');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rincian_biaya', function (Blueprint $table) {

            $table->dropColumn('masuk_amplop');

        });
    }
};