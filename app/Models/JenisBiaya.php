<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisBiaya extends Model
{
    use HasFactory;

    protected $table = 'jenis_biaya';

    protected $fillable = [
        'nama_biaya',
        'is_at_cost',
    ];

    protected $casts = [
        'is_at_cost' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function rincianBiaya()
    {
        return $this->hasMany(RincianBiaya::class);
    }
}
