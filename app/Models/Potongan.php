<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Potongan extends Model
{
    protected $fillable = [
        'pegawai_id',
        'tanggal',

        'potongan_wajib',
        'potongan_pajak',
        'potongan_bpjs',
        'potongan_bpjs_lain',
        'dana_sosial',
        'bank_jateng',
        'bank_bjb',
        'parcel',
        'kop_sayuk_rukun',
        'kop_mitra_lingua',

        'total_potongan',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }
}
