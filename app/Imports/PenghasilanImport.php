<?php

namespace App\Imports;

use App\Models\Penghasilan;
use App\Models\Pegawai;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PenghasilanImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // ===== NORMALISASI NIP DARI EXCEL =====
        $nip = trim((string)$row['nip']);
$nip = preg_replace('/[^0-9]/','',$nip);

$pegawai = Pegawai::whereRaw('REPLACE(nip," ","") = ?', [$nip])->first();

        // jika pegawai tidak ditemukan skip
        if (!$pegawai) {
            return null;
        }

        // ===== KONVERSI TANGGAL =====
        $tanggal = $row['tanggal'];

        if (is_numeric($tanggal)) {
            $tanggal = Date::excelToDateTimeObject($tanggal)->format('Y-m-d');
        }

        // ===== HITUNG TOTAL =====
        $total =
            ($row['gaji_induk'] ?? 0) +
            ($row['tunj_suami_istri'] ?? 0) +
            ($row['tunj_anak'] ?? 0) +
            ($row['tunj_umum'] ?? 0) +
            ($row['tunj_struktural'] ?? 0) +
            ($row['tunj_fungsional'] ?? 0) +
            ($row['tunj_beras'] ?? 0) +
            ($row['tunj_pajak'] ?? 0) +
            ($row['pembulatan'] ?? 0);

        return new Penghasilan([
            'pegawai_id'        => $pegawai->id,
            'tanggal'           => $tanggal,
            'gaji_induk'        => $row['gaji_induk'] ?? 0,
            'tunj_suami_istri'  => $row['tunj_suami_istri'] ?? 0,
            'tunj_anak'         => $row['tunj_anak'] ?? 0,
            'tunj_umum'         => $row['tunj_umum'] ?? 0,
            'tunj_struktural'   => $row['tunj_struktural'] ?? 0,
            'tunj_fungsional'   => $row['tunj_fungsional'] ?? 0,
            'tunj_beras'        => $row['tunj_beras'] ?? 0,
            'tunj_pajak'        => $row['tunj_pajak'] ?? 0,
            'pembulatan'        => $row['pembulatan'] ?? 0,
            'total_penghasilan' => $total,
        ]);
    }
}
