<?php

namespace App\Http\Controllers;

use App\Models\JenisPejabat;
use App\Models\Pegawai;
use App\Models\PejabatPeriode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PejabatController extends Controller
{
    public function index()
{
    $data = PejabatPeriode::with(['pegawai', 'jenisPejabat'])
        ->whereDate('periode_mulai', '<=', now())
        ->whereDate('periode_selesai', '>=', now())
        ->get();

    return view('dashboard.pejabat.index', compact('data'));
}

    public function create()
    {
        $jenisPejabat = JenisPejabat::all();
        $pegawai = Pegawai::orderBy('nama')->get();

        return view('dashboard.pejabat.create', compact('jenisPejabat', 'pegawai'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis_pejabat_id' => 'required|exists:jenis_pejabat,id',
            'pegawai_id'       => 'required|exists:pegawai,id',
            'periode_mulai'    => 'required|date',
            'periode_selesai'  => 'required|date|after_or_equal:periode_mulai',
        ]);

        DB::beginTransaction();

        try {

            // NONAKTIFKAN yg lama (opsional tapi disarankan)
            PejabatPeriode::where('jenis_pejabat_id', $request->jenis_pejabat_id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            // CEK BENTROK PERIODE
            $exists = PejabatPeriode::where('jenis_pejabat_id', $request->jenis_pejabat_id)
                ->where(function ($q) use ($request) {
                    $q->whereBetween('periode_mulai', [$request->periode_mulai, $request->periode_selesai])
                    ->orWhereBetween('periode_selesai', [$request->periode_mulai, $request->periode_selesai]);
                })
                ->exists();

            if ($exists) {
                return back()->with('error', 'Periode pejabat sudah terpakai!');
            }

            // SIMPAN
            PejabatPeriode::create([
                'jenis_pejabat_id' => $request->jenis_pejabat_id,
                'pegawai_id'       => $request->pegawai_id,
                'periode_mulai'    => $request->periode_mulai,
                'periode_selesai'  => $request->periode_selesai,
                'is_active'        => true,
            ]);

            DB::commit();

            return redirect()
                ->route('pejabat.index')
                ->with('success', 'Data pejabat berhasil disimpan');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}
