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
}