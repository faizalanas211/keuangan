<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Penghasilan;
use App\Models\Potongan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // ✅ FIX UTAMA
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class SlipGajiController extends Controller
{
    /**
     * ==================================================
     * INDEX (ADMIN & PEGAWAI)
     * ==================================================
     */
    public function index(Request $request)
    {
        $user     = Auth::user();          // ✅ FIX (bukan auth()->user())
        $results  = [];
        $pegawais = collect();             // ✅ SELALU ADA UNTUK VIEW

        /**
         * =========================
         * ADMIN
         * =========================
         */
        if (strtolower($user->role) === 'admin') {

            $pegawais = Pegawai::orderBy('nama')->get();

            if ($request->filled('pegawai_id') && $request->filled('bulan')) {
                $results = $this->generateSlip(
                    $request->pegawai_id,
                    $request->bulan
                );
            }
        }

        /**
         * =========================
         * PEGAWAI
         * =========================
         */
        else {

            // User HARUS terhubung ke pegawai
            if (!$user->pegawai_id) {
                abort(403, 'Akun pegawai belum terhubung.');
            }

            $pegawai = Pegawai::find($user->pegawai_id);

            if (!$pegawai) {
                abort(403, 'Data pegawai tidak ditemukan.');
            }

            // Dropdown hanya 1 (dirinya sendiri)
            $pegawais = collect([$pegawai]);

            if ($request->filled('bulan')) {
                $results = $this->generateSlip(
                    $pegawai->id,
                    $request->bulan
                );
            }
        }

        return view('dashboard.slip_gaji.index', compact(
            'pegawais',
            'results'
        ));
    }

    /**
     * ==================================================
     * CETAK PDF
     * ==================================================
     */
    public function cetak($pegawaiId, $bulan)
    {
        $user = Auth::user(); // ✅ FIX

        // 🔒 Pegawai hanya boleh cetak miliknya
        if (
            strtolower($user->role) === 'pegawai' &&
            $user->pegawai_id != $pegawaiId
        ) {
            abort(403);
        }

        $periode = Carbon::parse($bulan);

        $penghasilan = Penghasilan::with('pegawai')
            ->where('pegawai_id', $pegawaiId)
            ->whereMonth('tanggal', $periode->month)
            ->whereYear('tanggal', $periode->year)
            ->firstOrFail();

        $potongan = Potongan::where('pegawai_id', $pegawaiId)
            ->whereMonth('tanggal', $periode->month)
            ->whereYear('tanggal', $periode->year)
            ->first();

        $totalPenghasilan = $penghasilan->total_penghasilan ?? 0;
        $totalPotongan    = $potongan->total_potongan ?? 0;
        $bersih           = max(0, $totalPenghasilan - $totalPotongan);

        $pdf = Pdf::loadView('dashboard.slip_gaji.pdf', [
            'pegawai'           => $penghasilan->pegawai,
            'penghasilan'       => $penghasilan,
            'potongan'          => $potongan,
            'totalPenghasilan'  => $totalPenghasilan,
            'totalPotongan'     => $totalPotongan,
            'bersih'            => $bersih,
            'periode'           => $periode,
        ])->setPaper([0, 0, 595.28, 396.85], 'landscape'); // 21 x 14 cm

        return $pdf->stream(
            'Slip-Gaji-' .
            $penghasilan->pegawai->nama . '-' .
            $periode->format('F-Y') . '.pdf'
        );
    }

    /**
     * ==================================================
     * GENERATE SLIP (REUSABLE & AMAN)
     * ==================================================
     */
    private function generateSlip($pegawaiId, $bulan)
    {
        $periode = Carbon::parse($bulan);

        $penghasilan = Penghasilan::with('pegawai')
            ->where('pegawai_id', $pegawaiId)
            ->whereMonth('tanggal', $periode->month)
            ->whereYear('tanggal', $periode->year)
            ->first();

        if (!$penghasilan) {
            return [];
        }

        $potongan = Potongan::where('pegawai_id', $pegawaiId)
            ->whereMonth('tanggal', $periode->month)
            ->whereYear('tanggal', $periode->year)
            ->first();

        $totalPenghasilan = $penghasilan->total_penghasilan ?? 0;
        $totalPotongan    = $potongan->total_potongan ?? 0;
        $bersih           = max(0, $totalPenghasilan - $totalPotongan);

        return [[
            'pegawai' => $penghasilan->pegawai,
            'periode' => $periode->translatedFormat('F Y'),
            'bulan'   => $periode->format('Y-m'),
            'bersih'  => $bersih,
        ]];
    }
}
