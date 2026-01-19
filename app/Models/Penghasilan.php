<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penghasilan extends Model
{
    use HasFactory;

    protected $fillable = [
        'pegawai_id',
        'tanggal',
        'gaji_induk',
        'tunj_suami_istri',
        'tunj_anak',
        'tunj_umum',
        'tunj_struktural',
        'tunj_fungsional',
        'tunj_beras',
        'tunj_pajak',
        'pembulatan',
        'total_penghasilan',
    ];


    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }
}
