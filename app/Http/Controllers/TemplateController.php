<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = Template::latest()->get();
        return view('dashboard.template.index', compact('templates'));
    }

    public function create()
    {
        return view('dashboard.template.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'jenis' => 'required|in:kuitansi_spd,slip_gaji',
            'file' => 'required|file|mimes:docx'
        ]);

        // nama file dari input
        $namaFile = Str::slug($request->nama); // contoh: template-kuitansi-2026

        // tambah timestamp
        $fileName = $namaFile . '-' . time() . '.docx';

        // simpan ke public disk
        $filePath = $request->file('file')->storeAs('templates', $fileName, 'public');

        Template::create([
            'nama' => $request->nama,
            'jenis' => $request->jenis,
            'file_path' => $filePath
        ]);

        return redirect()->route('template.index')
            ->with('success', 'Template berhasil diupload');
    }

    public function destroy($id)
    {
        $template = Template::findOrFail($id);

        // hapus file di storage
        if ($template->file_path && Storage::disk('public')->exists($template->file_path)) {
            Storage::disk('public')->delete($template->file_path);
        }

        // hapus data di database
        $template->delete();

        return redirect()->back()->with('success', 'Template berhasil dihapus');
    }

    public function preview($id)
    {
        $template = Template::findOrFail($id);

        $url = asset('storage/' . $template->file_path);

        return view('dashboard.template.preview', compact('url'));
    }

    public function edit($id)
    {
        $template = Template::findOrFail($id);

        return view('dashboard.template.edit', compact('template'));
    }

    public function update(Request $request, $id)
    {
        $template = Template::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'jenis' => 'required|in:kuitansi_spd,slip_gaji',
            'file' => 'nullable|file|mimes:docx'
        ]);

        // update basic data
        $template->nama = $request->nama;
        $template->jenis = $request->jenis;

        // kalau upload file baru
        if ($request->hasFile('file')) {

            // hapus file lama
            if ($template->file_path && Storage::disk('public')->exists($template->file_path)) {
                Storage::disk('public')->delete($template->file_path);
            }

            // simpan file baru
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();

            $path = $file->storeAs('templates', $filename, 'public');

            $template->file_path = $path;
        }

        $template->save();

        return redirect()->route('template.index')
            ->with('success', 'Template berhasil diupdate');
    }
}

