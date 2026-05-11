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
        $jenisPejabat = JenisPejabat::all();
        $pegawai = Pegawai::all();
        $data = PejabatPeriode::with(['pegawai', 'jenisPejabat'])
            ->whereDate('periode_mulai', '<=', now())
            ->whereDate('periode_selesai', '>=', now())
            ->get();

        return view('dashboard.pejabat.index', compact('data', 'jenisPejabat', 'pegawai'));
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

        // NONAKTIFKAN yg lama
        PejabatPeriode::where('jenis_pejabat_id', $request->jenis_pejabat_id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        // CEK BENTROK PERIODE
        $exists = PejabatPeriode::where('jenis_pejabat_id', $request->jenis_pejabat_id)
            ->where(function ($q) use ($request) {
                $q->whereBetween('periode_mulai', [
                    $request->periode_mulai,
                    $request->periode_selesai
                ])
                ->orWhereBetween('periode_selesai', [
                    $request->periode_mulai,
                    $request->periode_selesai
                ]);
            })
            ->exists();

        if ($exists) {

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Periode pejabat sudah terpakai!'
                ], 422);
            }

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

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data pejabat berhasil disimpan'
            ]);
        }

        return redirect()
            ->route('pejabat.index')
            ->with('success', 'Data pejabat berhasil disimpan');

    } catch (\Exception $e) {

        DB::rollBack();

        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }

        return back()->with('error', $e->getMessage());
    }
}

    public function update(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'jenis_pejabat_id' => 'required|exists:jenis_pejabat,id',
            'pegawai_id' => 'required|exists:pegawai,id',
            'periode_mulai' => 'required|date',
            'periode_selesai' => 'required|date|after_or_equal:periode_mulai',
            'is_active' => 'boolean'
        ]);

        try {
            // Cari data pejabat
            $pejabat = PejabatPeriode::findOrFail($id);
            
            // Update data
            $pejabat->update([
                'jenis_pejabat_id' => $request->jenis_pejabat_id,
                'pegawai_id' => $request->pegawai_id,
                'periode_mulai' => $request->periode_mulai,
                'periode_selesai' => $request->periode_selesai,
                'is_active' => $request->is_active ?? 0
            ]);
            
            // Jika request dari AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pejabat berhasil diupdate',
                    'data' => $pejabat
                ]);
            }
            
            return redirect()->route('pejabat.index')->with('success', 'Pejabat berhasil diupdate');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pejabat tidak ditemukan'
                ], 404);
            }
            
            return back()->with('error', 'Data pejabat tidak ditemukan');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengupdate pejabat: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Gagal mengupdate pejabat: ' . $e->getMessage());
        }
    }

    public function destroy($id)
{
    DB::beginTransaction();

    try {

        $pejabat = PejabatPeriode::findOrFail($id);

        $pejabat->delete();

        DB::commit();

        // AJAX
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data pejabat berhasil dihapus'
            ]);
        }

        // NORMAL REQUEST
        return redirect()
            ->route('pejabat.index')
            ->with('success', 'Data pejabat berhasil dihapus');

    } catch (\Exception $e) {

        DB::rollBack();

        // AJAX
        if (request()->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }

        // NORMAL REQUEST
        return back()->with('error', $e->getMessage());
    }
}
}
