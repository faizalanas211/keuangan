<?php

namespace App\Http\Controllers;

use App\Exports\NominatifPerjalananExport;
use App\Exports\SbyPenyimpanExport;
use App\Models\JenisBiaya;
use App\Models\Pegawai;
use App\Models\PerjalananDinas;
use App\Models\PerjalananDinasPegawai;
use App\Models\RincianBiaya;
use App\Models\SuratPerjalanan;
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
        $request->validate([
            'tingkat_perjalanan' => 'nullable',
            'alat_angkutan' => 'required',
            'dari_kota' => 'required',
            'tujuan_kota' => 'required',
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
            'tanggal_terima' => 'required|date',
            'pegawai' => 'required|array|min:1',
            'rincian' => 'required|array',
            'nomor_st' => 'required',
            'tanggal_st' => 'required|date',
        ]);

        DB::beginTransaction();

        try {

            // Simpan Perjalanan Dinas
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

            // Loop Pegawai
            foreach ($request->pegawai as $pegawaiId) {

                $pp = PerjalananDinasPegawai::create([
                    'perjalanan_dinas_id' => $perjalanan->id,
                    'pegawai_id'          => $pegawaiId,
                ]);

                // Loop Rincian Biaya per Pegawai
                if (isset($request->rincian[$pegawaiId])) {

                    foreach ($request->rincian[$pegawaiId] as $rincian) {

                        $volume = $rincian['volume'] ?? 0;
                        $tarif  = $rincian['tarif'] ?? 0;

                        RincianBiaya::create([
                            'perjalanan_dinas_pegawai_id' => $pp->id,
                            'jenis_biaya_id' => $rincian['jenis_biaya_id'],
                            'uraian' => $rincian['uraian'] ?? null,
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
                    'perjalananDinas',
                    'rincian.jenisBiaya'
                ])->findOrFail($id);

        $perjalanan = $pp->perjalananDinas;
        $pegawai    = $pp->pegawai;

        // ===============================
        // FILTER RINCIAN
        // ===============================

        $transportRincian = $pp->rincian->first(function ($r) {
            return str_contains(strtolower($r->jenisBiaya->nama_biaya), 'transport');
        });

        $harianRincian = $pp->rincian->first(function ($r) {
            return str_contains(strtolower($r->jenisBiaya->nama_biaya), 'harian perjalanan dinas');
        });

        $transport = $transportRincian->total ?? 0;
        $harian    = $harianRincian->total ?? 0;

        $sumTotal  = $pp->rincian->sum('total');

        // ===============================
        // LOAD TEMPLATE
        // ===============================

        $template = new TemplateProcessor(
            storage_path('app/templates/template_kuitansi.docx')
        );

        // ===============================
        // SET DATA KE TEMPLATE
        // ===============================

        $data = [

            // HEADER
            'tahun_anggaran' => $perjalanan->tahun_anggaran ?? date('Y'),
            'beban_mak'      => $perjalanan->kode_mak ?? '-',

            // TANGGAL HARI INI
            'tanggal_hari_ini' => Carbon::now()->translatedFormat('d F Y'),

            // KUITANSI
            'jumlah_rupiah'  => 'Rp' . number_format($sumTotal, 0, ',', '.'),
            'terbilang'      => $this->terbilang($sumTotal),
            'keperluan'      => $perjalanan->nama_kegiatan ?? '-',
            'nomor_spd'      => $perjalanan->surat->nomor_st ?? '-',
            'tanggal_spd'    => $perjalanan->surat->tanggal_st
                                ? Carbon::parse($perjalanan->surat->tanggal_st)
                                    ->translatedFormat('d F Y')
                                : '-',
            'tujuan'         => $perjalanan->tujuan_kota ?? '-',

            // PEGAWAI
            'nama_penerima'  => $pegawai->nama,
            'nip_penerima'   => $pegawai->nip,

            // RINCIAN TRANSPORT
            'asal'               => $perjalanan->dari_kota ?? '-',
            'volume_transport'   => $transportRincian->volume ?? 1,
            'satuan_transport'   => $transportRincian->satuan ?? 'kl',
            'tarif_transport'    => number_format($transportRincian->tarif ?? 0, 0, ',', '.'),
            'total_transport'    => number_format($transport, 0, ',', '.'),

            // RINCIAN HARIAN
            'volume_harian'      => $harianRincian->volume ?? 1,
            'satuan_harian'      => $harianRincian->satuan ?? 'hr',
            'tarif_harian'       => number_format($harianRincian->tarif ?? 0, 0, ',', '.'),
            'total_harian'       => number_format($harian, 0, ',', '.'),

            // TOTAL
            'sum_total'          => number_format($sumTotal, 0, ',', '.'),
        ];

        foreach ($data as $key => $value) {
            $template->setValue($key, $value ?? '-');
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
