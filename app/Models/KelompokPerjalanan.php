<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KelompokPerjalanan extends Model
{
    protected $table = 'kelompok_perjalanan';

    public $timestamps = false; // karena cuma ada created_at

    protected $fillable = [
        'perjalanan_dinas_id',
        'nama_kelompok',
        'nomor_st',
        'tanggal_st',
        'created_at',
    ];

    protected $casts = [
        'tanggal_st' => 'date',
        'created_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function perjalanan()
    {
        return $this->belongsTo(PerjalananDinas::class, 'perjalanan_dinas_id');
    }

    public function pegawai()
    {
        return $this->hasMany(PerjalananDinasPegawai::class, 'kelompok_id');
    }

    public function nonpegawai()
    {
        return $this->hasMany(NonPegawai::class, 'kelompok_id');
    }
}