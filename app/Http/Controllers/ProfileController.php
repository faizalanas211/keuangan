<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $pegawai = auth()->user()->pegawai;

        if ($pegawai->foto && Storage::disk('public')->exists($pegawai->foto)) {
            Storage::disk('public')->delete($pegawai->foto);
        }

        $file = $request->file('foto');
        $namaFile = Str::slug($pegawai->nip . '_' . $pegawai->nama) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('pegawai', $namaFile, 'public');

        $pegawai->update([
            'foto' => $path
        ]);

        return back()->with('success','Foto berhasil diperbarui');
    }
}
