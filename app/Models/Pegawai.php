<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $table = 'pegawai';

    protected $fillable = [
        'nama',
        'status',
        'nip',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'jabatan',
        'pangkat_golongan',
        'foto',
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function kehadiran()
    {
        return $this->hasMany(Kehadiran::class, 'pegawai_id');
    }

    public function perjalananDinas()
    {
        return $this->belongsToMany(PerjalananDinas::class, 'perjalanan_dinas_pegawai')
                    ->withTimestamps();
    }

}
