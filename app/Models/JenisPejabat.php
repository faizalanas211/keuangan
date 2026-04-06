<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisPejabat extends Model
{
    protected $table = 'jenis_pejabat';

    protected $fillable = ['nama'];

    public function pejabatPeriode()
    {
        return $this->hasMany(PejabatPeriode::class);
    }
}