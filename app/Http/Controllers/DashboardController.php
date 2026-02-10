<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Penghasilan;
use App\Models\Potongan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user  = Auth::user();
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

        // Ambil pegawai dari users.pegawai_id 
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

        // RIWAYAT BULAN SEBELUMNYA (selain bulan ini)
        $riwayatGaji = Penghasilan::where('pegawai_id', $pegawai->id)
            ->where(function ($q) use ($bulan) {
                $q->whereYear('tanggal', '<', $bulan->year)
                ->orWhere(function ($q2) use ($bulan) {
                    $q2->whereYear('tanggal', $bulan->year)
                        ->whereMonth('tanggal', '<', $bulan->month);
                });
            })
            ->orderBy('tanggal', 'desc')
            ->take(6) // tampil 6 bulan terakhir
            ->get()
            ->map(function ($item) {
                $potongan = Potongan::where('pegawai_id', $item->pegawai_id)
                    ->whereMonth('tanggal', Carbon::parse($item->tanggal)->month)
                    ->whereYear('tanggal', Carbon::parse($item->tanggal)->year)
                    ->first();

                return (object) [
                    'id'                 => $item->id,
                    'periode'            => $item->tanggal,
                    'total_penghasilan'  => $item->total_penghasilan,
                    'total_potongan'     => $potongan->total_potongan ?? 0,
                    'gaji_bersih'        => max(
                        0,
                        $item->total_penghasilan - ($potongan->total_potongan ?? 0)
                    ),
                ];
            });

        return view('dashboard.main', [
            'mode'              => 'pegawai',
            'pegawai'           => $pegawai,
            'totalPenghasilan'  => $totalPenghasilan,
            'totalPotongan'     => $totalPotongan,
            'totalBersih'       => $totalBersih,
            'riwayatGaji'       => $riwayatGaji,
        ]);
    }
}
