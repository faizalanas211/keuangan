<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Penghasilan;
use App\Models\Potongan;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Writer\HTML as HtmlWriter;

class SlipGajiController extends Controller
{
    /**
     * ==================================================
     * INDEX (ADMIN & PEGAWAI)
     * ==================================================
     */
    public function index(Request $request)
    {
        $user     = Auth::user();
        $results  = [];
        $pegawais = collect();

        if (strtolower($user->role) === 'admin') {

            $pegawais = Pegawai::orderBy('nama')->get();

            if ($request->filled('pegawai_id') && $request->filled('bulan')) {
                $results = $this->generateSlip(
                    $request->pegawai_id,
                    $request->bulan
                );
            }
        } else {

            if (!$user->pegawai_id) {
                abort(403, 'Akun pegawai belum terhubung.');
            }

            $pegawai = Pegawai::find($user->pegawai_id);

            if (!$pegawai) {
                abort(403, 'Data pegawai tidak ditemukan.');
            }

            $pegawais = collect([$pegawai]);

            if ($request->filled('bulan')) {
                $results = $this->generateSlip(
                    $pegawai->id,
                    $request->bulan
                );
            }
        }

        return view('dashboard.slip_gaji.index', compact(
            'pegawais',
            'results'
        ));
    }

    /**
     * ==================================================
     * CETAK PDF
     * ==================================================
     */
    public function cetak($pegawaiId, $bulan)
    {
        $user = Auth::user();

        if (
            strtolower($user->role) === 'pegawai' &&
            $user->pegawai_id != $pegawaiId
        ) {
            abort(403);
        }

        $periode = Carbon::parse($bulan);

        $penghasilan = Penghasilan::with('pegawai')
            ->where('pegawai_id', $pegawaiId)
            ->whereMonth('tanggal', $periode->month)
            ->whereYear('tanggal', $periode->year)
            ->firstOrFail();

        $potongan = Potongan::where('pegawai_id', $pegawaiId)
            ->whereMonth('tanggal', $periode->month)
            ->whereYear('tanggal', $periode->year)
            ->first();

        $totalPenghasilan = $penghasilan->total_penghasilan ?? 0;
        $totalPotongan    = $potongan->total_potongan ?? 0;
        $bersih           = max(0, $totalPenghasilan - $totalPotongan);

        $pdf = Pdf::loadView('dashboard.slip_gaji.pdf', [
            'pegawai'           => $penghasilan->pegawai,
            'penghasilan'       => $penghasilan,
            'potongan'          => $potongan,
            'totalPenghasilan'  => $totalPenghasilan,
            'totalPotongan'     => $totalPotongan,
            'bersih'            => $bersih,
            'periode'           => $periode,
        ])->setPaper([0, 0, 595.28, 396.85], 'landscape');

        return $pdf->stream(
            'Slip-Gaji-' .
            $penghasilan->pegawai->nama . '-' .
            $periode->format('F-Y') . '.pdf'
        );
    }

    /**
     * ==================================================
     * GENERATE SLIP (REUSABLE & AMAN)
     * ==================================================
     */
    private function generateSlip($pegawaiId, $bulan)
    {
        $periode = Carbon::parse($bulan);

        $penghasilan = Penghasilan::with('pegawai')
            ->where('pegawai_id', $pegawaiId)
            ->whereMonth('tanggal', $periode->month)
            ->whereYear('tanggal', $periode->year)
            ->first();

        if (!$penghasilan) {
            return [];
        }

        $potongan = Potongan::where('pegawai_id', $pegawaiId)
            ->whereMonth('tanggal', $periode->month)
            ->whereYear('tanggal', $periode->year)
            ->first();

        $totalPenghasilan = $penghasilan->total_penghasilan ?? 0;
        $totalPotongan    = $potongan->total_potongan ?? 0;
        $bersih           = max(0, $totalPenghasilan - $totalPotongan);

        return [[
            'pegawai' => $penghasilan->pegawai,
            'periode' => $periode->translatedFormat('F Y'),
            'bulan'   => $periode->format('Y-m'),
            'bersih'  => $bersih,
        ]];
    }

    /**
     * ==================================================
     * CETAK WORD (DOCX)
     * ==================================================
     */
    public function cetakWord($pegawaiId, $bulan)
    {
        $user = Auth::user();

        if (
            strtolower($user->role) === 'pegawai' &&
            $user->pegawai_id != $pegawaiId
        ) {
            abort(403);
        }

        $periode = Carbon::parse($bulan);

        $penghasilan = Penghasilan::with('pegawai')
            ->where('pegawai_id', $pegawaiId)
            ->whereMonth('tanggal', $periode->month)
            ->whereYear('tanggal', $periode->year)
            ->firstOrFail();

        $potongan = Potongan::where('pegawai_id', $pegawaiId)
            ->whereMonth('tanggal', $periode->month)
            ->whereYear('tanggal', $periode->year)
            ->first();

        $totalPenghasilan = $penghasilan->total_penghasilan ?? 0;
        $totalPotongan    = $potongan->total_potongan ?? 0;
        $bersih           = max(0, $totalPenghasilan - $totalPotongan);

        // Ambil template dari tabel "templates" dengan jenis "slip_gaji"
        $template = Template::where('jenis', 'slip_gaji')->latest()->first();

        if (!$template) {
            return back()->with('error', 'Template Word belum diupload! Silakan upload template slip gaji terlebih dahulu.');
        }

        // Ambil path file template dari storage
        $templatePath = storage_path('app/public/' . $template->file_path);

        if (!file_exists($templatePath)) {
            return back()->with('error', 'File template tidak ditemukan! Path: ' . $templatePath);
        }

        // Load template dengan TemplateProcessor
        $templateProcessor = new TemplateProcessor($templatePath);

        // Data untuk mengganti placeholder
        $templateProcessor->setValue('nama_pegawai', $penghasilan->pegawai->nama);
        $templateProcessor->setValue('nip', $penghasilan->pegawai->nip);
        $templateProcessor->setValue('jabatan', $penghasilan->pegawai->jabatan);
        $templateProcessor->setValue('pangkat_golongan', $penghasilan->pegawai->pangkat_golongan);
        $templateProcessor->setValue('bulan_tahun', $periode->translatedFormat('F Y'));
        $templateProcessor->setValue('tanggal_cetak', 'Ungaran, 1 ' . $periode->translatedFormat('F Y'));
        
        // Penghasilan
        $templateProcessor->setValue('gaji_induk', number_format($penghasilan->gaji_induk ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('tunj_suami_istri', number_format($penghasilan->tunj_suami_istri ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('tunj_anak', number_format($penghasilan->tunj_anak ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('tunj_umum', number_format($penghasilan->tunj_umum ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('tunj_struktural', $penghasilan->tunj_struktural ? number_format($penghasilan->tunj_struktural, 0, ',', '.') : '-');
        $templateProcessor->setValue('tunj_fungsional', $penghasilan->tunj_fungsional ? number_format($penghasilan->tunj_fungsional, 0, ',', '.') : '-');
        $templateProcessor->setValue('tunj_beras', number_format($penghasilan->tunj_beras ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('tunj_pajak', $penghasilan->tunj_pajak ? number_format($penghasilan->tunj_pajak, 0, ',', '.') : '-');
        $templateProcessor->setValue('pembulatan', number_format($penghasilan->pembulatan ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('total_penghasilan', number_format($totalPenghasilan, 0, ',', '.'));
        
        // Potongan
        $templateProcessor->setValue('potongan_wajib', number_format($potongan->potongan_wajib ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('potongan_pajak', number_format($potongan->potongan_pajak ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('potongan_bpjs', number_format($potongan->potongan_bpjs ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('potongan_bpjs_lain', number_format($potongan->potongan_bpjs_lain ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('dana_sosial', number_format($potongan->dana_sosial ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('bank_jateng', number_format($potongan->bank_jateng ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('bank_bjb', number_format($potongan->bank_bjb ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('parcel', number_format($potongan->parcel ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('kop_sayuk_rukun', number_format($potongan->kop_sayuk_rukun ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('kop_mitra_lingua', number_format($potongan->kop_mitra_lingua ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('total_potongan', number_format($totalPotongan, 0, ',', '.'));
        
        // Total bersih
        $templateProcessor->setValue('bersih', number_format($bersih, 0, ',', '.'));
        $templateProcessor->setValue('terbilang', \App\Helpers\Terbilang::convert($bersih));

        // Simpan hasil
        $outputPath = storage_path('app/slip_gaji_' . $penghasilan->pegawai->nip . '_' . time() . '.docx');
        $templateProcessor->saveAs($outputPath);

        // Download file
        return response()->download($outputPath)->deleteFileAfterSend(true);
    }

    /**
     * ==================================================
     * CETAK PDF DARI TEMPLATE WORD
     * ==================================================
     */
    public function cetakPdfDariWord($pegawaiId, $bulan)
    {
        $user = Auth::user();

        if (
            strtolower($user->role) === 'pegawai' &&
            $user->pegawai_id != $pegawaiId
        ) {
            abort(403);
        }

        $periode = Carbon::parse($bulan);

        $penghasilan = Penghasilan::with('pegawai')
            ->where('pegawai_id', $pegawaiId)
            ->whereMonth('tanggal', $periode->month)
            ->whereYear('tanggal', $periode->year)
            ->firstOrFail();

        $potongan = Potongan::where('pegawai_id', $pegawaiId)
            ->whereMonth('tanggal', $periode->month)
            ->whereYear('tanggal', $periode->year)
            ->first();

        $totalPenghasilan = $penghasilan->total_penghasilan ?? 0;
        $totalPotongan    = $potongan->total_potongan ?? 0;
        $bersih           = max(0, $totalPenghasilan - $totalPotongan);

        // Ambil template dari tabel "templates" dengan jenis "slip_gaji"
        $template = Template::where('jenis', 'slip_gaji')->latest()->first();

        if (!$template) {
            return back()->with('error', 'Template Word belum diupload! Silakan upload template slip gaji terlebih dahulu.');
        }

        // Ambil path file template dari storage
        $templatePath = storage_path('app/public/' . $template->file_path);

        if (!file_exists($templatePath)) {
            return back()->with('error', 'File template tidak ditemukan!');
        }

        // Load template dengan TemplateProcessor
        $templateProcessor = new TemplateProcessor($templatePath);

        // Data untuk mengganti placeholder
        $templateProcessor->setValue('nama_pegawai', $penghasilan->pegawai->nama);
        $templateProcessor->setValue('nip', $penghasilan->pegawai->nip);
        $templateProcessor->setValue('jabatan', $penghasilan->pegawai->jabatan);
        $templateProcessor->setValue('pangkat_golongan', $penghasilan->pegawai->pangkat_golongan);
        $templateProcessor->setValue('bulan_tahun', $periode->translatedFormat('F Y'));
        $templateProcessor->setValue('tanggal_cetak', 'Ungaran, 1 ' . $periode->translatedFormat('F Y'));
        
        // Penghasilan
        $templateProcessor->setValue('gaji_induk', number_format($penghasilan->gaji_induk ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('tunj_suami_istri', number_format($penghasilan->tunj_suami_istri ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('tunj_anak', number_format($penghasilan->tunj_anak ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('tunj_umum', number_format($penghasilan->tunj_umum ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('tunj_struktural', $penghasilan->tunj_struktural ? number_format($penghasilan->tunj_struktural, 0, ',', '.') : '-');
        $templateProcessor->setValue('tunj_fungsional', $penghasilan->tunj_fungsional ? number_format($penghasilan->tunj_fungsional, 0, ',', '.') : '-');
        $templateProcessor->setValue('tunj_beras', number_format($penghasilan->tunj_beras ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('tunj_pajak', $penghasilan->tunj_pajak ? number_format($penghasilan->tunj_pajak, 0, ',', '.') : '-');
        $templateProcessor->setValue('pembulatan', number_format($penghasilan->pembulatan ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('total_penghasilan', number_format($totalPenghasilan, 0, ',', '.'));
        
        // Potongan
        $templateProcessor->setValue('potongan_wajib', number_format($potongan->potongan_wajib ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('potongan_pajak', number_format($potongan->potongan_pajak ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('potongan_bpjs', number_format($potongan->potongan_bpjs ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('potongan_bpjs_lain', number_format($potongan->potongan_bpjs_lain ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('dana_sosial', number_format($potongan->dana_sosial ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('bank_jateng', number_format($potongan->bank_jateng ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('bank_bjb', number_format($potongan->bank_bjb ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('parcel', number_format($potongan->parcel ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('kop_sayuk_rukun', number_format($potongan->kop_sayuk_rukun ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('kop_mitra_lingua', number_format($potongan->kop_mitra_lingua ?? 0, 0, ',', '.'));
        $templateProcessor->setValue('total_potongan', number_format($totalPotongan, 0, ',', '.'));
        
        // Total bersih
        $templateProcessor->setValue('bersih', number_format($bersih, 0, ',', '.'));
        $templateProcessor->setValue('terbilang', \App\Helpers\Terbilang::convert($bersih));

        // Simpan hasil Word ke file sementara
        $tempDocxPath = storage_path('app/temp_slip_' . time() . '.docx');
        $templateProcessor->saveAs($tempDocxPath);

        // Konversi DOCX ke HTML
        $phpWord = IOFactory::load($tempDocxPath);
        $htmlWriter = new HtmlWriter($phpWord);
        
        // Simpan HTML ke file sementara
        $tempHtmlPath = storage_path('app/temp_slip_' . time() . '.html');
        $htmlWriter->save($tempHtmlPath);
        
        // Baca HTML
        $htmlContent = file_get_contents($tempHtmlPath);
        
        // Tambahkan style agar tampilan PDF rapi
        $fullHtml = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Slip Gaji</title>
            <style>
                body {
                    font-family: "Times New Roman", Times, serif;
                    font-size: 11pt;
                    margin: 20px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                }
                td, th {
                    padding: 5px;
                    vertical-align: top;
                }
                .text-right {
                    text-align: right;
                }
                .bold {
                    font-weight: bold;
                }
            </style>
        </head>
        <body>
            ' . $htmlContent . '
        </body>
        </html>';
        
        // Konversi HTML ke PDF
        $pdf = Pdf::loadHTML($fullHtml);
        $pdf->setPaper('A4', 'portrait');
        
        // Hapus file sementara
        if (file_exists($tempDocxPath)) unlink($tempDocxPath);
        if (file_exists($tempHtmlPath)) unlink($tempHtmlPath);
        
        // Download PDF
        return $pdf->download('Slip-Gaji-' . $penghasilan->pegawai->nama . '-' . $periode->format('F-Y') . '.pdf');
    }
}