<?php

namespace App\Http\Controllers;

use App\Imports\PegawaiImport;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class PegawaiController extends Controller
{
    public function index()
    {
        // Ambil pegawai aktif dengan pagination
        $pegawais = Pegawai::where('status', 'aktif')
            ->orderBy('nama')
            ->paginate(50);

        // Total pegawai aktif
        $totalPegawai = Pegawai::where('status', 'aktif')->count();

        return view('dashboard.pegawai.index', compact('pegawais', 'totalPegawai'));
    }

    public function create()
    {
        return view('dashboard.pegawai.create');
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'nama' => 'required|string|max:255',
            'status' => 'required|in:aktif,nonaktif',
            'nip' => 'required|string|unique:pegawai,nip',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'jabatan' => 'required|string|max:255',
            'pangkat_golongan' => 'required|string|max:255',
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:10240', // max 10MB
        ]);

        $fotoPath = null;

        // Jika ada file foto
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');

            // Buat nama file: nip_nama.ext
            $namaFile = Str::slug($request->nip . '_' . $request->nama) . '.' . $file->getClientOriginalExtension();

            // Simpan di folder storage/app/public/pegawai
            $fotoPath = $file->storeAs('pegawai', $namaFile, 'public');
        }

        // Simpan data pegawai
        Pegawai::create([
            'nama' => $request->nama,
            'status' => $request->status,
            'nip' => $request->nip,
            'jenis_kelamin' => $request->jenis_kelamin,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jabatan' => $request->jabatan,
            'pangkat_golongan' => $request->pangkat_golongan,
            'foto' => $fotoPath,
        ]);

        return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil disimpan!');
    }

    public function show(Pegawai $pegawai) {}
    public function edit(Pegawai $pegawai) {
        return view('dashboard.pegawai.edit', compact('pegawai'));
    }

    public function update(Request $request, Pegawai $pegawai)
    {

        // Validasi input
        $validated = $request->validate([
            'nama'             => 'required|string|max:255',
            'status'           => 'required|in:aktif,nonaktif',
            'nip'              => 'required|string|max:50',
            'jenis_kelamin'    => 'required|string',
            'tempat_lahir'     => 'nullable|string|max:255',
            'tanggal_lahir'    => 'nullable|date',
            'jabatan'          => 'required|string|max:255',
            'pangkat_golongan' => 'nullable|string|max:255',
            'foto'             => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
        ]);

        // Jika upload foto baru
        if ($request->hasFile('foto')) {

            // Hapus foto lama jika ada
            if ($pegawai->foto && Storage::disk('public')->exists($pegawai->foto)) {
                Storage::disk('public')->delete($pegawai->foto);
            }

            // Upload foto baru
            $file = $request->file('foto');

            $filename = uniqid() . '-' . time() . '.' . $file->getClientOriginalExtension();

            $path = $file->storeAs('pegawai', $filename, 'public');

            // Set data foto baru
            $validated['foto'] = $path;
        }

        // Update data pegawai
        $pegawai->update($validated);

        // Ambil page dari request
        $page = $request->input('page', 1);

        return redirect()->route('pegawai.index', ['page' => $page])
            ->with('success', 'Data berhasil diperbarui!');
    }

    public function destroy(Pegawai $pegawai)
    {
        // Hapus foto jika ada
        if ($pegawai->foto && Storage::disk('public')->exists($pegawai->foto)) {
            Storage::disk('public')->delete($pegawai->foto);
        }

        // Hapus data dari database
        $pegawai->delete();

        // Redirect dengan pesan sukses
        return redirect()
            ->route('pegawai.index')
            ->with('success', 'Data pegawai berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        Excel::import(new PegawaiImport, $request->file('file'));

        return redirect()
        ->route('pegawai.index')
        ->with('success', 'Data pegawai berhasil diimpor!');
    }
}
