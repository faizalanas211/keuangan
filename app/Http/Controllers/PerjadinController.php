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
        DB::beginTransaction();

        try {

            // 1️⃣ Simpan Perjalanan Dinas
            $perjalanan = PerjalananDinas::create([
                'tingkat_perjalanan' => $request->tingkat_perjalanan,
                'alat_angkutan'      => $request->alat_angkutan,
                'dari_kota'          => $request->dari_kota,
                'tujuan_kota'        => $request->tujuan_kota,
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

            // 2️⃣ Loop Pegawai
            foreach ($request->pegawai as $pegawaiId) {

                $pp = PerjalananDinasPegawai::create([
                    'perjalanan_dinas_id' => $perjalanan->id,
                    'pegawai_id'          => $pegawaiId,
                ]);

                // 3️⃣ Loop Rincian Biaya per Pegawai
                if (isset($request->rincian[$pegawaiId])) {

                    foreach ($request->rincian[$pegawaiId] as $jenisId => $rincian) {

                        $volume = $rincian['volume'] ?? 0;
                        $tarif  = $rincian['tarif'] ?? 0;

                        RincianBiaya::create([
                            'perjalanan_dinas_pegawai_id' => $pp->id,
                            'jenis_biaya_id' => $jenisId,
                            'uraian' => null,
                            'volume' => $volume ?: 0,
                            'satuan' => $rincian['satuan'] ?: '-',
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
                    'nomor_sk'   => $request->nomor_sk,
                    'nomor_st'   => $request->nomor_st,
                    'tanggal_st' => $request->tanggal_st,
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

                foreach ($request->rincian[$pegawaiId] as $jenisId => $rincian) {

                    $volume = $rincian['volume'] ?? 0;
                    $tarif  = $rincian['tarif'] ?? 0;

                    RincianBiaya::create([
                        'perjalanan_dinas_pegawai_id' => $pp->id,
                        'jenis_biaya_id' => $jenisId,
                        'volume' => $volume ?: 0,
                        'satuan' => $rincian['satuan'] ?: '-',
                        'tarif'  => $tarif ?: 0,
                        'total'  => ($volume ?: 0) * ($tarif ?: 0),
                    ]);
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

    public function exportSbyPenyimpan($id)
{
    $perjalanan = PerjalananDinas::findOrFail($id);

    $tanggal = Carbon::parse($perjalanan->tanggal_mulai);

    $grandTotalPerjalanan = 0;

    foreach ($perjalanan->pegawaiPerjalanan as $pp) {
        $grandTotalPerjalanan += $pp->rincian->sum('total');
    }

    return Excel::download(
        new SbyPenyimpanExport([
            'tanggal' => $tanggal,
            'nomor' => '                  /BBPJT/'.$tanggal->format('m').'/'.$tanggal->format('Y'),
            'nominal_angka' => (float) $grandTotalPerjalanan,
            'kepada' => 'Pegawai BBPJT',
            'uraian' => 'Belanja Perjalanan Dinas untuk melaksanakan kegiatan '
                        .$perjalanan->nama_kegiatan.
                        ' pada '.$tanggal->translatedFormat('d F Y').
                        ' bertempat di '.$perjalanan->tujuan_kota,
            'mak' => 'WA.7613.EBA.962.054.A.524111'
        ]),
        'SBY-Penyimpan-'.$perjalanan->id.'.xlsx'
    );
}
}
