<?php

namespace App\Imports;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PegawaiImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $pegawai = Pegawai::create([
            'nama'             => $row['nama'],
            'status'           => $row['status'] ?? 'aktif',
            'nip'              => $row['nip'],
            'jenis_kelamin'    => $row['jenis_kelamin'],
            'jabatan'          => $row['jabatan'],
            'pangkat_golongan' => $row['pangkat_golongan'],
            'foto'             => null,
        ]);

        // buat akun login otomatis
        User::create([
            'name' => $pegawai->nama,
            'email' => $pegawai->nip.'@pegawai.local',
            'nip' => $pegawai->nip,
            'pegawai_id' => $pegawai->id,
            'role' => $row['role'] ?? 'pegawai',
            'password' => Hash::make('12345678'),
        ]);

        return $pegawai;
    }
}
