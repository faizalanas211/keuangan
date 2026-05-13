<?php

namespace App\Http\Controllers;

use App\Exports\AmplopExport;
use App\Exports\NominatifPerjalananExport;
use App\Exports\SbyPenyimpanExport;
use App\Models\JenisBiaya;
use App\Models\KelompokPerjalanan;
use App\Models\NonPegawai;
use App\Models\Pegawai;
use App\Models\PejabatPeriode;
use App\Models\PerjalananDinas;
use App\Models\PerjalananDinasPegawai;
use App\Models\RincianBiaya;
use App\Models\SubKelompokPerjalanan;
// use App\Models\NonPegawai;
use App\Models\SuratPerjalanan;
use App\Models\Template;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Settings;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;

class PerjadinController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (strtolower($user->role) === 'admin') {

            $perjalanans = PerjalananDinas::with('pegawai')
                ->latest()
                ->paginate(10);

        } else {

            $perjalanans = PerjalananDinas::with('pegawai')
                ->where('created_by', $user->id)
                ->latest()
                ->paginate(10);
        }

        return view(
            'dashboard.perjadin.index',
            compact('perjalanans')
        );
    }

    public function create()
    {
        $pegawai = Pegawai::where('status','aktif')
                    ->orderBy('nama')
                    ->get();
        $jenisBiaya = JenisBiaya::orderBy('nama_biaya')->get();

        return view('dashboard.perjadin.create', compact('pegawai','jenisBiaya'));
    }

public function store(Request $request)
{
    // dd($request->all());
    
    $request->validate([
        'alat_angkutan' => 'required',
        'dari_kota' => 'required',
        'tujuan_kota' => 'required',
        'tanggal_mulai' => 'required|date',
        'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        'tanggal_terima' => 'required|date',
        'peserta' => 'required|array|min:1',
        'peserta.*.*' => 'array|min:1',
        'kelompok' => 'nullable|array',
        'kelompok.*.*.nomor_st' => 'nullable|string',
        'kelompok.*.*.tanggal_st' => 'nullable|date',
    ]);

    DB::beginTransaction();

    try {

        // ===============================
        // PERJALANAN DINAS
        // ===============================
        $perjalanan = PerjalananDinas::create([
            'tingkat_perjalanan' => $request->tingkat_perjalanan,
            'alat_angkutan'      => $request->alat_angkutan,
            'dari_kota'          => $request->dari_kota,
            'tujuan_kota'        => $request->tujuan_kota,
            'tanggal_terima'     => $request->tanggal_terima,
            'tanggal_mulai'      => $request->tanggal_mulai,
            'tanggal_akhir'      => $request->tanggal_akhir,
            'kode_mak'           => $request->kode_mak,
            'akun_biaya'         => $request->akun_biaya,
            'nama_kegiatan'      => $request->nama_kegiatan,
            'created_by'         => Auth::id(),
        ]);

        // ===============================
        // LOOP KELOMPOK
        // ===============================
        foreach ($request->peserta as $tipe => $listST) {

            // 1. Kelompok (panitia/peserta/narasumber)
            $kelompokModel = KelompokPerjalanan::firstOrCreate([
                'perjalanan_dinas_id' => $perjalanan->id,
                'nama_kelompok' => ucfirst($tipe),
            ]);

            foreach ($listST as $subIndex => $listPeserta) {

                if (empty($listPeserta)) continue;

                // 2. SubKelompok (ST)
                $subKelompok = SubKelompokPerjalanan::create([
                    'kelompok_perjalanan_id' => $kelompokModel->id,
                    'nomor_st' => $request->kelompok[$tipe][$subIndex]['nomor_st'] ?? null,
                    'tanggal_st' => $request->kelompok[$tipe][$subIndex]['tanggal_st'] ?? null,
                ]);

                foreach ($listPeserta as $pesertaId => $peserta) {

                    $isPegawai = !empty($peserta['pegawai_id']);

                    if ($isPegawai) {

                        $pp = PerjalananDinasPegawai::create([
                            'perjalanan_dinas_id' => $perjalanan->id,
                            'pegawai_id' => $peserta['pegawai_id'],
                            'subkelompok_id' => $subKelompok->id,
                        ]);

                        $rincianList = $request->rincian[$tipe][$subIndex][$pesertaId] ?? [];

                        foreach ($rincianList as $r) {
                            if (empty($r['jenis_biaya_id'])) continue;

                            RincianBiaya::create([
                                'perjalanan_dinas_pegawai_id' => $pp->id,
                                'jenis_biaya_id' => $r['jenis_biaya_id'],
                                'uraian' => $r['uraian'] ?? null,
                                'volume' => $r['volume'] ?? 0,
                                'satuan' => $r['satuan'] ?? '-',
                                'tarif'  => $r['tarif'] ?? 0,
                                'total'  => ($r['volume'] ?? 0) * ($r['tarif'] ?? 0),
                                'masuk_amplop' => isset($r['masuk_amplop']) ? 1 : 0, // TAMBAHKAN INI
                            ]);
                        }

                    } else {

                        if (empty($peserta['nama'])) continue;

                        $np = NonPegawai::create([
                            'perjalanan_dinas_id' => $perjalanan->id,
                            'nama' => $peserta['nama'],
                            'nik'  => $peserta['nik'] ?? null,
                            'instansi' => $peserta['instansi'] ?? null,
                            'subkelompok_id' => $subKelompok->id,
                        ]);

                        $rincianList = $request->rincian[$tipe][$subIndex][$pesertaId] ?? [];

                        foreach ($rincianList as $r) {
                            if (empty($r['jenis_biaya_id'])) continue;

                            RincianBiaya::create([
                                'nonpegawai_id' => $np->id,
                                'jenis_biaya_id' => $r['jenis_biaya_id'],
                                'uraian' => $r['uraian'] ?? null,
                                'volume' => $r['volume'] ?? 0,
                                'satuan' => $r['satuan'] ?? '-',
                                'tarif'  => $r['tarif'] ?? 0,
                                'total'  => ($r['volume'] ?? 0) * ($r['tarif'] ?? 0),
                                'masuk_amplop' => isset($r['masuk_amplop']) ? 1 : 0, // TAMBAHKAN INI
                            ]);
                        }
                    }
                }
            }
        }

        DB::commit();

        return redirect()
            ->route('perjadin.index')
            ->with('success', 'Perjalanan dinas berhasil disimpan');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', $e->getMessage());
    }
}

public function show($id)
{
    $perjalanan = PerjalananDinas::with([
        'kelompokPerjalanan.subKelompok.pegawai.pegawai',
        'kelompokPerjalanan.subKelompok.pegawai.rincian.jenisBiaya',
        'kelompokPerjalanan.subKelompok.nonpegawai.rincian.jenisBiaya',
    ])->findOrFail($id);

    $grandTotalPerjalanan = 0;

    foreach ($perjalanan->kelompokPerjalanan as $kelompok) {

        foreach ($kelompok->subKelompok as $sub) {

            foreach ($sub->pegawai as $pp) {
                $grandTotalPerjalanan += $pp->rincian->sum('total');
            }

            foreach ($sub->nonpegawai as $np) {
                $grandTotalPerjalanan += $np->rincian->sum('total');
            }
        }
    }

    return view('dashboard.perjadin.show', compact(
        'perjalanan',
        'grandTotalPerjalanan'
    ));
}

public function edit($id)
{
    $perjalanan = PerjalananDinas::with([
        'surat',

        'kelompokPerjalanan.subKelompok.pegawai.pegawai',
        'kelompokPerjalanan.subKelompok.pegawai.rincian.jenisBiaya',
        'kelompokPerjalanan.subKelompok.nonpegawai.rincian.jenisBiaya'

    ])->findOrFail($id);

    $pegawai = Pegawai::all();
    $jenisBiaya = JenisBiaya::all();

    // ==================== PERBAIKAN: FORMAT DATA SEBAGAI OBJECT DENGAN KEY ====================
    $kelompokData = [];

    foreach ($perjalanan->kelompokPerjalanan as $k) {

        $jenisKelompok = $k->jenis ?? strtolower($k->nama_kelompok);

        $kelompokData[$jenisKelompok] = [];

        foreach ($k->subKelompok as $st) {

            $stData = [
                'id' => $st->id,
                'nomor_st' => $st->nomor_st,
                'tanggal_st' => $st->tanggal_st,
                'items' => []
            ];

            // ================= PEGAWAI =================
            foreach ($st->pegawai as $pp) {

                $stData['items'][] = [
                    'type' => 'pegawai',
                    'id' => $pp->id,
                    'pegawai_id' => $pp->pegawai_id,

                    'rincian' => $pp->rincian->map(function ($r) {
                        return [
                            'id' => $r->id,
                            'jenis' => $r->jenis_biaya_id,
                            'uraian' => $r->uraian,
                            'volume' => $r->volume,
                            'tarif' => $r->tarif,
                            'masuk_amplop' => $r->masuk_amplop, 
                        ];
                    })->values()
                ];
            }

            // ================= NON PEGAWAI =================
            foreach ($st->nonpegawai as $np) {

                $stData['items'][] = [
                    'type' => 'nonpegawai',
                    'id' => $np->id,
                    'nama' => $np->nama,
                    'nik' => $np->nik,
                    'instansi' => $np->instansi,

                    'rincian' => $np->rincian->map(function ($r) {
                        return [
                            'id' => $r->id,
                            'jenis' => $r->jenis_biaya_id,
                            'uraian' => $r->uraian,
                            'volume' => $r->volume,
                            'tarif' => $r->tarif,
                            'masuk_amplop' => $r->masuk_amplop, 
                        ];
                    })->values()
                ];
            }

            $kelompokData[$jenisKelompok][] = $stData;
        }
    }

    // Pastikan semua kelompok ada (untuk kelompok yang tidak punya data)
    foreach (['panitia', 'peserta', 'narasumber'] as $jenis) {
        if (!isset($kelompokData[$jenis])) {
            $kelompokData[$jenis] = [];
        }
    }

    // dd($kelompokData);

    $initialData = [
        'step1' => [
            'tingkat_perjalanan' => $perjalanan->tingkat_perjalanan,
            'alat_angkutan' => $perjalanan->alat_angkutan,
            'dari_kota' => $perjalanan->dari_kota,
            'tujuan_kota' => $perjalanan->tujuan_kota,
            'tanggal_mulai' => $perjalanan->tanggal_mulai
                ? Carbon::parse($perjalanan->tanggal_mulai)->format('Y-m-d')
                : null,

            'tanggal_akhir' => $perjalanan->tanggal_akhir
                ? Carbon::parse($perjalanan->tanggal_akhir)->format('Y-m-d')
                : null,

            'tanggal_terima' => $perjalanan->tanggal_terima
                ? Carbon::parse($perjalanan->tanggal_terima)->format('Y-m-d')
                : null,

            'kode_mak' => $perjalanan->kode_mak,
            'akun_biaya' => $perjalanan->akun_biaya,
            'nama_kegiatan' => $perjalanan->nama_kegiatan,
        ],

        'panitia' => $kelompokData['panitia'] ?? [],
        'peserta' => $kelompokData['peserta'] ?? [],
        'narasumber' => $kelompokData['narasumber'] ?? [],
    ];

    // dd($initialData);

    return view('dashboard.perjadin.edit', compact(
        'perjalanan',
        'pegawai',
        'jenisBiaya',
        'kelompokData',
        'initialData'
    ));
}

    public function update(Request $request, $id)
{
    // dd($request->all());
    $request->validate([
        'alat_angkutan' => 'required',
        'dari_kota' => 'required',
        'tujuan_kota' => 'required',
        'tanggal_mulai' => 'required|date',
        'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        'tanggal_terima' => 'required|date',
        'peserta' => 'required|array|min:1',
        'kelompok' => 'nullable|array',
        'kelompok.*.nomor_st' => 'nullable|string',
        'kelompok.*.tanggal_st' => 'nullable|date',
    ]);

    DB::beginTransaction();

    try {

        $perjalanan = PerjalananDinas::findOrFail($id);

        // ===============================
        // UPDATE PERJALANAN
        // ===============================
        $perjalanan->update([
            'tingkat_perjalanan' => $request->tingkat_perjalanan,
            'alat_angkutan'      => $request->alat_angkutan,
            'dari_kota'          => $request->dari_kota,
            'tujuan_kota'        => $request->tujuan_kota,
            'tanggal_terima'     => $request->tanggal_terima,
            'tanggal_mulai'      => $request->tanggal_mulai,
            'tanggal_akhir'      => $request->tanggal_akhir,
            'kode_mak'           => $request->kode_mak,
            'akun_biaya'         => $request->akun_biaya,
            'nama_kegiatan'      => $request->nama_kegiatan,
        ]);

        // ===============================
        // 🔥 HAPUS DATA LAMA (PENTING)
        // ===============================
        // hapus rincian dulu
        RincianBiaya::whereIn('perjalanan_dinas_pegawai_id',
            PerjalananDinasPegawai::where('perjalanan_dinas_id', $id)->pluck('id')
        )->delete();

        RincianBiaya::whereIn('nonpegawai_id',
            NonPegawai::where('perjalanan_dinas_id', $id)->pluck('id')
        )->delete();

        // hapus peserta
        PerjalananDinasPegawai::where('perjalanan_dinas_id', $id)->delete();
        NonPegawai::where('perjalanan_dinas_id', $id)->delete();

        // hapus sub kelompok
        SubKelompokPerjalanan::whereIn('kelompok_perjalanan_id',
            KelompokPerjalanan::where('perjalanan_dinas_id', $id)->pluck('id')
        )->delete();

        // hapus kelompok
        KelompokPerjalanan::where('perjalanan_dinas_id', $id)->delete();

        // ===============================
        // RE-INSERT (SAMA KYK STORE)
        // ===============================
        $allTipe = ['panitia', 'peserta', 'narasumber'];

        foreach ($allTipe as $tipe) {

            $listST = $request->peserta[$tipe] ?? [];

            if (empty($listST)) continue;

            // 1. KELOMPOK
            $kelompokModel = KelompokPerjalanan::create([
                'perjalanan_dinas_id' => $perjalanan->id,
                'nama_kelompok' => ucfirst($tipe),
            ]);

            foreach ($listST as $subIndex => $listPeserta) {

                if (empty($listPeserta)) continue;

                // 2. SUB KELOMPOK (ST)
                $subKelompok = SubKelompokPerjalanan::create([
                    'kelompok_perjalanan_id' => $kelompokModel->id,
                    'nomor_st' => $request->kelompok[$tipe][$subIndex]['nomor_st'] ?? null,
                    'tanggal_st' => $request->kelompok[$tipe][$subIndex]['tanggal_st'] ?? null,
                ]);

                foreach ($listPeserta as $pesertaId => $peserta) {

                    $isPegawai = !empty($peserta['pegawai_id']);

                    // ================= PEGAWAI =================
                    if ($isPegawai) {

                        if (empty($peserta['pegawai_id'])) continue;

                        $pp = PerjalananDinasPegawai::create([
                            'perjalanan_dinas_id' => $perjalanan->id,
                            'pegawai_id' => $peserta['pegawai_id'],
                            'subkelompok_id' => $subKelompok->id,
                        ]);

                        $rincianList = $request->rincian[$tipe][$subIndex][$pesertaId] ?? [];

                        foreach ($rincianList as $r) {

                            if (empty($r['jenis_biaya_id'])) continue;

                            $volume = $r['volume'] ?? 0;
                            $tarif  = $r['tarif'] ?? 0;

                            RincianBiaya::create([
                                'perjalanan_dinas_pegawai_id' => $pp->id,
                                'jenis_biaya_id' => $r['jenis_biaya_id'],
                                'uraian' => $r['uraian'] ?? null,
                                'volume' => $volume,
                                'satuan' => $r['satuan'] ?? '-',
                                'tarif'  => $tarif,
                                'total'  => $volume * $tarif,
                                'masuk_amplop' => isset($r['masuk_amplop']) ? 1 : 0, // TAMBAHKAN INI
                            ]);
                        }
                    }

                    // ================= NON PEGAWAI =================
                    else {

                        if (empty($peserta['nama'])) continue;

                        $np = NonPegawai::create([
                            'perjalanan_dinas_id' => $perjalanan->id,
                            'nama' => $peserta['nama'],
                            'nik'  => $peserta['nik'] ?? null,
                            'instansi' => $peserta['instansi'] ?? null,
                            'subkelompok_id' => $subKelompok->id,
                        ]);

                        $rincianList = $request->rincian[$tipe][$subIndex][$pesertaId] ?? [];

                        foreach ($rincianList as $r) {

                            if (empty($r['jenis_biaya_id'])) continue;

                            $volume = $r['volume'] ?? 0;
                            $tarif  = $r['tarif'] ?? 0;

                            RincianBiaya::create([
                                'nonpegawai_id' => $np->id,
                                'jenis_biaya_id' => $r['jenis_biaya_id'],
                                'uraian' => $r['uraian'] ?? null,
                                'volume' => $volume,
                                'satuan' => $r['satuan'] ?? '-',
                                'tarif'  => $tarif,
                                'total'  => $volume * $tarif,
                                'masuk_amplop' => isset($r['masuk_amplop']) ? 1 : 0, // TAMBAHKAN INI
                            ]);
                        }
                    }
                }
            }
        }

        DB::commit();

        return redirect()
            ->route('perjadin.index')
            ->with('success', 'Perjalanan dinas berhasil diperbarui');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', $e->getMessage());
    }
}

    public function exportNominatif($id)
{
    $perjalanan = PerjalananDinas::with([
        'kelompokPerjalanan.pegawai.rincian.jenisBiaya',
        'kelompokPerjalanan.nonpegawai.rincian.jenisBiaya'
    ])->findOrFail($id);

    return Excel::download(
        new NominatifPerjalananExport($perjalanan),
        'Nominatif_'.$perjalanan->nama_kegiatan.'.xlsx'
    );
}

    /**
     * =========================================================================
     * EXPORT NOMINATIF PER SURAT TUGAS (BERDASARKAN NOMOR ST)
     * - Menggabungkan semua peserta dari semua kelompok (Panitia/Peserta/Narasumber)
     *   yang memiliki nomor ST yang sama
     * - Menggunakan format yang SAMA PERSIS dengan export keseluruhan
     * =========================================================================
     */
    public function exportNominatifPerSubKelompok($perjalananId, $subKelompokId)
    {
        $perjalanan = PerjalananDinas::with([
            'pegawaiPerjalanan.pegawai',
            'pegawaiPerjalanan.rincian.jenisBiaya',
            'nonpegawai.rincian.jenisBiaya'
        ])->findOrFail($perjalananId);
        
        $subKelompok = SubKelompokPerjalanan::findOrFail($subKelompokId);
        $nomorST = $subKelompok->nomor_st;
        
        // Cari semua subKelompok dengan nomor ST yang SAMA
        $subKelompokIds = SubKelompokPerjalanan::whereHas('kelompok', function($q) use ($perjalananId) {
            $q->where('perjalanan_dinas_id', $perjalananId);
        })->where('nomor_st', $nomorST)->pluck('id')->toArray();
        
        // Filter pegawai berdasarkan subkelompok_id
        $filteredPegawai = $perjalanan->pegawaiPerjalanan->filter(function($pp) use ($subKelompokIds) {
            return in_array($pp->subkelompok_id, $subKelompokIds);
        });
        
        // Filter nonpegawai berdasarkan subkelompok_id
        $filteredNonPegawai = $perjalanan->nonpegawai->filter(function($np) use ($subKelompokIds) {
            return in_array($np->subkelompok_id, $subKelompokIds);
        });
        
        // Set relasi yang sudah difilter
        $perjalanan->setRelation('pegawaiPerjalanan', $filteredPegawai);
        $perjalanan->setRelation('nonpegawai', $filteredNonPegawai);
        
        return Excel::download(
            new NominatifPerjalananExport($perjalanan),
            'Nominatif_ST_' . preg_replace('/[^a-zA-Z0-9]/', '_', $nomorST ?? 'no_st') . '.xlsx'
        );
    }

    /**
     * =========================================================================
     * EXPORT SBY PER SURAT TUGAS (BERDASARKAN NOMOR ST)
     * - Satu file SBY untuk satu ST
     * - Total biaya = akumulasi semua peserta dalam ST tersebut
     * - Nama penerima diisi peserta pertama (urutan teratas)
     * =========================================================================
     */
    public function exportSbyPerSt($perjalananId, $subKelompokId)
    {
        $perjalanan = PerjalananDinas::with([
            'pegawaiPerjalanan.pegawai',
            'pegawaiPerjalanan.rincian.jenisBiaya',
            'nonpegawai.rincian.jenisBiaya'
        ])->findOrFail($perjalananId);
        
        $subKelompok = SubKelompokPerjalanan::findOrFail($subKelompokId);
        $nomorST = $subKelompok->nomor_st;
        
        // Cari semua subKelompok dengan nomor ST yang SAMA
        $subKelompokIds = SubKelompokPerjalanan::whereHas('kelompok', function($q) use ($perjalananId) {
            $q->where('perjalanan_dinas_id', $perjalananId);
        })->where('nomor_st', $nomorST)->pluck('id')->toArray();
        
        // Kumpulkan semua peserta dalam ST yang sama
        $allPegawai = $perjalanan->pegawaiPerjalanan->filter(fn($pp) => in_array($pp->subkelompok_id, $subKelompokIds));
        $allNonPegawai = $perjalanan->nonpegawai->filter(fn($np) => in_array($np->subkelompok_id, $subKelompokIds));
        
        // Hitung total akumulasi semua peserta
        $totalST = 0;
        foreach ($allPegawai as $pp) {
            $totalST += $pp->rincian->sum('total');
        }
        foreach ($allNonPegawai as $np) {
            $totalST += $np->rincian->sum('total');
        }
        
        // Ambil peserta pertama (urutan teratas)
        $pesertaPertama = null;
        if ($allPegawai->isNotEmpty()) {
            $pesertaPertama = $allPegawai->first();
            $pesertaPertama->type = 'pegawai';
        } elseif ($allNonPegawai->isNotEmpty()) {
            $pesertaPertama = $allNonPegawai->first();
            $pesertaPertama->type = 'nonpegawai';
        }
        
        $tanggalMulai = Carbon::parse($perjalanan->tanggal_mulai);
        $tanggalAkhir = Carbon::parse($perjalanan->tanggal_akhir);
        $tanggalTerima = Carbon::parse($perjalanan->tanggal_terima);
        
        // Format tanggal dinas
        if ($tanggalMulai->isSameDay($tanggalAkhir)) {
            $tanggalDinas = $tanggalMulai->translatedFormat('d F Y');
        } else {
            $tanggalDinas = $tanggalMulai->translatedFormat('d F Y') . ' s/d ' . $tanggalAkhir->translatedFormat('d F Y');
        }
        
        // Siapkan data penerima
        $namaPenerima = '-';
        $nipPenerima = '-';
        $kepada = 'Peserta Perjalanan Dinas';
        
        if ($pesertaPertama) {
            if ($pesertaPertama->type == 'pegawai') {
                $namaPenerima = $pesertaPertama->pegawai->nama;
                $nipPenerima = $pesertaPertama->pegawai->nip;
                $kepada = 'Pegawai BBPJT';
            } else {
                $namaPenerima = $pesertaPertama->nama;
                $nipPenerima = $pesertaPertama->nik ?? $pesertaPertama->instansi ?? '-';
                $kepada = $pesertaPertama->instansi ?? 'Non Pegawai';
            }
        }
        
        $uraian = 'Belanja Perjalanan Dinas untuk melaksanakan kegiatan ' 
                . $perjalanan->nama_kegiatan 
                . ' pada ' . $tanggalDinas 
                . ' bertempat di ' . $perjalanan->tujuan_kota;
        
        return Excel::download(
            new SbyPenyimpanExport([
                'tanggal' => $tanggalTerima,
                'nomor' => '                  /BBPJT/' . $tanggalTerima->format('m') . '/' . $tanggalTerima->format('Y'),
                'kepada' => $kepada,
                'kepada_nama' => $namaPenerima,
                'kepada_nip' => $nipPenerima,
                'nominal_angka' => (float) $totalST,
                'uraian' => $uraian,
                'mak' => $perjalanan->kode_mak
            ]),
            'SBY_ST_' . preg_replace('/[^a-zA-Z0-9]/', '_', $nomorST ?? 'no_st') . '.xlsx'
        );
    }

    public function exportSbyPenyimpan($ppId)
    {
        $pp = PerjalananDinasPegawai::with(['pegawai', 'perjalananDinas.surat', 'rincian'])
            ->findOrFail($ppId);

        $perjalanan = $pp->perjalananDinas;

        $tanggalMulai = Carbon::parse($perjalanan->tanggal_mulai);
        $tanggalAkhir = Carbon::parse($perjalanan->tanggal_akhir);
        $tanggalTerima = Carbon::parse($perjalanan->tanggal_terima);

        // format tanggal dinas
        if ($tanggalMulai->isSameDay($tanggalAkhir)) {
            $tanggalDinas = $tanggalMulai->translatedFormat('d F Y');
        } else {
            $tanggalDinas = $tanggalMulai->translatedFormat('d F Y') 
                . ' s/d ' . 
                $tanggalAkhir->translatedFormat('d F Y');
        }

        // total hanya untuk pegawai ini
        $total = $pp->rincian->sum('total');

        return Excel::download(
            new SbyPenyimpanExport([
                'tanggal' => $tanggalTerima,
                'nomor' => '                  /BBPJT/'.$tanggalTerima->format('m').'/'.$tanggalTerima->format('Y'),

                'kepada' => 'Pegawai BBPJT',
                'kepada_nama' => $pp->pegawai->nama,
                'kepada_nip' => $pp->pegawai->nip,

                'nominal_angka' => (float) $total,

                // pakai tanggalDinas
                'uraian' => 'Belanja Perjalanan Dinas untuk melaksanakan kegiatan '
                            .$perjalanan->nama_kegiatan.
                            ' pada '.$tanggalDinas.
                            ' bertempat di '.$perjalanan->tujuan_kota,

                'mak' => $perjalanan->kode_mak
            ]),
            'SBY-Penyimpan-'.$pp->id.'.xlsx'
        );
    }

    public function exportSbyNonPegawai($npId)
    {
        $np = NonPegawai::with(['perjalananDinas.surat', 'rincian'])
            ->findOrFail($npId);

        $perjalanan = $np->perjalananDinas;

        $namaKelompok = $np->subKelompok->kelompok->nama_kelompok;

        $tanggalMulai = Carbon::parse($perjalanan->tanggal_mulai);
        $tanggalAkhir = Carbon::parse($perjalanan->tanggal_akhir);
        $tanggalTerima = Carbon::parse($perjalanan->tanggal_terima);

        // format tanggal dinas
        if ($tanggalMulai->isSameDay($tanggalAkhir)) {
            $tanggalDinas = $tanggalMulai->translatedFormat('d F Y');
        } else {
            $tanggalDinas = $tanggalMulai->translatedFormat('d F Y') 
                . ' s/d ' . 
                $tanggalAkhir->translatedFormat('d F Y');
        }

        // total hanya untuk pegawai ini
        $total = $np->rincian->sum('total');

        return Excel::download(
            new SbyPenyimpanExport([
                'tanggal' => $tanggalTerima,
                'nomor' => '                  /BBPJT/'.$tanggalTerima->format('m').'/'.$tanggalTerima->format('Y'),

                'kepada' => $namaKelompok . ' Perjalanan Dinas',
                'kepada_nama' => $np->nama,
                'kepada_nip' => $np->nik,

                'nominal_angka' => (float) $total,

                // pakai tanggalDinas
                'uraian' => 'Belanja Perjalanan Dinas untuk melaksanakan kegiatan '
                            .$perjalanan->nama_kegiatan.
                            ' pada '.$tanggalDinas.
                            ' bertempat di '.$perjalanan->tujuan_kota,

                'mak' => $perjalanan->kode_mak
            ]),
            'SBY-Penyimpan-'.$np->id.'.xlsx'
        );
    }

    public function exportSbyNonPegawaii($npId)
    {
        $np = NonPegawai::with(['perjalananDinas.surat', 'rincian'])
            ->findOrFail($npId);

        $perjalanan = $np->perjalananDinas;

        $tanggal_mulai = Carbon::parse($perjalanan->tanggal_mulai);
        $tanggal_akhir = Carbon::parse($perjalanan->tanggal_akhir);

        // format tanggal dinas
        if ($tanggal_mulai->isSameDay($tanggal_akhir)) {
            $tanggalDinas = $tanggal_mulai->translatedFormat('d F Y');
        } else {
            $tanggalDinas = $tanggal_mulai->translatedFormat('d F Y')
                .' s/d '.$tanggal_akhir->translatedFormat('d F Y');
        }

        // tanggal untuk nomor & header (pakai tanggal terima)
        $tanggal = Carbon::parse($perjalanan->tanggal_terima);

        // total hanya untuk nonpegawai ini
        $total = $np->rincian->sum('total');

        return Excel::download(
            new SbyPenyimpanExport([
                'tanggal' => $tanggal,
                'nomor' => '                  /BBPJT/'.$tanggal->format('m').'/'.$tanggal->format('Y'),

                'kepada' => $np->instansi ?? $np->nama, // kalau instansi kosong fallback ke nama
                'kepada_nama' => $np->nama,
                'kepada_nip' => $np->nik ?? '-',

                'nominal_angka' => (float) $total,

                'uraian' => 'Belanja Perjalanan Dinas untuk melaksanakan kegiatan '
                            .$perjalanan->nama_kegiatan.
                            ' pada '.$tanggalDinas.
                            ' bertempat di '.$perjalanan->tujuan_kota,

                'mak' => $perjalanan->kode_mak
            ]),
            'SBY-NonPegawai-'.$np->id.'.xlsx'
        );
    }

    private function safeValue($text)
    {
        return htmlspecialchars($text ?? '-', ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    public function exportKuitansi($id)
{
    $pp = PerjalananDinasPegawai::with([
        'pegawai',
        'perjalananDinas.surat',
        'rincian.jenisBiaya',
        'subKelompok',
    ])->findOrFail($id);

    $perjalanan = $pp->perjalananDinas;
    $pegawai    = $pp->pegawai;

    $tanggal = $perjalanan->tanggal_mulai;

    $tanggalMulai = Carbon::parse(
        $perjalanan->tanggal_mulai
    );

    $tanggalAkhir = Carbon::parse(
        $perjalanan->tanggal_akhir
    );

    $lamaPerjalanan = $tanggalMulai
        ->diffInDays($tanggalAkhir) + 1;

    $bendahara = PejabatPeriode::getByTanggal(
        'Bendahara Pengeluaran',
        $tanggal
    );

    $ppk = PejabatPeriode::getByTanggal(
        'Pejabat Pembuat Komitmen',
        $tanggal
    );

    $rincian = $pp->rincian;

    $sumTotal = $rincian->sum('total');

    // ===============================
    // LOAD TEMPLATE
    // ===============================
    $templateFile = Template::where(
            'jenis',
            'kuitansi_spd'
        )
        ->latest()
        ->first();

    $template = new TemplateProcessor(
        storage_path(
            'app/public/' . $templateFile->file_path
        )
    );

    // ===============================
    // HANDLE BLOCK_KUITANSI
    // ===============================
    $template->cloneBlock(
        'block_kuitansi',
        1,
        true,
        true
    );

    // ===============================
    // SET DATA UMUM
    // ===============================
    $data = [

        'tahun_anggaran#1' => $this->safeValue(
            $perjalanan->tahun_anggaran ?? date('Y')
        ),

        'beban_mak#1' => $this->safeValue(
            $perjalanan->kode_mak ?? '-'
        ),

        'jumlah_rupiah#1' => 'Rp'
            . number_format(
                $sumTotal,
                0,
                ',',
                '.'
            ),

        'terbilang#1' => $this->safeValue(
            $this->terbilang($sumTotal)
        ),

        'keperluan#1' => $this->safeValue(
            $perjalanan->nama_kegiatan ?? '-'
        ),

        'nomor_spd#1' => $this->safeValue(
            $pp->subKelompok->nomor_st ?? '-'
        ),

        'tanggal_spd#1' => $pp->subKelompok->tanggal_st
            ? Carbon::parse(
                $pp->subKelompok->tanggal_st
            )->translatedFormat('d F Y')
            : $this->safeValue('-'),

        'tujuan#1' => $this->safeValue(
            $perjalanan->tujuan_kota ?? '-'
        ),

        // PERJALANAN
        'nama_kegiatan#1' => $this->safeValue(
            $perjalanan->nama_kegiatan ?? '-'
        ),

        'alat_angkutan#1' => $this->safeValue(
            $perjalanan->alat_angkutan ?? '-'
        ),

        'dari_kota#1' => $this->safeValue(
            $perjalanan->dari_kota ?? '-'
        ),

        'tujuan_kota#1' => $this->safeValue(
            $perjalanan->tujuan_kota ?? '-'
        ),

        'tingkat_perjalanan#1' => $this->safeValue(
            $perjalanan->tingkat_perjalanan ?? '-'
        ),

        // TANGGAL
        'tanggal_mulai#1' => $tanggalMulai
            ->translatedFormat('d F Y'),

        'tanggal_akhir#1' => $tanggalAkhir
            ->translatedFormat('d F Y'),

        'lama_perjalanan#1' => $lamaPerjalanan . ' hari',

        // TANGGAL TERIMA
        'tanggal_terima#1' => $perjalanan->tanggal_terima
            ? Carbon::parse(
                $perjalanan->tanggal_terima
            )->translatedFormat('d F Y')
            : $this->safeValue('-'),

        // PENERIMA
        'nama_penerima#1' => $this->safeValue(
            $pegawai->nama
        ),

        'nip_penerima#1' => $this->safeValue(
            $pegawai->nip
        ),

        'pangkat_golongan_penerima#1' => $this->safeValue(
            $pegawai->pangkat_golongan ?? '-'
        ),

        'jabatan_penerima#1' => $this->safeValue(
            $pegawai->jabatan ?? '-'
        ),

        // BENDAHARA
        'nama_bendahara#1' => $this->safeValue(
            $bendahara?->pegawai?->nama ?? '-'
        ),

        'nip_bendahara#1' => $this->safeValue(
            $bendahara?->pegawai?->nip ?? '-'
        ),

        // PPK
        'nama_ppk#1' => $this->safeValue(
            $ppk?->pegawai?->nama ?? '-'
        ),

        'nip_ppk#1' => $this->safeValue(
            $ppk?->pegawai?->nip ?? '-'
        ),

        'sum_total#1' => number_format(
            $sumTotal,
            0,
            ',',
            '.'
        ),
    ];

    foreach ($data as $key => $value) {

        $template->setValue(
            $key,
            $value ?? '-'
        );
    }

    // ===============================
    // RINCIAN DINAMIS
    // ===============================
    if ($rincian->isEmpty()) {

        $template->setValue(
            'no#1',
            '-'
        );

        $template->setValue(
            'uraian#1',
            '-'
        );

        $template->setValue(
            'jumlah#1',
            '-'
        );

        $template->setValue(
            'keterangan#1',
            '-'
        );

    } else {

        $template->cloneRow(
            'no#1',
            $rincian->count()
        );

        foreach ($rincian as $i => $r) {

            $index = $i + 1;

            $jenis = $this->safeValue(
                $r->jenisBiaya->nama_biaya
            );

            $detail = $this->safeValue(
                $r->uraian
            );

            $uraian = $jenis;

            if (!empty($r->uraian)) {

                $uraian .= " ({$detail})";
            }

            $volume = (int) $r->volume;

            if ($r->volume && $r->tarif) {

                $uraian .= " : {$volume} "
                    . $this->safeValue($r->satuan)
                    . " x Rp"
                    . number_format(
                        $r->tarif,
                        0,
                        ',',
                        '.'
                    );
            }

            $template->setValue(
                "no#1#{$index}",
                $index
            );

            $template->setValue(
                "uraian#1#{$index}",
                $uraian
            );

            $template->setValue(
                "jumlah#1#{$index}",
                'Rp' . number_format(
                    $r->total,
                    0,
                    ',',
                    '.'
                )
            );

            $template->setValue(
                "keterangan#1#{$index}",
                '-'
            );
        }
    }

    // ===============================
    // RINCIAN RIIL
    // ===============================
    $rincianRiil = $rincian
    ->filter(function ($r) {

        $nama = strtolower(
            $r->jenisBiaya->nama_biaya
        );

        $keywords = [
            'taksi',
            'transport',
        ];

        foreach ($keywords as $keyword) {

            if (str_contains($nama, $keyword)) {
                return true;
            }
        }

        return false;
    })
    ->values();

    if ($rincianRiil->count() == 1) {

        $r = $rincianRiil->first();

        $uraian = $r->uraian
            ? $this->safeValue(
                $r->jenisBiaya->nama_biaya
            ) . ' (' . $this->safeValue(
                $r->uraian
            ) . ')'
            : $this->safeValue(
                $r->jenisBiaya->nama_biaya
            );

        $template->setValue(
            'no_riil#1',
            1
        );

        $template->setValue(
            'uraian_riil#1',
            $uraian
        );

        $template->setValue(
            'jumlah_riil#1',
            'Rp' . number_format(
                $r->total,
                0,
                ',',
                '.'
            )
        );

    } elseif ($rincianRiil->isEmpty()) {

        $template->setValue(
            'no_riil#1',
            '-'
        );

        $template->setValue(
            'uraian_riil#1',
            '-'
        );

        $template->setValue(
            'jumlah_riil#1',
            '-'
        );

    } else {

        $template->cloneRow(
            'no_riil#1',
            $rincianRiil->count()
        );

        foreach ($rincianRiil as $i => $r) {

            $index = $i + 1;

            $uraian = $r->uraian
                ? $this->safeValue(
                    $r->jenisBiaya->nama_biaya
                ) . ' (' . $this->safeValue(
                    $r->uraian
                ) . ')'
                : $this->safeValue(
                    $r->jenisBiaya->nama_biaya
                );

            $template->setValue(
                "no_riil#1#{$index}",
                $index
            );

            $template->setValue(
                "uraian_riil#1#{$index}",
                $uraian
            );

            $template->setValue(
                "jumlah_riil#1#{$index}",
                'Rp' . number_format(
                    $r->total,
                    0,
                    ',',
                    '.'
                )
            );
        }
    }

    $sumTotalRiil = $rincianRiil->sum('total');

    $template->setValue(
        'sum_total_riil#1',
        number_format(
            $sumTotalRiil,
            0,
            ',',
            '.'
        )
    );

    // ===============================
    // GENERATE FILE
    // ===============================
    $fileName = 'Kuitansi_'
        . str_replace(
            '/',
            '-',
            $perjalanan->surat->nomor_st ?? 'SPD'
        )
        . '_'
        . $pegawai->nama
        . '.docx';

    $savePath = storage_path($fileName);

    $template->saveAs($savePath);

    return response()
        ->download($savePath)
        ->deleteFileAfterSend(true);
}

   public function exportKuitansiNonPegawai($npId)
{
    $np = NonPegawai::with([
        'perjalananDinas.surat',
        'rincian.jenisBiaya',
        'subKelompok',
    ])->findOrFail($npId);

    $perjalanan = $np->perjalananDinas;

    $tanggal = $perjalanan->tanggal_mulai;

    $tanggalMulai = Carbon::parse(
        $perjalanan->tanggal_mulai
    );

    $tanggalAkhir = Carbon::parse(
        $perjalanan->tanggal_akhir
    );

    // lama perjalanan
    $lamaPerjalanan = $tanggalMulai
        ->diffInDays($tanggalAkhir) + 1;

    $bendahara = PejabatPeriode::getByTanggal(
        'Bendahara Pengeluaran',
        $tanggal
    );

    $ppk = PejabatPeriode::getByTanggal(
        'Pejabat Pembuat Komitmen',
        $tanggal
    );

    $rincian = $np->rincian;

    $sumTotal = $rincian->sum('total');

    // ===============================
    // LOAD TEMPLATE
    // ===============================
    $templateFile = Template::where(
            'jenis',
            'kuitansi_spd'
        )
        ->latest()
        ->first();

    $template = new TemplateProcessor(
        storage_path(
            'app/public/' . $templateFile->file_path
        )
    );

    // ===============================
    // HANDLE BLOCK_KUITANSI
    // ===============================
    $template->cloneBlock(
        'block_kuitansi',
        1,
        true,
        true
    );

    // ===============================
    // DATA UMUM
    // ===============================
    $data = [

        'tahun_anggaran#1' => $this->safeValue(
            $perjalanan->tahun_anggaran ?? date('Y')
        ),

        'beban_mak#1' => $this->safeValue(
            $perjalanan->kode_mak ?? '-'
        ),

        'jumlah_rupiah#1' => 'Rp'
            . number_format(
                $sumTotal,
                0,
                ',',
                '.'
            ),

        'terbilang#1' => $this->safeValue(
            $this->terbilang($sumTotal)
        ),

        'keperluan#1' => $this->safeValue(
            $perjalanan->nama_kegiatan ?? '-'
        ),

        'nomor_spd#1' => $this->safeValue(
            $np->subKelompok->nomor_st ?? '-'
        ),

        'tanggal_spd#1' => $np->subKelompok->tanggal_st
            ? Carbon::parse(
                $np->subKelompok->tanggal_st
            )->translatedFormat('d F Y')
            : $this->safeValue('-'),

        'tujuan#1' => $this->safeValue(
            $perjalanan->tujuan_kota ?? '-'
        ),

        // PERJALANAN
        'nama_kegiatan#1' => $this->safeValue(
            $perjalanan->nama_kegiatan ?? '-'
        ),

        'alat_angkutan#1' => $this->safeValue(
            $perjalanan->alat_angkutan ?? '-'
        ),

        'dari_kota#1' => $this->safeValue(
            $perjalanan->dari_kota ?? '-'
        ),

        'tujuan_kota#1' => $this->safeValue(
            $perjalanan->tujuan_kota ?? '-'
        ),

        'tingkat_perjalanan#1' => $this->safeValue(
            $perjalanan->tingkat_perjalanan ?? '-'
        ),

        // TANGGAL
        'tanggal_mulai#1' => $tanggalMulai
            ->translatedFormat('d F Y'),

        'tanggal_akhir#1' => $tanggalAkhir
            ->translatedFormat('d F Y'),

        'lama_perjalanan#1' => $lamaPerjalanan . ' hari',

        // TANGGAL TERIMA
        'tanggal_terima#1' => $perjalanan->tanggal_terima
            ? Carbon::parse(
                $perjalanan->tanggal_terima
            )->translatedFormat('d F Y')
            : $this->safeValue('-'),

        // PENERIMA NON PEGAWAI
        'nama_penerima#1' => $this->safeValue(
            $np->nama
        ),

        'nip_penerima#1' => $this->safeValue(
            $np->nik
        ),

        'pangkat_golongan_penerima#1' => $this->safeValue(
            $np->instansi ?? '-'
        ),

        'jabatan_penerima#1' => $this->safeValue(
            $np->instansi ?? '-'
        ),

        // BENDAHARA
        'nama_bendahara#1' => $this->safeValue(
            $bendahara?->pegawai?->nama ?? '-'
        ),

        'nip_bendahara#1' => $this->safeValue(
            $bendahara?->pegawai?->nip ?? '-'
        ),

        // PPK
        'nama_ppk#1' => $this->safeValue(
            $ppk?->pegawai?->nama ?? '-'
        ),

        'nip_ppk#1' => $this->safeValue(
            $ppk?->pegawai?->nip ?? '-'
        ),

        'sum_total#1' => number_format(
            $sumTotal,
            0,
            ',',
            '.'
        ),
    ];

    foreach ($data as $key => $value) {

        $template->setValue(
            $key,
            $value ?? '-'
        );
    }

    // ===============================
    // RINCIAN DINAMIS
    // ===============================
    if ($rincian->isEmpty()) {

        $template->setValue(
            'no#1',
            '-'
        );

        $template->setValue(
            'uraian#1',
            '-'
        );

        $template->setValue(
            'jumlah#1',
            '-'
        );

        $template->setValue(
            'keterangan#1',
            '-'
        );

    } else {

        $template->cloneRow(
            'no#1',
            $rincian->count()
        );

        foreach ($rincian as $i => $r) {

            $index = $i + 1;

            $jenis = $this->safeValue(
                $r->jenisBiaya->nama_biaya
            );

            $detail = $this->safeValue(
                $r->uraian
            );

            $uraian = $jenis;

            if (!empty($r->uraian)) {

                $uraian .= " ({$detail})";
            }

            $volume = (int) $r->volume;

            if ($r->volume && $r->tarif) {

                $uraian .= " : {$volume} "
                    . $this->safeValue($r->satuan)
                    . " x Rp"
                    . number_format(
                        $r->tarif,
                        0,
                        ',',
                        '.'
                    );
            }

            $template->setValue(
                "no#1#{$index}",
                $index
            );

            $template->setValue(
                "uraian#1#{$index}",
                $uraian
            );

            $template->setValue(
                "jumlah#1#{$index}",
                'Rp' . number_format(
                    $r->total,
                    0,
                    ',',
                    '.'
                )
            );

            $template->setValue(
                "keterangan#1#{$index}",
                '-'
            );
        }
    }

    // ===============================
    // RINCIAN RIIL
    // ===============================
    $rincianRiil = $rincian
        ->filter(function ($r) {

            $nama = strtolower(
                $r->jenisBiaya->nama_biaya
            );

            return str_contains(
                $nama,
                'taksi'
            );
        })
        ->values();

    if ($rincianRiil->count() == 1) {

        $r = $rincianRiil->first();

        $uraian = !empty($r->uraian)
            ? $this->safeValue(
                $r->jenisBiaya->nama_biaya
            ) . ' (' . $this->safeValue(
                $r->uraian
            ) . ')'
            : $this->safeValue(
                $r->jenisBiaya->nama_biaya
            );

        $template->setValue(
            'no_riil#1',
            1
        );

        $template->setValue(
            'uraian_riil#1',
            $uraian
        );

        $template->setValue(
            'jumlah_riil#1',
            'Rp' . number_format(
                $r->total,
                0,
                ',',
                '.'
            )
        );

    } elseif ($rincianRiil->isEmpty()) {

        $template->setValue(
            'no_riil#1',
            '-'
        );

        $template->setValue(
            'uraian_riil#1',
            '-'
        );

        $template->setValue(
            'jumlah_riil#1',
            '-'
        );

    } else {

        $template->cloneRow(
            'no_riil#1',
            $rincianRiil->count()
        );

        foreach ($rincianRiil as $i => $r) {

            $index = $i + 1;

            $uraian = !empty($r->uraian)
                ? $this->safeValue(
                    $r->jenisBiaya->nama_biaya
                ) . ' (' . $this->safeValue(
                    $r->uraian
                ) . ')'
                : $this->safeValue(
                    $r->jenisBiaya->nama_biaya
                );

            $template->setValue(
                "no_riil#1#{$index}",
                $index
            );

            $template->setValue(
                "uraian_riil#1#{$index}",
                $uraian
            );

            $template->setValue(
                "jumlah_riil#1#{$index}",
                'Rp' . number_format(
                    $r->total,
                    0,
                    ',',
                    '.'
                )
            );
        }
    }

    $sumTotalRiil = $rincianRiil->sum('total');

    $template->setValue(
        'sum_total_riil#1',
        number_format(
            $sumTotalRiil,
            0,
            ',',
            '.'
        )
    );

    // ===============================
    // GENERATE FILE
    // ===============================
    $fileName = 'Kuitansi_'
        . str_replace(
            '/',
            '-',
            $perjalanan->surat->nomor_st ?? 'SPD'
        )
        . '_'
        . $np->nama
        . '.docx';

    $savePath = storage_path($fileName);

    $template->saveAs($savePath);

    return response()
        ->download($savePath)
        ->deleteFileAfterSend(true);
}

    public function exportKuitansiST($subKelompokId)
{
    $subKelompok = SubKelompokPerjalanan::with([

        // PEGAWAI
        'pegawai.pegawai',
        'pegawai.perjalananDinas.surat',
        'pegawai.rincian.jenisBiaya',

        // NON PEGAWAI
        'nonPegawai',
        'nonPegawai.perjalananDinas.surat',
        'nonPegawai.rincian.jenisBiaya',

    ])->findOrFail($subKelompokId);

    // ===============================
    // GABUNG PESERTA
    // ===============================
    $pesertaList = collect();

    // PEGAWAI
    foreach ($subKelompok->pegawai as $pp) {

        $pesertaList->push([
            'tipe' => 'pegawai',
            'data' => $pp,
            'nama' => $pp->pegawai->nama,
            'nip'  => $pp->pegawai->nip,
            'jabatan' => $pp->pegawai->jabatan,
            'pangkat_golongan' => $pp->pegawai->pangkat_golongan,
            'perjalanan' => $pp->perjalananDinas,
            'rincian' => $pp->rincian,
        ]);
    }

    // NON PEGAWAI
    foreach ($subKelompok->nonPegawai as $np) {

        $pesertaList->push([
            'tipe' => 'nonpegawai',
            'data' => $np,
            'nama' => $np->nama,
            'nip'  => '-',
            'jabatan' => $np->instansi ?? '-',
            'pangkat_golongan' => '-',
            'perjalanan' => $np->perjalananDinas,
            'rincian' => $np->rincian,
        ]);
    }

    // ===============================
    // LOAD TEMPLATE
    // ===============================
    $templateFile = Template::where('jenis', 'kuitansi_spd')
                    ->latest()
                    ->first();

    $template = new TemplateProcessor(
        storage_path('app/public/' . $templateFile->file_path)
    );

    // ===============================
    // CLONE BLOCK
    // ===============================
    $template->cloneBlock(
        'block_kuitansi',
        $pesertaList->count(),
        true,
        true
    );

    foreach ($pesertaList->values() as $pIndex => $item) {

        $index = $pIndex + 1;

        $perjalanan = $item['perjalanan'];
        $rincian    = $item['rincian'];

        $tanggal = $perjalanan->tanggal_mulai;

        $tanggalMulai = Carbon::parse(
            $perjalanan->tanggal_mulai
        );

        $tanggalAkhir = Carbon::parse(
            $perjalanan->tanggal_akhir
        );

        $lamaPerjalanan =
            $tanggalMulai->diffInDays($tanggalAkhir) + 1;

        $bendahara = PejabatPeriode::getByTanggal(
            'Bendahara Pengeluaran',
            $tanggal
        );

        $ppk = PejabatPeriode::getByTanggal(
            'Pejabat Pembuat Komitmen',
            $tanggal
        );

        $sumTotal = $rincian->sum('total');

        // ===============================
        // DATA UMUM
        // ===============================
        $data = [

            'tahun_anggaran' =>
                $this->safeValue(
                    $perjalanan->tahun_anggaran ?? date('Y')
                ),

            'beban_mak' =>
                $this->safeValue(
                    $perjalanan->kode_mak ?? '-'
                ),

            'jumlah_rupiah' =>
                'Rp' . number_format(
                    $sumTotal,
                    0,
                    ',',
                    '.'
                ),

            'terbilang' =>
                $this->safeValue(
                    $this->terbilang($sumTotal)
                ),

            'keperluan' =>
                $this->safeValue(
                    $perjalanan->nama_kegiatan ?? '-'
                ),

            'nomor_spd' =>
                $this->safeValue(
                    $subKelompok->nomor_st ?? '-'
                ),

            'tanggal_spd' =>
                $subKelompok->tanggal_st
                    ? Carbon::parse(
                        $subKelompok->tanggal_st
                    )->translatedFormat('d F Y')
                    : '-',

            'tujuan' =>
                $this->safeValue(
                    $perjalanan->tujuan_kota ?? '-'
                ),

            'nama_kegiatan' =>
                $this->safeValue(
                    $perjalanan->nama_kegiatan ?? '-'
                ),

            'alat_angkutan' =>
                $this->safeValue(
                    $perjalanan->alat_angkutan ?? '-'
                ),

            'dari_kota' =>
                $this->safeValue(
                    $perjalanan->dari_kota ?? '-'
                ),

            'tujuan_kota' =>
                $this->safeValue(
                    $perjalanan->tujuan_kota ?? '-'
                ),

            'tingkat_perjalanan' =>
                $this->safeValue(
                    $perjalanan->tingkat_perjalanan ?? '-'
                ),

            'tanggal_mulai' =>
                $tanggalMulai->translatedFormat('d F Y'),

            'tanggal_akhir' =>
                $tanggalAkhir->translatedFormat('d F Y'),

            'lama_perjalanan' =>
                $lamaPerjalanan . ' hari',

            'tanggal_terima' =>
                $perjalanan->tanggal_terima
                    ? Carbon::parse(
                        $perjalanan->tanggal_terima
                    )->translatedFormat('d F Y')
                    : '-',

            // PENERIMA
            'nama_penerima' =>
                $this->safeValue($item['nama']),

            'nip_penerima' =>
                $this->safeValue($item['nip']),

            'pangkat_golongan_penerima' =>
                $this->safeValue(
                    $item['pangkat_golongan']
                ),

            'jabatan_penerima' =>
                $this->safeValue(
                    $item['jabatan']
                ),

            // BENDAHARA
            'nama_bendahara' =>
                $this->safeValue(
                    $bendahara?->pegawai?->nama ?? '-'
                ),

            'nip_bendahara' =>
                $this->safeValue(
                    $bendahara?->pegawai?->nip ?? '-'
                ),

            // PPK
            'nama_ppk' =>
                $this->safeValue(
                    $ppk?->pegawai?->nama ?? '-'
                ),

            'nip_ppk' =>
                $this->safeValue(
                    $ppk?->pegawai?->nip ?? '-'
                ),

            'sum_total' =>
                number_format(
                    $sumTotal,
                    0,
                    ',',
                    '.'
                ),
        ];

        foreach ($data as $key => $value) {

            $template->setValue(
                "{$key}#{$index}",
                $value ?? '-'
            );
        }

        // ===============================
        // RINCIAN
        // ===============================
        if ($rincian->isEmpty()) {

            $template->setValue(
                "no#{$index}",
                '-'
            );

            $template->setValue(
                "uraian#{$index}",
                '-'
            );

            $template->setValue(
                "jumlah#{$index}",
                '-'
            );

            $template->setValue(
                "keterangan#{$index}",
                '-'
            );

        } else {

            $template->cloneRow(
                "no#{$index}",
                $rincian->count()
            );

            foreach ($rincian->values() as $rIndex => $r) {

                $row = $rIndex + 1;

                $jenis = $this->safeValue(
                    $r->jenisBiaya->nama_biaya
                );

                $detail = $this->safeValue(
                    $r->uraian
                );

                $uraian = $jenis;

                if (!empty($r->uraian)) {

                    $uraian .= " ({$detail})";
                }

                if ($r->volume && $r->tarif) {

                    $uraian .= " : "
                        . (int) $r->volume
                        . " "
                        . $this->safeValue($r->satuan)
                        . " x Rp"
                        . number_format(
                            $r->tarif,
                            0,
                            ',',
                            '.'
                        );
                }

                $template->setValue(
                    "no#{$index}#{$row}",
                    $row
                );

                $template->setValue(
                    "uraian#{$index}#{$row}",
                    $uraian
                );

                $template->setValue(
                    "jumlah#{$index}#{$row}",
                    'Rp' . number_format(
                        $r->total,
                        0,
                        ',',
                        '.'
                    )
                );

                $template->setValue(
                    "keterangan#{$index}#{$row}",
                    '-'
                );
            }
        }

        // ===============================
        // RINCIAN RIIL
        // ===============================
        $rincianRiil = $rincian->filter(function ($r) {

            $nama = strtolower(
                $r->jenisBiaya->nama_biaya
            );

            return str_contains($nama, 'taksi');
        })->values();

        if ($rincianRiil->isEmpty()) {

            $template->setValue(
                "no_riil#{$index}",
                '-'
            );

            $template->setValue(
                "uraian_riil#{$index}",
                '-'
            );

            $template->setValue(
                "jumlah_riil#{$index}",
                '-'
            );

        } else {

            $template->cloneRow(
                "no_riil#{$index}",
                $rincianRiil->count()
            );

            foreach ($rincianRiil as $riilIndex => $r) {

                $row = $riilIndex + 1;

                $uraian = $r->uraian
                    ? $this->safeValue(
                        $r->jenisBiaya->nama_biaya
                    ) . ' (' .
                    $this->safeValue(
                        $r->uraian
                    ) . ')'
                    : $this->safeValue(
                        $r->jenisBiaya->nama_biaya
                    );

                $template->setValue(
                    "no_riil#{$index}#{$row}",
                    $row
                );

                $template->setValue(
                    "uraian_riil#{$index}#{$row}",
                    $uraian
                );

                $template->setValue(
                    "jumlah_riil#{$index}#{$row}",
                    'Rp' . number_format(
                        $r->total,
                        0,
                        ',',
                        '.'
                    )
                );
            }
        }

        $template->setValue(
            "sum_total_riil#{$index}",
            number_format(
                $rincianRiil->sum('total'),
                0,
                ',',
                '.'
            )
        );
    }

    // ===============================
    // GENERATE FILE
    // ===============================
    $fileName = 'Kuitansi_ST_'
        . str_replace(
            '/',
            '-',
            $subKelompok->nomor_st ?? 'SPD'
        )
        . '.docx';

    $savePath = storage_path($fileName);

    $template->saveAs($savePath);

    return response()
        ->download($savePath)
        ->deleteFileAfterSend(true);
}

    private function terbilang($angka)
    {
        $angka = abs($angka);
        $huruf = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam",
                "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];

        if ($angka < 12)
            return " " . $huruf[$angka];
        elseif ($angka < 20)
            return $this->terbilang($angka - 10) . " Belas";
        elseif ($angka < 100)
            return $this->terbilang($angka / 10) . " Puluh" . $this->terbilang($angka % 10);
        elseif ($angka < 200)
            return " Seratus" . $this->terbilang($angka - 100);
        elseif ($angka < 1000)
            return $this->terbilang($angka / 100) . " Ratus" . $this->terbilang($angka % 100);
        elseif ($angka < 2000)
            return " Seribu" . $this->terbilang($angka - 1000);
        elseif ($angka < 1000000)
            return $this->terbilang($angka / 1000) . " Ribu" . $this->terbilang($angka % 1000);
        elseif ($angka < 1000000000)
            return $this->terbilang($angka / 1000000) . " Juta" . $this->terbilang($angka % 1000000);
        
        return "";
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $perjalanan = PerjalananDinas::findOrFail($id);

            // ===============================
            // HAPUS RINCIAN PEGAWAI
            // ===============================
            foreach ($perjalanan->pegawaiPerjalanan as $pp) {
                RincianBiaya::where('perjalanan_dinas_pegawai_id', $pp->id)->delete();
            }

            // ===============================
            // HAPUS RINCIAN NON PEGAWAI
            // ===============================
            foreach ($perjalanan->nonpegawai as $np) {
                RincianBiaya::where('nonpegawai_id', $np->id)->delete();
            }

            // ===============================
            // HAPUS PEGAWAI (pivot)
            // ===============================
            $perjalanan->pegawaiPerjalanan()->delete();

            // ===============================
            // HAPUS NON PEGAWAI
            // ===============================
            $perjalanan->nonpegawai()->delete();

            // ===============================
            // HAPUS SURAT
            // ===============================
            if ($perjalanan->surat) {
                $perjalanan->surat()->delete();
            }

            // ===============================
            // HAPUS PERJALANAN
            // ===============================
            $perjalanan->delete();

            DB::commit();

            return redirect()
                ->route('perjadin.index')
                ->with('success', 'Data perjalanan dinas berhasil dihapus');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }

    private function generateSbyPegawaiFile($pp)
{
    $perjalanan = $pp->perjalananDinas;

    $tanggalMulai = Carbon::parse($perjalanan->tanggal_mulai);
    $tanggalAkhir = Carbon::parse($perjalanan->tanggal_akhir);
    $tanggalTerima = Carbon::parse($perjalanan->tanggal_terima);

    $tanggalDinas = $tanggalMulai->isSameDay($tanggalAkhir)
        ? $tanggalMulai->translatedFormat('d F Y')
        : $tanggalMulai->translatedFormat('d F Y').' s/d '.$tanggalAkhir->translatedFormat('d F Y');

    $total = $pp->rincian->sum('total');

    $name = Str::slug($pp->pegawai->nama); 
    $fileName = 'SBY-Penyimpan-'.$name.'.xlsx';
    $path = 'temp/'.$fileName;

    Excel::store(
        new SbyPenyimpanExport([
            'tanggal' => $tanggalTerima,
            'nomor' => '                  /BBPJT/'.$tanggalTerima->format('m').'/'.$tanggalTerima->format('Y'),
            'kepada' => 'Pegawai BBPJT',
            'kepada_nama' => $pp->pegawai->nama,
            'kepada_nip' => $pp->pegawai->nip,
            'nominal_angka' => (float) $total,
            'uraian' => 'Belanja Perjalanan Dinas untuk '.$perjalanan->nama_kegiatan.' pada '.$tanggalDinas.' di '.$perjalanan->tujuan_kota,
            'mak' => $perjalanan->kode_mak
        ]),
        $path,
        'local'
    );

    return Storage::disk('local')->path($path);
}

private function generateSbyNonPegawaiFile($np)
{
    $perjalanan = $np->perjalananDinas;

    $namaKelompok = $np->subKelompok->kelompok->nama_kelompok;

    $tanggalMulai = Carbon::parse($perjalanan->tanggal_mulai);
    $tanggalAkhir = Carbon::parse($perjalanan->tanggal_akhir);
    $tanggalTerima = Carbon::parse($perjalanan->tanggal_terima);

    $tanggalDinas = $tanggalMulai->isSameDay($tanggalAkhir)
        ? $tanggalMulai->translatedFormat('d F Y')
        : $tanggalMulai->translatedFormat('d F Y').' s/d '.$tanggalAkhir->translatedFormat('d F Y');

    $total = $np->rincian->sum('total');

    $name = Str::slug($np->nama); 
    $fileName = 'SBY-NonPegawai-'.$name.'.xlsx';
    $path = 'temp/'.$fileName;

    Excel::store(
        new SbyPenyimpanExport([
            'tanggal' => $tanggalTerima,
            'nomor' => '                  /BBPJT/'.$tanggalTerima->format('m').'/'.$tanggalTerima->format('Y'),
            'kepada' => $namaKelompok . ' Perjalanan Dinas',
            'kepada_nama' => $np->nama,
            'kepada_nip' => $np->nik,
            'nominal_angka' => (float) $total,
            'uraian' => 'Belanja Perjalanan Dinas untuk '.$perjalanan->nama_kegiatan.' pada '.$tanggalDinas.' di '.$perjalanan->tujuan_kota,
            'mak' => $perjalanan->kode_mak
        ]),
        $path,
        'local'
    );

    return Storage::disk('local')->path($path);
}

public function exportAllSbyZip($perjadinId)
{
    $perjalanan = PerjalananDinas::with([
        'pegawaiPerjalanan.pegawai',
        'pegawaiPerjalanan.rincian',
        'nonpegawai.rincian'
    ])->findOrFail($perjadinId);

    $zip = new ZipArchive();
    $zipName = 'SBY-'.$perjadinId.'.zip';
    $zipPath = storage_path('app/'.$zipName);

    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {

        // ===== PEGAWAI =====
        foreach ($perjalanan->pegawaiPerjalanan as $pp) {
            $file = $this->generateSbyPegawaiFile($pp);
            if (!file_exists($file)) {
    dd('FILE TIDAK ADA', $file);
}
            $zip->addFile($file, 'Pegawai/'.basename($file));
        }

        // ===== NON PEGAWAI =====
        foreach ($perjalanan->nonpegawai as $np) {
            $file = $this->generateSbyNonPegawaiFile($np);
            $zip->addFile($file, 'NonPegawai/'.basename($file));
        }

        $zip->close();
    }

    return response()->download($zipPath)->deleteFileAfterSend(true);
}

private function generateKuitansiPegawai($pp)
{
    $templateFile = Template::where('jenis', 'kuitansi_spd')->latest()->first();

    $template = new TemplateProcessor(
        storage_path('app/public/' . $templateFile->file_path)
    );

    $perjalanan = $pp->perjalananDinas;
    $pegawai    = $pp->pegawai;

    $tanggal = $perjalanan->tanggal_mulai;
    $tanggalMulai = Carbon::parse($perjalanan->tanggal_mulai);
    $tanggalAkhir = Carbon::parse($perjalanan->tanggal_akhir);

    // lama perjalanan (hari)
    $lamaPerjalanan = $tanggalMulai->diffInDays($tanggalAkhir) + 1;

    $bendahara = PejabatPeriode::getByTanggal('Bendahara Pengeluaran', $tanggal);
    $ppk       = PejabatPeriode::getByTanggal('Pejabat Pembuat Komitmen', $tanggal);

    $rincian = $pp->rincian;
    $sumTotal = $rincian->sum('total');

    // ===============================
    // SET DATA UMUM
    // ===============================
    $data = [
        'tahun_anggaran'   => $perjalanan->tahun_anggaran ?? date('Y'),
        'beban_mak'        => $perjalanan->kode_mak ?? '-',

        'jumlah_rupiah' => 'Rp' . number_format($sumTotal, 0, ',', '.'),
        'terbilang'     => $this->terbilang($sumTotal),
        'keperluan'     => $perjalanan->nama_kegiatan ?? '-',
        'nomor_spd'     => $pp->subKelompok->nomor_st ?? '-',
        'tanggal_spd'   => $pp->subKelompok->tanggal_st
            ? Carbon::parse($pp->tanggal_st)->translatedFormat('d F Y')
            : '-',
        'tujuan'        => $perjalanan->tujuan_kota ?? '-',

        // PERJALANAN
        'nama_kegiatan'   => $perjalanan->nama_kegiatan ?? '-',
        'alat_angkutan'   => $perjalanan->alat_angkutan ?? '-',
        'dari_kota'       => $perjalanan->dari_kota ?? '-',
        'tujuan_kota'     => $perjalanan->tujuan_kota ?? '-',
        'tingkat_perjalanan' => $perjalanan->tingkat_perjalanan ?? '-',

        // TANGGAL
        'tanggal_mulai' => $tanggalMulai->translatedFormat('d F Y'),
        'tanggal_akhir' => $tanggalAkhir->translatedFormat('d F Y'),
        'lama_perjalanan' => $lamaPerjalanan . ' hari',

        // TANGGAL TERIMA 
        'tanggal_terima' => $perjalanan->tanggal_terima
                        ? Carbon::parse($perjalanan->tanggal_terima)->translatedFormat('d F Y')
                        : '-',

        // Penerima
        'nama_penerima' => $pegawai->nama,
        'nip_penerima'  => $pegawai->nip,
        'pangkat_golongan_penerima' => $pegawai->pangkat_golongan ?? '-',
        'jabatan_penerima' => $pegawai->jabatan ?? '-',

        // Bendahara
        'nama_bendahara' => $bendahara?->pegawai?->nama ?? '-',
        'nip_bendahara'  => $bendahara?->pegawai?->nip ?? '-',

        // PPK
        'nama_ppk'       => $ppk?->pegawai?->nama ?? '-',
        'nip_ppk'        => $ppk?->pegawai?->nip ?? '-',

        'sum_total'     => number_format($sumTotal, 0, ',', '.'),
    ];

    foreach ($data as $key => $value) {
        $template->setValue($key, $value ?? '-');
    }

    // ===============================
    // RINCIAN DINAMIS 
    // ===============================
    $template->cloneRow('no', $rincian->count());

    foreach ($rincian as $i => $r) {

        $index = $i + 1;

        $jenis = $r->jenisBiaya->nama_biaya;
        $detail = $r->uraian;

        $uraian = $jenis;

        if (!empty($detail)) {
            $uraian .= " ({$detail})";
        }

        $volume = (int) $r->volume;

        if ($r->volume && $r->tarif) {
            $uraian .= " : {$volume} {$r->satuan} x Rp"
        . number_format($r->tarif, 0, ',', '.');
        }

        $template->setValue("no#{$index}", $index);
        $template->setValue("uraian#{$index}", $uraian);
        $template->setValue("jumlah#{$index}", 'Rp' . number_format($r->total, 0, ',', '.'));
        $template->setValue("keterangan#{$index}", '-');
    }

    $rincianRiil = $rincian->filter(function ($r) {
        $nama = strtolower($r->jenisBiaya->nama_biaya);

        return str_contains($nama, 'taksi');
    });

    if ($rincianRiil->count() == 1) {

        $r = $rincianRiil->first();

        $uraian = $r->uraian
            ? $r->jenisBiaya->nama_biaya . ' (' . $r->uraian . ')'
            : $r->jenisBiaya->nama_biaya;

        // TANPA #1
        $template->setValue('no_riil', 1);
        $template->setValue('uraian_riil', $uraian);
        $template->setValue(
            'jumlah_riil',
            'Rp' . number_format($r->total, 0, ',', '.')
        );

    } elseif ($rincianRiil->isEmpty()) {

        // optional: tetap 1 baris kosong
        $template->setValue('no_riil', '-');
        $template->setValue('uraian_riil', '-');
        $template->setValue('jumlah_riil', '-');

    } else {

        $template->cloneRow('no_riil', $rincianRiil->count());

        foreach ($rincianRiil as $i => $r) {

            $index = $i + 1;

            $uraian = $r->uraian
                ? $r->jenisBiaya->nama_biaya . ' (' . $r->uraian . ')'
                : $r->jenisBiaya->nama_biaya;

            $template->setValue("no_riil#{$index}", $index);
            $template->setValue("uraian_riil#{$index}", $uraian);
            $template->setValue(
                "jumlah_riil#{$index}",
                'Rp' . number_format($r->total, 0, ',', '.')
            );
        }
    }

    $sumTotalRiil = $rincianRiil->sum('total');

    $template->setValue(
        'sum_total_riil',
        number_format($sumTotalRiil, 0, ',', '.')
    );

    // ===== SAVE FILE =====
    $name = Str::slug($pegawai->nama);
    $fileName = 'Kuitansi-'.$name.'.docx';
    $path = 'temp/'.$fileName;

    Storage::makeDirectory('temp');

    $fullPath = Storage::disk('local')->path($path);

    $template->saveAs($fullPath);

    return $fullPath;
}

private function generateKuitansiNonPegawai($np)
{
    $templateFile = Template::where('jenis', 'kuitansi_spd')->latest()->first();

    $template = new TemplateProcessor(
        storage_path('app/public/' . $templateFile->file_path)
    );

    $perjalanan = $np->perjalananDinas;

    $tanggal = $perjalanan->tanggal_mulai;
    $tanggalMulai = Carbon::parse($perjalanan->tanggal_mulai);
    $tanggalAkhir = Carbon::parse($perjalanan->tanggal_akhir);

    // lama perjalanan (hari)
    $lamaPerjalanan = $tanggalMulai->diffInDays($tanggalAkhir) + 1;

    $bendahara = PejabatPeriode::getByTanggal('Bendahara Pengeluaran', $tanggal);
    $ppk       = PejabatPeriode::getByTanggal('Pejabat Pembuat Komitmen', $tanggal);

    $rincian = $np->rincian;
    $sumTotal = $rincian->sum('total');

    // ===============================
    // SET DATA UMUM
    // ===============================
    $data = [
        'tahun_anggaran'   => $perjalanan->tahun_anggaran ?? date('Y'),
        'beban_mak'        => $perjalanan->kode_mak ?? '-',

        'jumlah_rupiah' => 'Rp' . number_format($sumTotal, 0, ',', '.'),
        'terbilang'     => $this->terbilang($sumTotal),
        'keperluan'     => $perjalanan->nama_kegiatan ?? '-',
        'nomor_spd'     => $np->subKelompok->nomor_st ?? '-',
        'tanggal_spd'   => $np->subKelompok->tanggal_st
            ? Carbon::parse($np->tanggal_st)->translatedFormat('d F Y')
            : '-',
        'tujuan'        => $perjalanan->tujuan_kota ?? '-',

        // PERJALANAN
        'nama_kegiatan'   => $perjalanan->nama_kegiatan ?? '-',
        'alat_angkutan'   => $perjalanan->alat_angkutan ?? '-',
        'dari_kota'       => $perjalanan->dari_kota ?? '-',
        'tujuan_kota'     => $perjalanan->tujuan_kota ?? '-',
        'tingkat_perjalanan' => $perjalanan->tingkat_perjalanan ?? '-',

        // TANGGAL
        'tanggal_mulai' => $tanggalMulai->translatedFormat('d F Y'),
        'tanggal_akhir' => $tanggalAkhir->translatedFormat('d F Y'),
        'lama_perjalanan' => $lamaPerjalanan . ' hari',

        // TANGGAL TERIMA 
        'tanggal_terima' => $perjalanan->tanggal_terima
                        ? Carbon::parse($perjalanan->tanggal_terima)->translatedFormat('d F Y')
                        : '-',

        // Penerima
        'nama_penerima' => $np->nama,
        'nip_penerima'  => $np->nik,
        'pangkat_golongan_penerima' => $np->instansi ?? '-',
        'jabatan_penerima' => $np->instansi ?? '-',

        // Bendahara
        'nama_bendahara' => $bendahara?->pegawai?->nama ?? '-',
        'nip_bendahara'  => $bendahara?->pegawai?->nip ?? '-',

        // PPK
        'nama_ppk'       => $ppk?->pegawai?->nama ?? '-',
        'nip_ppk'        => $ppk?->pegawai?->nip ?? '-',

        'sum_total'     => number_format($sumTotal, 0, ',', '.'),
    ];

    foreach ($data as $key => $value) {
        $template->setValue($key, $value ?? '-');
    }

    // ===============================
    // RINCIAN DINAMIS 
    // ===============================
    $template->cloneRow('no', $rincian->count());

    foreach ($rincian as $i => $r) {

        $index = $i + 1;

        $jenis = $r->jenisBiaya->nama_biaya;
        $detail = $r->uraian;

        $uraian = $jenis;

        if (!empty($detail)) {
            $uraian .= " ({$detail})";
        }

        $volume = (int) $r->volume;

        if ($r->volume && $r->tarif) {
            $uraian .= " : {$volume} {$r->satuan} x Rp"
        . number_format($r->tarif, 0, ',', '.');
        }

        $template->setValue("no#{$index}", $index);
        $template->setValue("uraian#{$index}", $uraian);
        $template->setValue("jumlah#{$index}", 'Rp' . number_format($r->total, 0, ',', '.'));
        $template->setValue("keterangan#{$index}", '-');
    }

    $rincianRiil = $rincian->filter(function ($r) {
        $nama = strtolower($r->jenisBiaya->nama_biaya);

        return str_contains($nama, 'taksi');
    });

    if ($rincianRiil->count() == 1) {

        $r = $rincianRiil->first();

        $uraian = $r->uraian
            ? $r->jenisBiaya->nama_biaya . ' (' . $r->uraian . ')'
            : $r->jenisBiaya->nama_biaya;

        // TANPA #1
        $template->setValue('no_riil', 1);
        $template->setValue('uraian_riil', $uraian);
        $template->setValue(
            'jumlah_riil',
            'Rp' . number_format($r->total, 0, ',', '.')
        );

    } elseif ($rincianRiil->isEmpty()) {

        // optional: tetap 1 baris kosong
        $template->setValue('no_riil', '-');
        $template->setValue('uraian_riil', '-');
        $template->setValue('jumlah_riil', '-');

    } else {

        $template->cloneRow('no_riil', $rincianRiil->count());

        foreach ($rincianRiil as $i => $r) {

            $index = $i + 1;

            $uraian = $r->uraian
                ? $r->jenisBiaya->nama_biaya . ' (' . $r->uraian . ')'
                : $r->jenisBiaya->nama_biaya;

            $template->setValue("no_riil#{$index}", $index);
            $template->setValue("uraian_riil#{$index}", $uraian);
            $template->setValue(
                "jumlah_riil#{$index}",
                'Rp' . number_format($r->total, 0, ',', '.')
            );
        }
    }

    $sumTotalRiil = $rincianRiil->sum('total');

    $template->setValue(
        'sum_total_riil',
        number_format($sumTotalRiil, 0, ',', '.')
    );

    $name = Str::slug($np->nama);
    $fileName = 'Kuitansi-'.$name.'.docx';
    $path = 'temp/'.$fileName;

    Storage::makeDirectory('temp');

    $fullPath = Storage::disk('local')->path($path);

    $template->saveAs($fullPath);

    return $fullPath;
}

public function exportAllKuitansiZip($perjadinId)
{
    $perjalanan = PerjalananDinas::with([
        'pegawaiPerjalanan.pegawai',
        'pegawaiPerjalanan.rincian.jenisBiaya',
        'pegawaiPerjalanan.subKelompok',

        'nonpegawai.rincian.jenisBiaya',
        'nonpegawai.subKelompok',
    ])->findOrFail($perjadinId);

    $zip = new \ZipArchive();
    $zipName = 'Kuitansi-'.$perjadinId.'.zip';
    $zipPath = storage_path('app/'.$zipName);

    if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {

        // ===== PEGAWAI =====
        foreach ($perjalanan->pegawaiPerjalanan as $pp) {

            $file = $this->generateKuitansiPegawai($pp);

            if (file_exists($file)) {
                $zip->addFile($file, 'Pegawai/'.basename($file));
            }
        }

        // ===== NON PEGAWAI =====
        foreach ($perjalanan->nonpegawai as $np) {

            $file = $this->generateKuitansiNonPegawai($np);

            if (file_exists($file)) {
                $zip->addFile($file, 'NonPegawai/'.basename($file));
            }
        }

        $zip->close();
    }

    return response()->download($zipPath)->deleteFileAfterSend(true);
}

public function exportAmplop($id)
{
    $pp = PerjalananDinasPegawai::with([
        'pegawai',
        'perjalananDinas.surat',
        'rincian' => function ($q) {
            $q->where('masuk_amplop', true);
        },
        'rincian.jenisBiaya',
        'subKelompok',
    ])->findOrFail($id);

    $perjalanan = $pp->perjalananDinas;
    $pegawai    = $pp->pegawai;

    // HANYA YANG MASUK AMPLOP
    $rincian = $pp->rincian;

    $sumTotal = $rincian->sum('total');

    // ===============================
    // LOAD TEMPLATE
    // ===============================
    $templateFile = Template::where('jenis', 'amplop')
                    ->latest()
                    ->first();

    $template = new TemplateProcessor(
        storage_path('app/public/' . $templateFile->file_path)
    );

    $template->cloneBlock(
        'block_amplop',
        1,
        true,
        true
    );

    // ===============================
    // DATA UMUM
    // ===============================
    $data = [

        'nama' => $this->safeValue($pegawai->nama),

        'nama_kegiatan' => $this->safeValue($perjalanan->nama_kegiatan),

        'sum_total' => number_format(
            $sumTotal,
            0,
            ',',
            '.'
        ),

        'jumlah_rupiah' => 'Rp' . number_format(
            $sumTotal,
            0,
            ',',
            '.'
        ),

        'terbilang' => $this->safeValue(
            $this->terbilang($sumTotal)
        ),
    ];

    foreach ($data as $key => $value) {

        $template->setValue(
            "{$key}#1",
            $value ?? '-'
        );
    }

    // ===============================
    // RINCIAN DINAMIS
    // ===============================
    if ($rincian->isEmpty()) {

        $template->setValue('no', '-');
        $template->setValue('uraian', '-');
        $template->setValue('jumlah', '-');

    } else {

        $template->cloneRow(
            'no#1',
            $rincian->count()
        );

        foreach ($rincian->values() as $i => $r) {

            $index = $i + 1;

            $jenis  = $this->safeValue(
                $r->jenisBiaya->nama_biaya
            );

            $detail = $this->safeValue($r->uraian);

            $uraian = $jenis;

            if (!empty($r->uraian)) {
                $uraian .= " ({$detail})";
            }

            $volume = (int) $r->volume;

            if ($r->volume && $r->tarif) {

                $uraian .= " : {$volume} "
                    . $this->safeValue($r->satuan)
                    . " x Rp"
                    . number_format(
                        $r->tarif,
                        0,
                        ',',
                        '.'
                    );
            }

            $template->setValue(
                "no#1#{$index}",
                $index
            );

            $template->setValue(
                "uraian#1#{$index}",
                $uraian
            );

            $template->setValue(
                "jumlah#1#{$index}",
                'Rp' . number_format(
                    $r->total,
                    0,
                    ',',
                    '.'
                )
            );
        }
    }

    // ===============================
    // GENERATE FILE
    // ===============================
    $fileName = 'Amplop_'
        . str_replace(
            '/',
            '-',
            $pp->subKelompok->nomor_st ?? 'SPD'
        )
        . '_'
        . $pegawai->nama
        . '.docx';

    $savePath = storage_path($fileName);

    $template->saveAs($savePath);

    return response()
        ->download($savePath)
        ->deleteFileAfterSend(true);
}

public function exportAmplopNonPegawai($npId)
{
    $np = NonPegawai::with([
        'rincian' => function ($q) {
            $q->where('masuk_amplop', true);
        },
        'subKelompok',
    ])->findOrFail($npId);

    $perjalanan = $np->perjalananDinas;

    // HANYA YANG MASUK AMPLOP
    $rincian = $np->rincian;

    $sumTotal = $rincian->sum('total');

    // ===============================
    // LOAD TEMPLATE
    // ===============================
    $templateFile = Template::where('jenis', 'amplop')
                    ->latest()
                    ->first();

    $template = new TemplateProcessor(
        storage_path('app/public/' . $templateFile->file_path)
    );

    $template->cloneBlock(
        'block_amplop',
        1,
        true,
        true
    );

    // ===============================
    // DATA UMUM
    // ===============================
    $data = [

        'nama' => $this->safeValue($np->nama),

        'nama_kegiatan' => $this->safeValue($perjalanan->nama_kegiatan),

        'sum_total' => number_format(
            $sumTotal,
            0,
            ',',
            '.'
        ),

        'jumlah_rupiah' => 'Rp' . number_format(
            $sumTotal,
            0,
            ',',
            '.'
        ),

        'terbilang' => $this->safeValue(
            $this->terbilang($sumTotal)
        ),
    ];

    foreach ($data as $key => $value) {

        $template->setValue(
            "{$key}#1",
            $value ?? '-'
        );
    }

    // ===============================
    // RINCIAN DINAMIS
    // ===============================
    if ($rincian->isEmpty()) {

        $template->setValue('no', '-');
        $template->setValue('uraian', '-');
        $template->setValue('jumlah', '-');

    } else {

        $template->cloneRow(
            'no#1',
            $rincian->count()
        );

        foreach ($rincian->values() as $i => $r) {

            $index = $i + 1;

            $jenis  = $this->safeValue(
                $r->jenisBiaya->nama_biaya
            );

            $detail = $this->safeValue($r->uraian);

            $uraian = $jenis;

            if (!empty($r->uraian)) {
                $uraian .= " ({$detail})";
            }

            $volume = (int) $r->volume;

            if ($r->volume && $r->tarif) {

                $uraian .= " : {$volume} "
                    . $this->safeValue($r->satuan)
                    . " x Rp"
                    . number_format(
                        $r->tarif,
                        0,
                        ',',
                        '.'
                    );
            }

            $template->setValue(
                "no#1#{$index}",
                $index
            );

            $template->setValue(
                "uraian#1#{$index}",
                $uraian
            );

            $template->setValue(
                "jumlah#1#{$index}",
                'Rp' . number_format(
                    $r->total,
                    0,
                    ',',
                    '.'
                )
            );
        }
    }

    // ===============================
    // GENERATE FILE
    // ===============================
    $fileName = 'Amplop_'
        . str_replace(
            '/',
            '-',
            $np->subKelompok->nomor_st ?? 'SPD'
        )
        . '_'
        . $np->nama
        . '.docx';

    $savePath = storage_path($fileName);

    $template->saveAs($savePath);

    return response()
        ->download($savePath)
        ->deleteFileAfterSend(true);
}

public function exportAmplopST($subKelompokId)
{
    $subKelompok = SubKelompokPerjalanan::with([

        // PEGAWAI
        'pegawai.pegawai',
        'pegawai.perjalananDinas',
        'pegawai.rincian' => function ($q) {
            $q->where('masuk_amplop', true);
        },
        'pegawai.rincian.jenisBiaya',

        // NON PEGAWAI
        'nonPegawai',
        'nonPegawai.rincian' => function ($q) {
            $q->where('masuk_amplop', true);
        },
        'nonPegawai.rincian.jenisBiaya',

    ])->findOrFail($subKelompokId);

    // ===============================
    // GABUNG SEMUA PESERTA
    // ===============================
    $pesertaList = collect();

    // PEGAWAI
    foreach ($subKelompok->pegawai as $pp) {

        $pesertaList->push([
            'nama' => $pp->pegawai->nama,
            'perjalanan' => $pp->perjalananDinas,
            'rincian' => $pp->rincian,
        ]);
    }

    // NON PEGAWAI
    foreach ($subKelompok->nonPegawai as $np) {

        $pesertaList->push([
            'nama' => $np->nama,
            'perjalanan' => $np->perjalananDinas,
            'rincian' => $np->rincian,
        ]);
    }

    // ===============================
    // LOAD TEMPLATE
    // ===============================
    $templateFile = Template::where('jenis', 'amplop')
                    ->latest()
                    ->first();

    $template = new TemplateProcessor(
        storage_path('app/public/' . $templateFile->file_path)
    );

    // ===============================
    // CLONE BLOCK
    // ===============================
    $template->cloneBlock(
        'block_amplop',
        $pesertaList->count(),
        true,
        true
    );

    foreach ($pesertaList->values() as $pIndex => $item) {

        $index = $pIndex + 1;

        $nama       = $item['nama'];
        $perjalanan = $item['perjalanan'];
        $rincian    = $item['rincian'];

        $sumTotal = $rincian->sum('total');

        // ===============================
        // DATA UMUM
        // ===============================
        $template->setValue(
            "nama#{$index}",
            $this->safeValue($nama)
        );

        $template->setValue(
            "nama_kegiatan#{$index}",
            $this->safeValue($perjalanan->nama_kegiatan)
        );

        $template->setValue(
            "jumlah_rupiah#{$index}",
            'Rp' . number_format($sumTotal, 0, ',', '.')
        );

        $template->setValue(
            "sum_total#{$index}",
            number_format($sumTotal, 0, ',', '.')
        );

        $template->setValue(
            "terbilang#{$index}",
            $this->safeValue(
                $this->terbilang($sumTotal)
            )
        );

        // ===============================
        // RINCIAN
        // ===============================
        if ($rincian->isEmpty()) {

            $template->setValue("no#{$index}", '-');
            $template->setValue("uraian#{$index}", '-');
            $template->setValue("jumlah#{$index}", '-');

        } else {

            $template->cloneRow(
                "no#{$index}",
                $rincian->count()
            );

            foreach ($rincian->values() as $rIndex => $r) {

                $row = $rIndex + 1;

                $jenis = $this->safeValue(
                    $r->jenisBiaya->nama_biaya
                );

                $detail = $this->safeValue(
                    $r->uraian
                );

                $uraian = $jenis;

                if (!empty($r->uraian)) {
                    $uraian .= " ({$detail})";
                }

                if ($r->volume && $r->tarif) {

                    $uraian .= " : "
                        . (int) $r->volume
                        . " "
                        . $this->safeValue($r->satuan)
                        . " x Rp"
                        . number_format(
                            $r->tarif,
                            0,
                            ',',
                            '.'
                        );
                }

                $template->setValue(
                    "no#{$index}#{$row}",
                    $row
                );

                $template->setValue(
                    "uraian#{$index}#{$row}",
                    $uraian
                );

                $template->setValue(
                    "jumlah#{$index}#{$row}",
                    'Rp' . number_format(
                        $r->total,
                        0,
                        ',',
                        '.'
                    )
                );
            }
        }
    }

    // ===============================
    // GENERATE FILE
    // ===============================
    $fileName = 'Amplop_ST_'
        . str_replace(
            '/',
            '-',
            $subKelompok->nomor_st ?? 'SPD'
        )
        . '.docx';

    $savePath = storage_path($fileName);

    $template->saveAs($savePath);

    return response()
        ->download($savePath)
        ->deleteFileAfterSend(true);
}

private function convertWordToPdf($wordPath, $pdfPath)
{
    // Gunakan LibreOffice (harus terinstall di server)
    $command = sprintf(
        'libreoffice --headless --convert-to pdf --outdir "%s" "%s" 2>&1',
        dirname($pdfPath),
        $wordPath
    );
    exec($command, $output, $returnCode);
    
    // File hasil convert ada di folder yang sama dengan nama yang sama
    $convertedFile = dirname($pdfPath) . '/' . pathinfo($wordPath, PATHINFO_FILENAME) . '.pdf';
    if (file_exists($convertedFile)) {
        rename($convertedFile, $pdfPath);
    }
    
    return $returnCode === 0;
}

private function mergePdfFiles($pdfFiles, $outputPath)
{
    // Menggunakan FPDI
    $pdf = new Fpdi();
    
    foreach ($pdfFiles as $file) {
        $pageCount = $pdf->setSourceFile($file);
        for ($i = 1; $i <= $pageCount; $i++) {
            $template = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($template);
            $pdf->AddPage($size['orientation'] ?? 'P', [$size['width'], $size['height']]);
            $pdf->useTemplate($template);
        }
    }
    
    $pdf->Output($outputPath, 'F');
}
}
