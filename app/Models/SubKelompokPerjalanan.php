<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubKelompokPerjalanan extends Model
{
    protected $table = 'sub_kelompok_perjalanan';

    protected $fillable = [
        'kelompok_perjalanan_id',
        'nomor_st',
        'tanggal_st',
    ];

    public function kelompok()
    {
        return $this->belongsTo(KelompokPerjalanan::class, 'kelompok_perjalanan_id');
    }

    public function pegawai()
    {
        return $this->hasMany(PerjalananDinasPegawai::class, 'subkelompok_id');
    }

    public function nonpegawai()
    {
        return $this->hasMany(NonPegawai::class, 'subkelompok_id');
    }
}