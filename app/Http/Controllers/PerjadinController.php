<?php

namespace App\Http\Controllers;

use App\Exports\NominatifPerjalananExport;
use App\Exports\SbyPenyimpanExport;
use App\Models\JenisBiaya;
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
        'nomor_st' => 'required',
        'tanggal_st' => 'required|date',
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

        SuratPerjalanan::create([
            'perjalanan_dinas_id' => $perjalanan->id,
            'nomor_sk'   => $request->nomor_sk,
            'nomor_st'   => $request->nomor_st,
            'tanggal_st' => $request->tanggal_st,
        ]);

        // ===============================
        // LOOP PESERTA (🔥 INI YG BARU)
        // ===============================
        foreach ($request->peserta as $pesertaKey => $peserta) {

            $tipe = explode('_', $pesertaKey)[0];

            // ======================
            // PEGAWAI
            // ======================
            if ($tipe === 'pegawai') {

                if (empty($peserta['pegawai_id'])) continue;

                $pp = PerjalananDinasPegawai::create([
                    'perjalanan_dinas_id' => $perjalanan->id,
                    'pegawai_id' => $peserta['pegawai_id'],
                ]);

                if (isset($request->rincian[$pesertaKey])) {
                    foreach ($request->rincian[$pesertaKey] as $r) {

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

            // ======================
            // NON PEGAWAI
            // ======================
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

        foreach ($perjalanan->pegawaiPerjalanan as $pp) {
            $grandTotalPerjalanan += $pp->rincian->sum('total');
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

        return view('dashboard.perjadin.edit', compact(
            'perjalanan',
            'pegawai',
            'jenisBiaya'
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

            // Hapus pivot lama + rincian lama
            foreach ($perjalanan->pegawaiPerjalanan as $pp) {
                $pp->rincian()->delete();
                $pp->delete();
            }

            // Insert ulang
            foreach ($request->pegawai as $pegawaiId) {

                $pp = PerjalananDinasPegawai::create([
                    'perjalanan_dinas_id' => $perjalanan->id,
                    'pegawai_id'          => $pegawaiId,
                ]);

                if (!empty($request->rincian[$pegawaiId])) {

                    foreach ($request->rincian[$pegawaiId] as $rincian) {

                        // skip kalau kosong semua
                        if (
                            empty($rincian['jenis_biaya_id']) &&
                            empty($rincian['uraian']) &&
                            empty($rincian['volume']) &&
                            empty($rincian['tarif'])
                        ) {
                            continue;
                        }

                        $volume = $rincian['volume'] ?? 0;
                        $tarif  = $rincian['tarif'] ?? 0;

                        RincianBiaya::create([
                            'perjalanan_dinas_pegawai_id' => $pp->id,
                            'jenis_biaya_id' => $rincian['jenis_biaya_id'],
                            'uraian' => $rincian['uraian'] ?? null, // ✅ tambahan
                            'volume' => $volume ?: 0,
                            'satuan' => $rincian['satuan'] ?? '-',
                            'tarif'  => $tarif ?: 0,
                            'total'  => ($volume ?: 0) * ($tarif ?: 0),
                        ]);
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
        $tanggal = Carbon::parse($perjalanan->tanggal_terima);

        // total hanya untuk pegawai ini
        $total = $pp->rincian->sum('total');

        return Excel::download(
            new SbyPenyimpanExport([
                'tanggal' => $tanggal,
                'nomor' => '                  /BBPJT/'.$tanggal->format('m').'/'.$tanggal->format('Y'),

                'kepada' => $pp->pegawai->nama,
                'kepada_nip' => $pp->pegawai->nip,

                'nominal_angka' => (float) $total,

                'uraian' => 'Belanja Perjalanan Dinas untuk melaksanakan kegiatan '
                            .$perjalanan->nama_kegiatan.
                            ' pada '.$tanggal->translatedFormat('d F Y').
                            ' bertempat di '.$perjalanan->tujuan_kota,

                'mak' => 'WA.7613.EBA.962.054.A.524111'
            ]),
            'SBY-Penyimpan-'.$pp->id.'.xlsx'
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
}
