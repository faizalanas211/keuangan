<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Penghasilan;
use App\Models\Potongan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user  = auth()->user();
        $bulan = Carbon::now();

        /*
        |--------------------------------------------------------------------------
        | ========================= ADMIN DASHBOARD =========================
        |--------------------------------------------------------------------------
        */
        if ($user->role === 'admin' || $user->role === 'Admin') {

            $totalPegawai = Pegawai::count();

            $totalPenghasilan = Penghasilan::whereMonth('tanggal', $bulan->month)
                ->whereYear('tanggal', $bulan->year)
                ->sum('total_penghasilan');

            $totalPotongan = Potongan::whereMonth('tanggal', $bulan->month)
                ->whereYear('tanggal', $bulan->year)
                ->sum('total_potongan');

            $totalBersih = max(0, $totalPenghasilan - $totalPotongan);

            return view('dashboard.main', [
                'mode'              => 'admin',
                'totalPegawai'      => $totalPegawai,
                'totalPenghasilan'  => $totalPenghasilan,
                'totalPotongan'     => $totalPotongan,
                'totalBersih'       => $totalBersih,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | ========================= PEGAWAI DASHBOARD =========================
        |--------------------------------------------------------------------------
        */

        // Ambil pegawai dari users.pegawai_id (INI YANG BENAR)
        $pegawai = Pegawai::find($user->pegawai_id);

        // Jika akun belum terhubung ke pegawai
        if (!$pegawai) {
            abort(403, 'Akun pegawai belum terhubung.');
        }

        // Penghasilan bulan ini
        $penghasilan = Penghasilan::where('pegawai_id', $pegawai->id)
            ->whereMonth('tanggal', $bulan->month)
            ->whereYear('tanggal', $bulan->year)
            ->first();

        // Potongan bulan ini
        $potongan = Potongan::where('pegawai_id', $pegawai->id)
            ->whereMonth('tanggal', $bulan->month)
            ->whereYear('tanggal', $bulan->year)
            ->first();

        $totalPenghasilan = $penghasilan->total_penghasilan ?? 0;
        $totalPotongan    = $potongan->total_potongan ?? 0;
        $totalBersih      = max(0, $totalPenghasilan - $totalPotongan);

        return view('dashboard.main', [
            'mode'              => 'pegawai',
            'pegawai'           => $pegawai,
            'totalPenghasilan'  => $totalPenghasilan,
            'totalPotongan'     => $totalPotongan,
            'totalBersih'       => $totalBersih,
        ]);
    }
}
