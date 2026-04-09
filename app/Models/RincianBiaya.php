<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RincianBiaya extends Model
{
    use HasFactory;

    protected $table = 'rincian_biaya';

    protected $fillable = [
        'perjalanan_dinas_pegawai_id',
        'nonpegawai_id',
        'jenis_biaya_id',
        'uraian',
        'volume',
        'satuan',
        'tarif',
        'total',
    ];

    protected $casts = [
        'volume' => 'decimal:2',
        'tarif'  => 'decimal:2',
        'total'  => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function perjalananDinasPegawai()
    {
        return $this->belongsTo(PerjalananDinasPegawai::class);
    }

    public function nonpegawai()
    {
        return $this->belongsTo(NonPegawai::class, 'nonpegawai_id');
    }

    public function jenisBiaya()
    {
        return $this->belongsTo(JenisBiaya::class);
    }

    /*
    |--------------------------------------------------------------------------
    | AUTO HITUNG TOTAL
    |--------------------------------------------------------------------------
    */

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->total = $model->volume * $model->tarif;
        });
    }
}
