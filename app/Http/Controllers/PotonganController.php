<?php

namespace App\Http\Controllers;

use App\Models\Potongan;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PotonganController extends Controller
{
    public function index(Request $request)
    {
        $query = Potongan::with('pegawai')
            ->orderByDesc('tanggal');

        // Filter bulan
        if ($request->filled('bulan')) {
            $bulan = Carbon::parse($request->bulan);
            $query->whereMonth('tanggal', $bulan->month)
                  ->whereYear('tanggal', $bulan->year);
        }

        $potongans = $query->get();

        return view('dashboard.potongan.index', compact('potongans'));
    }

    public function create()
    {
        $pegawais = Pegawai::orderBy('nama')->get();
        return view('dashboard.potongan.create', compact('pegawais'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pegawai_id'            => 'required|exists:pegawai,id',
            'tanggal'               => 'required|date',

            'potongan_wajib'        => 'nullable|numeric|min:0',
            'potongan_pajak'        => 'nullable|numeric|min:0',
            'potongan_bpjs'         => 'nullable|numeric|min:0',
            'potongan_bpjs_lain'    => 'nullable|numeric|min:0',
            'dana_sosial'           => 'nullable|numeric|min:0',
            'bank_jateng'           => 'nullable|numeric|min:0',
            'bank_bjb'              => 'nullable|numeric|min:0',
            'parcel'                => 'nullable|numeric|min:0',
            'kop_sayuk_rukun'       => 'nullable|numeric|min:0',
            'kop_mitra_lingua'      => 'nullable|numeric|min:0',
        ]);

        // HITUNG TOTAL POTONGAN
        $validated['total_potongan'] =
            ($validated['potongan_wajib'] ?? 0)
          + ($validated['potongan_pajak'] ?? 0)
          + ($validated['potongan_bpjs'] ?? 0)
          + ($validated['potongan_bpjs_lain'] ?? 0)
          + ($validated['dana_sosial'] ?? 0)
          + ($validated['bank_jateng'] ?? 0)
          + ($validated['bank_bjb'] ?? 0)
          + ($validated['parcel'] ?? 0)
          + ($validated['kop_sayuk_rukun'] ?? 0)
          + ($validated['kop_mitra_lingua'] ?? 0);

        Potongan::create($validated);

        return redirect()
            ->route('potongan.index')
            ->with('success', 'Data potongan berhasil disimpan.');
    }

    public function edit(Potongan $potongan)
    {
        $pegawais = Pegawai::orderBy('nama')->get();
        return view('dashboard.potongan.edit', compact('potongan', 'pegawais'));
    }

    public function update(Request $request, Potongan $potongan)
    {
        $validated = $request->validate([
            'pegawai_id'            => 'required|exists:pegawai,id',
            'tanggal'               => 'required|date',

            'potongan_wajib'        => 'nullable|numeric|min:0',
            'potongan_pajak'        => 'nullable|numeric|min:0',
            'potongan_bpjs'         => 'nullable|numeric|min:0',
            'potongan_bpjs_lain'    => 'nullable|numeric|min:0',
            'dana_sosial'           => 'nullable|numeric|min:0',
            'bank_jateng'           => 'nullable|numeric|min:0',
            'bank_bjb'              => 'nullable|numeric|min:0',
            'parcel'                => 'nullable|numeric|min:0',
            'kop_sayuk_rukun'       => 'nullable|numeric|min:0',
            'kop_mitra_lingua'      => 'nullable|numeric|min:0',
        ]);

        // HITUNG ULANG TOTAL
        $validated['total_potongan'] =
            ($validated['potongan_wajib'] ?? 0)
          + ($validated['potongan_pajak'] ?? 0)
          + ($validated['potongan_bpjs'] ?? 0)
          + ($validated['potongan_bpjs_lain'] ?? 0)
          + ($validated['dana_sosial'] ?? 0)
          + ($validated['bank_jateng'] ?? 0)
          + ($validated['bank_bjb'] ?? 0)
          + ($validated['parcel'] ?? 0)
          + ($validated['kop_sayuk_rukun'] ?? 0)
          + ($validated['kop_mitra_lingua'] ?? 0);

        $potongan->update($validated);

        return redirect()
            ->route('potongan.index')
            ->with('success', 'Data potongan berhasil diperbarui.');
    }

    public function destroy(Potongan $potongan)
    {
        $potongan->delete();

        return redirect()
            ->route('potongan.index')
            ->with('success', 'Data potongan berhasil dihapus.');
    }
}
