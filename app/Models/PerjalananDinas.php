<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerjalananDinas extends Model
{
    use HasFactory;

    protected $table = 'perjalanan_dinas';

    protected $fillable = [
        'tingkat_perjalanan',
        'alat_angkutan',
        'dari_kota',
        'tujuan_kota',
        'akun_biaya',
        'kode_mak',
        'tanggal_terima',
        'tanggal_mulai',
        'tanggal_akhir',
        'nama_kegiatan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_terima' => 'date',
        'tanggal_mulai'  => 'date',
        'tanggal_akhir'  => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // user pembuat
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // pivot ke pegawai
    public function pegawai()
    {
        return $this->belongsToMany(Pegawai::class, 'perjalanan_dinas_pegawai')
                    ->withTimestamps();
    }

    // surat
    public function surat()
    {
        return $this->hasOne(SuratPerjalanan::class);
    }

    // pivot table pegawai - perjalanan dinas
    public function pegawaiPerjalanan()
    {
        return $this->hasMany(PerjalananDinasPegawai::class);
    }

}
