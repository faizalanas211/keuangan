<?php

namespace App\Http\Controllers;

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
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PerjadinController extends Controller
{
    public function index()
    {
        $perjalanans = PerjalananDinas::with('pegawai')
                        ->latest()
                        ->paginate(10);

        return view('dashboard.perjadin.index', compact('perjalanans'));
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

        // SuratPerjalanan::create([
        //     'perjalanan_dinas_id' => $perjalanan->id,
        //     'nomor_sk'   => $request->nomor_sk,
        //     'nomor_st' => $request->kelompok[$kelompok]['nomor_st'] ?? null,
        //     'tanggal_st' => $request->kelompok[$kelompok]['tanggal_st'] ?? null,
        // ]);

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
        'kelompokPerjalanan.pegawai.pegawai',
        'kelompokPerjalanan.pegawai.rincian.jenisBiaya',
        'kelompokPerjalanan.nonpegawai.rincian.jenisBiaya'
    ])->findOrFail($id);

    $pegawai = Pegawai::all();
    $jenisBiaya = JenisBiaya::all();

    // ==================== PERBAIKAN: FORMAT DATA SEBAGAI OBJECT DENGAN KEY ====================
    $kelompokData = [];
    
    foreach ($perjalanan->kelompokPerjalanan as $k) {
        $jenisKelompok = $k->jenis ?? strtolower($k->nama_kelompok);
        
        $kelompokData[$jenisKelompok] = [
            'id' => $k->id,
            'jenis' => $jenisKelompok,
            'nomor_st' => $k->nomor_st,
            'tanggal_st' => $k->tanggal_st,
            'pegawai' => [],
            'nonpegawai' => []
        ];
        
        // PEGAWAI
        foreach ($k->pegawai as $pp) {
            $rincianData = [];
            foreach ($pp->rincian as $r) {
                $rincianData[] = [
                    'id' => $r->id,
                    'jenis_biaya_id' => $r->jenis_biaya_id,
                    'uraian' => $r->uraian,
                    'volume' => $r->volume,
                    'satuan' => $r->satuan,
                    'tarif' => $r->tarif,
                    'total' => $r->total,
                ];
            }
            
            $kelompokData[$jenisKelompok]['pegawai'][] = [
                'id' => $pp->id,
                'pegawai_id' => $pp->pegawai_id,
                'peserta_id' => $jenisKelompok . '_pegawai_' . $pp->pegawai_id . '_' . $pp->id,
                'nama' => $pp->pegawai->nama,
                'nip' => $pp->pegawai->nip,
                'jabatan' => $pp->pegawai->jabatan,
                'perjalanan_dinas_pegawai_id' => $pp->id,
                'rincian' => $rincianData
            ];
        }
        
        // NON PEGAWAI
        foreach ($k->nonpegawai as $np) {
            $rincianData = [];
            foreach ($np->rincian as $r) {
                $rincianData[] = [
                    'id' => $r->id,
                    'jenis_biaya_id' => $r->jenis_biaya_id,
                    'uraian' => $r->uraian,
                    'volume' => $r->volume,
                    'satuan' => $r->satuan,
                    'tarif' => $r->tarif,
                    'total' => $r->total,
                ];
            }
            
            $kelompokData[$jenisKelompok]['nonpegawai'][] = [
                'id' => $np->id,
                'nonpegawai_id' => $np->id,
                'peserta_id' => $jenisKelompok . '_nonpegawai_' . $np->id,
                'nama' => $np->nama,
                'nik' => $np->nik,
                'instansi' => $np->instansi,
                'rincian' => $rincianData
            ];
        }
    }

    // Pastikan semua kelompok ada (untuk kelompok yang tidak punya data)
    foreach (['panitia', 'peserta', 'narasumber'] as $jenis) {
        if (!isset($kelompokData[$jenis])) {
            $kelompokData[$jenis] = [
                'nomor_st' => null,
                'tanggal_st' => null,
                'pegawai' => [],
                'nonpegawai' => []
            ];
        }
    }

    // Debug: cek format data (hapus setelah yakin)
    // dd($kelompokData);

    return view('dashboard.perjadin.edit', compact(
        'perjalanan',
        'pegawai',
        'jenisBiaya',
        'kelompokData'
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

        // hapus kelompok
        KelompokPerjalanan::where('perjalanan_dinas_id', $id)->delete();

        // ===============================
        // RE-INSERT (SAMA KYK STORE)
        // ===============================
        foreach ($request->peserta as $kelompok => $listPeserta) {

            $kelompokModel = KelompokPerjalanan::create([
                'perjalanan_dinas_id' => $perjalanan->id,
                'nama_kelompok' => $kelompok,
                'nomor_st' => $request->kelompok[$kelompok]['nomor_st'] ?? null,
                'tanggal_st' => $request->kelompok[$kelompok]['tanggal_st'] ?? null,
            ]);

            foreach ($listPeserta as $pesertaId => $peserta) {

                $tipe = isset($peserta['pegawai_id']) ? 'pegawai' : 'nonpegawai';

                // ================= PEGAWAI =================
                if ($tipe === 'pegawai') {

                    if (empty($peserta['pegawai_id'])) continue;

                    $pp = PerjalananDinasPegawai::create([
                        'perjalanan_dinas_id' => $perjalanan->id,
                        'pegawai_id' => $peserta['pegawai_id'],
                        'kelompok_id' => $kelompokModel->id,
                    ]);

                    foreach ($request->rincian[$kelompok][$pesertaId] ?? [] as $r) {

                        if (empty($r['jenis_biaya_id'])) continue;

                        $volume = $r['volume'] ?? 0;
                        $tarif  = $r['tarif'] ?? 0;

                        RincianBiaya::create([
                            'perjalanan_dinas_pegawai_id' => $pp->id,
                            'nonpegawai_id' => null,
                            'jenis_biaya_id' => $r['jenis_biaya_id'],
                            'uraian' => $r['uraian'] ?? null,
                            'volume' => $volume,
                            'satuan' => $r['satuan'] ?? '-',
                            'tarif'  => $tarif,
                            'total'  => $volume * $tarif,
                        ]);
                    }
                }

                // ================= NON PEGAWAI =================
                if ($tipe === 'nonpegawai') {

                    if (empty($peserta['nama'])) continue;

                    $np = NonPegawai::create([
                        'perjalanan_dinas_id' => $perjalanan->id,
                        'kelompok_id' => $kelompokModel->id,
                        'nama' => $peserta['nama'],
                        'nik'  => $peserta['nik'] ?? null,
                        'instansi' => $peserta['instansi'] ?? null,
                    ]);

                    foreach ($request->rincian[$kelompok][$pesertaId] ?? [] as $r) {

                        if (empty($r['jenis_biaya_id'])) continue;

                        $volume = $r['volume'] ?? 0;
                        $tarif  = $r['tarif'] ?? 0;

                        RincianBiaya::create([
                            'perjalanan_dinas_pegawai_id' => null,
                            'nonpegawai_id' => $np->id,
                            'jenis_biaya_id' => $r['jenis_biaya_id'],
                            'uraian' => $r['uraian'] ?? null,
                            'volume' => $volume,
                            'satuan' => $r['satuan'] ?? '-',
                            'tarif'  => $tarif,
                            'total'  => $volume * $tarif,
                        ]);
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

    public function exportNominatif($id, $kelompokId)
{
    $perjalanan = PerjalananDinas::with([
        'kelompokPerjalanan.pegawai.rincian.jenisBiaya',
        'kelompokPerjalanan.nonpegawai.rincian.jenisBiaya'
    ])->findOrFail($id);

    // ambil kelompok yg dipilih
    $kelompok = $perjalanan->kelompokPerjalanan
        ->where('id', $kelompokId)
        ->firstOrFail();

    return Excel::download(
        new NominatifPerjalananExport($perjalanan, $kelompok),
        'Nominatif_'.$kelompok->nama_kelompok.'.xlsx'
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

    public function exportKuitansi($id)
    {
        $pp = PerjalananDinasPegawai::with([
            'pegawai',
            'perjalananDinas.surat',
            'rincian.jenisBiaya'
        ])->findOrFail($id);

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
        // LOAD TEMPLATE
        // ===============================
        $templateFile = Template::where('jenis', 'kuitansi_spd')
                        ->latest()
                        ->first();

        $template = new TemplateProcessor(
                        storage_path('app/public/' . $templateFile->file_path)
                    );

        // ===============================
        // SET DATA UMUM
        // ===============================
        $data = [
            'tahun_anggaran'   => $perjalanan->tahun_anggaran ?? date('Y'),
            'beban_mak'        => $perjalanan->kode_mak ?? '-',
            'tanggal_terima'=> $perjalanan->surat->tanggal_terima,

            'jumlah_rupiah' => 'Rp' . number_format($sumTotal, 0, ',', '.'),
            'terbilang'     => $this->terbilang($sumTotal),
            'keperluan'     => $perjalanan->nama_kegiatan ?? '-',
            'nomor_spd'     => $perjalanan->surat->nomor_st ?? '-',
            'tanggal_spd'   => $perjalanan->surat->tanggal_st
                ? Carbon::parse($perjalanan->surat->tanggal_st)->translatedFormat('d F Y')
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

            $uraian = $r->uraian ?: $r->jenisBiaya->nama_biaya;

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

        // ===============================
        // GENERATE FILE
        // ===============================
        $fileName = 'Kuitansi_' . str_replace('/', '-', $perjalanan->surat->nomor_st ?? 'SPD') 
                    . '_' . $pegawai->nama . '.docx';

        $savePath = storage_path($fileName);

        $template->saveAs($savePath);

        return response()->download($savePath)->deleteFileAfterSend(true);
    }

    public function exportKuitansiNonPegawai($npId)
    {
        $np = NonPegawai::with([
            'perjalananDinas.surat',
            'rincian.jenisBiaya'
        ])->findOrFail($npId);

        $perjalanan = $np->perjalananDinas;

        $tanggalMulai = Carbon::parse($perjalanan->tanggal_mulai);
        $tanggalAkhir = Carbon::parse($perjalanan->tanggal_akhir);

        // lama perjalanan
        $lamaPerjalanan = $tanggalMulai->diffInDays($tanggalAkhir) + 1;

        $bendahara = PejabatPeriode::getByTanggal('Bendahara Pengeluaran', $tanggalMulai);
        $ppk       = PejabatPeriode::getByTanggal('Pejabat Pembuat Komitmen', $tanggalMulai);

        $rincian = $np->rincian;
        $sumTotal = $rincian->sum('total');

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
        // DATA (🔥 placeholder SAMA)
        // ===============================
        $data = [
            'tahun_anggaran'   => $perjalanan->tahun_anggaran ?? date('Y'),
            'beban_mak'        => $perjalanan->kode_mak ?? '-',

            'jumlah_rupiah' => 'Rp' . number_format($sumTotal, 0, ',', '.'),
            'terbilang'     => $this->terbilang($sumTotal),

            'keperluan'     => $perjalanan->nama_kegiatan ?? '-',
            'nomor_spd'     => $perjalanan->surat->nomor_st ?? '-',
            'tanggal_spd'   => $perjalanan->surat->tanggal_st
                ? Carbon::parse($perjalanan->surat->tanggal_st)->translatedFormat('d F Y')
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

            'tanggal_terima' => $perjalanan->tanggal_terima
                ? Carbon::parse($perjalanan->tanggal_terima)->translatedFormat('d F Y')
                : '-',

            // 🔥 PENERIMA (NON PEGAWAI tapi placeholder sama)
            'nama_penerima' => $np->nama,
            'nip_penerima'  => $np->nik ?? '-',
            'pangkat_golongan_penerima' => '-', // ga ada di nonpegawai
            'jabatan_penerima' => $np->instansi ?? '-',

            // Bendahara
            'nama_bendahara' => $bendahara?->pegawai?->nama ?? '-',
            'nip_bendahara'  => $bendahara?->pegawai?->nip ?? '-',

            // PPK
            'nama_ppk'       => $ppk?->pegawai?->nama ?? '-',
            'nip_ppk'        => $ppk?->pegawai?->nip ?? '-',

            'sum_total' => number_format($sumTotal, 0, ',', '.'),
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

            $uraian = $r->uraian ?: $r->jenisBiaya->nama_biaya;

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

        // ===============================
        // FILE
        // ===============================
        $fileName = 'Kuitansi_NonPegawai_' 
            . str_replace('/', '-', $perjalanan->surat->nomor_st ?? 'SPD') 
            . '_' . $np->nama . '.docx';

        $savePath = storage_path($fileName);

        $template->saveAs($savePath);

        return response()->download($savePath)->deleteFileAfterSend(true);
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
}
