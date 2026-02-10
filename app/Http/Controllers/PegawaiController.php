<?php

namespace App\Http\Controllers;

use App\Imports\PegawaiImport;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\QueryException;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;

class PegawaiController extends Controller
{
    public function index()
    {
        $pegawais = Pegawai::where('status', 'aktif')
            ->orderBy('nama')
            ->paginate(50);

        $totalPegawai = Pegawai::where('status', 'aktif')->count();

        return view('dashboard.pegawai.index', compact('pegawais', 'totalPegawai'));
    }

    public function create()
    {
        return view('dashboard.pegawai.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'status' => 'required|in:aktif,nonaktif',
            'nip' => 'required|string|unique:pegawai,nip|unique:users,nip',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'jabatan' => 'required|string|max:255',
            'pangkat_golongan' => 'required|string|max:255',
            'role' => 'required|in:pegawai,admin',
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:10240',
        ]);

        $fotoPath = null;

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $namaFile = Str::slug($request->nip . '_' . $request->nama) . '.' . $file->getClientOriginalExtension();
            $fotoPath = $file->storeAs('pegawai', $namaFile, 'public');
        }

        // SIMPAN PEGAWAI
        $pegawai = Pegawai::create([
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

        // BUAT AKUN LOGIN OTOMATIS
        User::create([
            'name' => $pegawai->nama,
            'email' => $pegawai->nip . '@pegawai.local',
            'nip' => $pegawai->nip,
            'pegawai_id' => $pegawai->id,
            'role' => $request->role, // <-- sesuai pilihan admin / pegawai
            'password' => Hash::make($pegawai->nip),
        ]);

        return redirect()->route('pegawai.index')
            ->with('success', 'Data pegawai dan akun login berhasil dibuat!');
    }

    public function show(Pegawai $pegawai) {}

    public function edit(Pegawai $pegawai)
    {
        return view('dashboard.pegawai.edit', compact('pegawai'));
    }

    public function update(Request $request, Pegawai $pegawai)
    {
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

        if ($request->hasFile('foto')) {

            if ($pegawai->foto && Storage::disk('public')->exists($pegawai->foto)) {
                Storage::disk('public')->delete($pegawai->foto);
            }

            $file = $request->file('foto');
            $filename = uniqid() . '-' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('pegawai', $filename, 'public');
            $validated['foto'] = $path;
        }

        $pegawai->update($validated);

        $page = $request->input('page', 1);

        return redirect()->route('pegawai.index', ['page' => $page])
            ->with('success', 'Data berhasil diperbarui!');
    }

    public function destroy(Pegawai $pegawai)
    {
        if ($pegawai->foto && Storage::disk('public')->exists($pegawai->foto)) {
            Storage::disk('public')->delete($pegawai->foto);
        }

        // Hapus user terkait
        User::where('pegawai_id', $pegawai->id)->delete();

        $pegawai->delete();

        return redirect()
            ->route('pegawai.index')
            ->with('success', 'Data pegawai berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {

            Excel::import(new PegawaiImport, $request->file('file'));

            return redirect()
                ->route('pegawai.index')
                ->with('success', 'Data pegawai berhasil diimpor!');

        } catch (ExcelValidationException $e) {

            return redirect()
                ->route('pegawai.index')
                ->with('error', 'Terdapat data pegawai yang sudah terdaftar (NIP duplikat).');

        } catch (QueryException $e) {

            return redirect()
                ->route('pegawai.index')
                ->with('error', 'Gagal impor data. Pastikan tidak ada NIP yang sama di database.');

        } catch (\Exception $e) {

            return redirect()
                ->route('pegawai.index')
                ->with('error', 'Terjadi kesalahan saat impor data.');
        }
    }
}
