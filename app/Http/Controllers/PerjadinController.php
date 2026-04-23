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
    // NonPegawai::Create 
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
        foreach ($request->peserta as $kelompok => $listPeserta) {

            // ===============================
            // SIMPAN KELOMPOK 
            // ===============================
            $kelompokModel = KelompokPerjalanan::create([
                'perjalanan_dinas_id' => $perjalanan->id,
                'nama_kelompok' => ucfirst($kelompok),
                'nomor_st' => $request->kelompok[$kelompok]['nomor_st'] ?? null,
                'tanggal_st' => $request->kelompok[$kelompok]['tanggal_st'] ?? null,
            ]);

            // ===============================
            // LOOP PESERTA DI DALAM KELOMPOK
            // ===============================
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

                    if (isset($request->rincian[$kelompok][$pesertaId])) {
                        foreach ($request->rincian[$kelompok][$pesertaId] as $r) {

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
                }

                // ================= NON PEGAWAI =================
                if ($tipe === 'nonpegawai') {

                    if (empty($peserta['nama'])) continue;

                    $np = NonPegawai::create([
                        'perjalanan_dinas_id' => $perjalanan->id,
                        'nama' => $peserta['nama'],
                        'nik'  => $peserta['nik'] ?? null,
                        'instansi' => $peserta['instansi'] ?? null,
                        'kelompok_id' => $kelompokModel->id,
                    ]);

                    if (isset($request->rincian[$kelompok][$pesertaId])) {
                        foreach ($request->rincian[$kelompok][$pesertaId] as $r) {

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
            'surat',
            'pegawaiPerjalanan.pegawai',
            'pegawaiPerjalanan.rincian.jenisBiaya'
        ])->findOrFail($id);

        $grandTotalPerjalanan = 0;

        // =======================
        // TOTAL PEGAWAI
        // =======================

        foreach ($perjalanan->pegawaiPerjalanan as $pp) {
            $grandTotalPerjalanan += $pp->rincian->sum('total');
        }

        // =======================
        // TOTAL NON PEGAWAI
        // =======================
        foreach ($perjalanan->nonpegawai as $np) {
            foreach ($np->rincian as $r) {
                $grandTotalPerjalanan += $r->total;
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
            'pegawaiPerjalanan.pegawai',
            'pegawaiPerjalanan.rincian.jenisBiaya'
        ])->findOrFail($id);

        $pegawai = Pegawai::all();
        $jenisBiaya = JenisBiaya::all();

        $existingPegawai = $perjalanan->pegawaiPerjalanan->map(function($pp) {
            return [
                'id' => $pp->pegawai_id,
                'tipe' => 'pegawai',
                'peserta_id' => 'pegawai_' . $pp->pegawai_id,
                'nama' => $pp->pegawai->nama . ' - ' . $pp->pegawai->jabatan . ' (' . $pp->pegawai->nip . ')',
                'rincian' => $pp->rincian->map(function($r) {
                    return [
                        'jenis_biaya_id' => $r->jenis_biaya_id,
                        'uraian' => $r->uraian,
                        'volume' => $r->volume,
                        'satuan' => $r->satuan,
                        'tarif' => $r->tarif,
                        'total' => $r->total,
                    ];
                })->toArray()
            ];
        });

        $existingNonPegawai = $perjalanan->nonpegawai->map(function($np) {
            return [
                'id' => $np->id,
                'tipe' => 'nonpegawai',
                'peserta_id' => 'nonpegawai_' . $np->id,
                'nama' => $np->nama,
                'nik' => $np->nik,
                'instansi' => $np->instansi,
                'rincian' => $np->rincian->map(function($r) {
                    return [
                        'jenis_biaya_id' => $r->jenis_biaya_id,
                        'uraian' => $r->uraian,
                        'volume' => $r->volume,
                        'satuan' => $r->satuan,
                        'tarif' => $r->tarif,
                        'total' => $r->total,
                    ];
                })->toArray()
            ];
        });

        return view('dashboard.perjadin.edit', compact(
            'perjalanan',
            'pegawai',
            'jenisBiaya',
            'existingPegawai',
            'existingNonPegawai'
        ));
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $perjalanan = PerjalananDinas::findOrFail($id);

            // UPDATE perjalanan
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

            // UPDATE / CREATE surat
            SuratPerjalanan::updateOrCreate(
                ['perjalanan_dinas_id' => $perjalanan->id],
                [
                    'nomor_sk'   => $request->nomor_sk ?? $perjalanan->surat->nomor_sk,
                    'nomor_st'   => $request->nomor_st ?? $perjalanan->surat->nomor_st,
                    'tanggal_st' => $request->tanggal_st ?? $perjalanan->surat->tanggal_st,
                ]
            );

            // ===============================
            // HAPUS DATA LAMA
            // ===============================
            foreach ($perjalanan->pegawaiPerjalanan as $pp) {
                $pp->rincian()->delete();
                $pp->delete();
            }

            foreach ($perjalanan->nonpegawai as $np) {
                $np->rincian()->delete();
                $np->delete();
            }


            // ===============================
            // INSERT ULANG (🔥 FIXED)
            // ===============================
            foreach ($request->peserta as $pesertaKey => $peserta) {
                
                $tipe = explode('_', $pesertaKey)[0];

                // ===============================
                // PEGAWAI
                // ===============================
                if ($tipe === 'pegawai') {

                    if (empty($peserta['pegawai_id'])) continue;

                    $pp = PerjalananDinasPegawai::create([
                        'perjalanan_dinas_id' => $perjalanan->id,
                        'pegawai_id' => $peserta['pegawai_id'],
                    ]);

                    if (isset($request->rincian[$pesertaKey])) {
                        foreach ($request->rincian[$pesertaKey] as $r) {

                            if (
                                empty($r['jenis_biaya_id']) &&
                                empty($r['uraian']) &&
                                empty($r['volume']) &&
                                empty($r['tarif'])
                            ) continue;

                            $volume = (int) ($r['volume'] ?? 0);
                            $tarif  = (int) ($r['tarif'] ?? 0);

                            RincianBiaya::create([
                                'perjalanan_dinas_pegawai_id' => $pp->id,
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


                // ===============================
                // NON PEGAWAI
                // ===============================
                if ($tipe === 'nonpegawai') {

                    if (empty($peserta['nama'])) continue;

                    $np = NonPegawai::create([
                        'perjalanan_dinas_id' => $perjalanan->id,
                        'nama' => $peserta['nama'],
                        'nik'  => $peserta['nik'] ?? null,
                        'instansi' => $peserta['instansi'] ?? null,
                    ]);

                    if (isset($request->rincian[$pesertaKey])) {
                        foreach ($request->rincian[$pesertaKey] as $r) {

                            if (
                                empty($r['jenis_biaya_id']) &&
                                empty($r['uraian']) &&
                                empty($r['volume']) &&
                                empty($r['tarif'])
                            ) continue;

                            $volume = (int) ($r['volume'] ?? 0);
                            $tarif  = (int) ($r['tarif'] ?? 0);

                            RincianBiaya::create([
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
                ->with('success','Data berhasil diperbarui');

        } catch (\Exception $e) {

            DB::rollBack();
            return back()->with('error',$e->getMessage());
        }
    }

    public function exportNominatif($id)
    {
        $perjalanan = PerjalananDinas::with([
            'pegawaiPerjalanan.pegawai',
            'pegawaiPerjalanan.rincian'
        ])->findOrFail($id);

        return Excel::download(
            new NominatifPerjalananExport($perjalanan),
            'Nominatif_Perjalanan.xlsx'
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
