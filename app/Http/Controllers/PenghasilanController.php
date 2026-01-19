<?php

namespace App\Http\Controllers;

use App\Models\Penghasilan;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PenghasilanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Penghasilan::with('pegawai')
            ->orderByDesc('tanggal');

        // FILTER BULAN
        if ($request->filled('bulan')) {
            $bulan = Carbon::parse($request->bulan);
            $query->whereMonth('tanggal', $bulan->month)
                  ->whereYear('tanggal', $bulan->year);
        }

        $penghasilans = $query->get();

        return view('dashboard.penghasilan.index', compact('penghasilans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $pegawais = Pegawai::orderBy('nama')->get();

        return view('dashboard.penghasilan.create', compact('pegawais'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pegawai_id'        => 'required|exists:pegawai,id',
            'tanggal'           => 'required|date',

            'gaji_induk'        => 'required|numeric|min:0',
            'tunj_suami_istri'  => 'nullable|numeric|min:0',
            'tunj_anak'         => 'nullable|numeric|min:0',
            'tunj_umum'         => 'nullable|numeric|min:0',
            'tunj_struktural'   => 'nullable|numeric|min:0',
            'tunj_fungsional'   => 'nullable|numeric|min:0',
            'tunj_beras'        => 'nullable|numeric|min:0',
            'tunj_pajak'        => 'nullable|numeric|min:0',
            'pembulatan'        => 'nullable|numeric',
        ]);

        // HITUNG TOTAL PENGHASILAN (OTOMATIS)
        $validated['total_penghasilan'] =
            $validated['gaji_induk']
            + ($validated['tunj_suami_istri'] ?? 0)
            + ($validated['tunj_anak'] ?? 0)
            + ($validated['tunj_umum'] ?? 0)
            + ($validated['tunj_struktural'] ?? 0)
            + ($validated['tunj_fungsional'] ?? 0)
            + ($validated['tunj_beras'] ?? 0)
            + ($validated['tunj_pajak'] ?? 0)
            + ($validated['pembulatan'] ?? 0);

        Penghasilan::create($validated);

        return redirect()
            ->route('penghasilan.index')
            ->with('success', 'Data penghasilan berhasil disimpan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Penghasilan $penghasilan)
    {
        $pegawais = Pegawai::orderBy('nama')->get();

        return view('dashboard.penghasilan.edit', compact('penghasilan', 'pegawais'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Penghasilan $penghasilan)
    {
        $validated = $request->validate([
            'pegawai_id'        => 'required|exists:pegawai,id',
            'tanggal'           => 'required|date',

            'gaji_induk'        => 'required|numeric|min:0',
            'tunj_suami_istri'  => 'nullable|numeric|min:0',
            'tunj_anak'         => 'nullable|numeric|min:0',
            'tunj_umum'         => 'nullable|numeric|min:0',
            'tunj_struktural'   => 'nullable|numeric|min:0',
            'tunj_fungsional'   => 'nullable|numeric|min:0',
            'tunj_beras'        => 'nullable|numeric|min:0',
            'tunj_pajak'        => 'nullable|numeric|min:0',
            'pembulatan'        => 'nullable|numeric',
        ]);

        // HITUNG ULANG TOTAL PENGHASILAN
        $validated['total_penghasilan'] =
            $validated['gaji_induk']
            + ($validated['tunj_suami_istri'] ?? 0)
            + ($validated['tunj_anak'] ?? 0)
            + ($validated['tunj_umum'] ?? 0)
            + ($validated['tunj_struktural'] ?? 0)
            + ($validated['tunj_fungsional'] ?? 0)
            + ($validated['tunj_beras'] ?? 0)
            + ($validated['tunj_pajak'] ?? 0)
            + ($validated['pembulatan'] ?? 0);

        $penghasilan->update($validated);

        return redirect()
            ->route('penghasilan.index')
            ->with('success', 'Data penghasilan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Penghasilan $penghasilan)
    {
        $penghasilan->delete();

        return redirect()
            ->route('penghasilan.index')
            ->with('success', 'Data penghasilan berhasil dihapus.');
    }
}
