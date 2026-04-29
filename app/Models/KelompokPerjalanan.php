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
        'created_at',
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

    public function subKelompok()
    {
        return $this->hasMany(SubKelompokPerjalanan::class, 'kelompok_perjalanan_id');
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