<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PejabatPeriode extends Model
{
    protected $table = 'pejabat_periode';

    protected $fillable = [
        'jenis_pejabat_id',
        'pegawai_id',
        'periode_mulai',
        'periode_selesai',
        'is_active',
    ];

    protected $casts = [
        'periode_mulai' => 'date',
        'periode_selesai' => 'date',
        'is_active' => 'boolean',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function jenisPejabat()
    {
        return $this->belongsTo(JenisPejabat::class);
    }

    public static function getByTanggal($jenisNama, $tanggal)
{
    return self::whereHas('jenisPejabat', function ($q) use ($jenisNama) {
            $q->where('nama', $jenisNama);
        })
        ->whereDate('periode_mulai', '<=', $tanggal)
        ->whereDate('periode_selesai', '>=', $tanggal)
        ->with('pegawai')
        ->first();
}
}