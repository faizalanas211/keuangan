<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratPerjalanan extends Model
{
    use HasFactory;

    protected $table = 'surat_perjalanan';

    protected $fillable = [
        'perjalanan_dinas_id',
        'nomor_sk',
        'nomor_st',
        'tanggal_st',
    ];

    protected $casts = [
        'tanggal_st' => 'date',
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
}
