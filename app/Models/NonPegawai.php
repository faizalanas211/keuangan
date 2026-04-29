<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\RincianBiaya;

class NonPegawai extends Model
{
    protected $table = 'nonpegawai';

    protected $fillable = [
        'perjalanan_dinas_id',
        'nama',
        'nik',
        'instansi',
        'subkelompok_id',
    ];

    // ===============================
    // RELASI
    // ===============================

    public function perjalananDinas()
    {
        return $this->belongsTo(PerjalananDinas::class);
    }

    public function rincian()
    {
        return $this->hasMany(RincianBiaya::class, 'nonpegawai_id');
    }

    public function subKelompok()
    {
        return $this->belongsTo(SubKelompokPerjalanan::class, 'subkelompok_id');
    }
}