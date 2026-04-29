<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerjalananDinasPegawai extends Model
{
    use HasFactory;

    protected $table = 'perjalanan_dinas_pegawai';

    protected $fillable = [
        'perjalanan_dinas_id',
        'pegawai_id',
        'subkelompok_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function perjalananDinas()
    {
        return $this->belongsTo(PerjalananDinas::class);
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    // nanti untuk rincian biaya
    public function rincian()
    {
        return $this->hasMany(RincianBiaya::class);
    }

    public function subKelompok()
    {
        return $this->belongsTo(SubKelompokPerjalanan::class, 'subkelompok_id');
    }
}
