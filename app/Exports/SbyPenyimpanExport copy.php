<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class SbyPenyimpanExport implements WithEvents, WithCustomStartCell
{
    protected $data;

    public function __construct($data)
    {
        Carbon::setLocale('id');
        $this->data = $data;
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $tanggal = Carbon::parse($this->data['tanggal'])->translatedFormat('d F Y');
                $nominalAngka = $this->data['nominal_angka'];
                $nominalFormat = 'Rp' . number_format($nominalAngka, 0, ',', '.');

                /* ================= HEADER ================= */

                $sheet->mergeCells('A1:H1');
                $sheet->mergeCells('A2:H2');
                $sheet->mergeCells('A3:H3');

                $sheet->setCellValue('A1', 'KEMENTERIAN PENDIDIKAN DASAR DAN MENENGAH');
                $sheet->setCellValue('A2', 'BALAI BAHASA PROVINSI JAWA TENGAH');
                $sheet->setCellValue('A3', 'SURAT PERINTAH BAYAR');

                $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(14);

                $sheet->getStyle('A3')->getFont()
                    ->setBold(true)
                    ->setSize(16)
                    ->setName('Arial Black')
                    ->setUnderline(true);

                $sheet->getStyle('A1:A3')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                /* ================= TANGGAL ================= */

                $sheet->setCellValue('B5', 'Tanggal : '.$tanggal);
                $sheet->setCellValue('G5', 'Nomor : '.$this->data['nomor']);

                $sheet->getStyle('B5')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle('G5')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                /* ================= SECTION 1 ================= */

                $sheet->mergeCells('B7:H7');
                $sheet->setCellValue('B7',
                    'Saya yang bertanda tangan di bawah ini selaku Pejabat Pembuat Komitmen memerintahkan Bendahara Pengeluaran agar melakukan pembayaran sejumlah:'
                );

                $sheet->mergeCells('B8:H8');
                $sheet->setCellValue('B8', $nominalFormat);
                $sheet->getStyle('B8')->getFont()->setBold(true)->setSize(12);

                // Border
                $sheet->getStyle('A7:H8')->getBorders()->getTop()
                    ->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('A7:H8')->getBorders()->getBottom()
                    ->setBorderStyle(Border::BORDER_THIN);

                /* ================= TERBILANG ================= */

                $sheet->mergeCells('B9:H9');
                $sheet->setCellValue(
                    'B9',
                    'Terbilang: ' . trim($this->terbilang($nominalAngka)) . ' rupiah'
                );

                /* ================= SECTION 2 ================= */

                $sheet->setCellValue('B11', 'Kepada');
                $sheet->setCellValue('C11', ':');
                $sheet->mergeCells('D11:H11');
                $sheet->setCellValue('D11', $this->data['kepada']);

                $sheet->setCellValue('B12', 'Untuk pembayaran');
                $sheet->setCellValue('C12', ':');
                $sheet->mergeCells('D12:H13');
                $sheet->setCellValue('D12', $this->data['uraian']);

                $sheet->setCellValue('B21', 'Kegiatan, output, MAK');
                $sheet->setCellValue('C21', ':');
                $sheet->mergeCells('D21:H21');
                $sheet->setCellValue('D21', $this->data['mak']);

                $sheet->setCellValue('B22', 'Kode');
                $sheet->setCellValue('C22', ':');

                // Border atas bawah section isi
                $sheet->getStyle('A11:H22')->getBorders()->getTop()
                    ->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('A11:H22')->getBorders()->getBottom()
                    ->setBorderStyle(Border::BORDER_THIN);

                /* ================= SECTION TTD ================= */

                $sheet->mergeCells('B24:D24');
                $sheet->mergeCells('E24:F24');
                $sheet->mergeCells('G24:H24');

                $sheet->setCellValue('B25', 'Bendahara Pengeluaran,');
                $sheet->setCellValue('G25', 'a.n. Kuasa Pengguna Anggaran');
                $sheet->setCellValue('G26', 'Pejabat Pembuat Komitmen,');

                $sheet->mergeCells('B29:D29');
                $sheet->mergeCells('E29:F29');
                $sheet->mergeCells('G29:H29');

                $sheet->setCellValue('B29', 'Danang Eko Prasetyo');
                $sheet->setCellValue('E29', 'Andy Rahmadi Santoso, S.Kom.');
                $sheet->setCellValue('G29', 'Ngatirah, M.Si.');

                $sheet->mergeCells('B30:D30');
                $sheet->mergeCells('G30:H30');

                $sheet->setCellValue('B30', 'NIP 198001132009101002');
                $sheet->setCellValue('G30', 'NIP 197903132006042002');

                $sheet->getStyle('B24:H30')->getBorders()->getTop()
                    ->setBorderStyle(Border::BORDER_THIN);

                $sheet->getStyle('B24:H30')->getBorders()->getBottom()
                    ->setBorderStyle(Border::BORDER_THIN);

                /* ================= FORMAT UMUM ================= */

                $sheet->getStyle('A1:H40')->getAlignment()->setWrapText(true);

                $sheet->getColumnDimension('A')->setWidth(4);   // kecil
                $sheet->getColumnDimension('B')->setWidth(18);
                $sheet->getColumnDimension('C')->setWidth(3);   // sangat kecil (untuk :)
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(18);
                $sheet->getColumnDimension('F')->setWidth(18);
                $sheet->getColumnDimension('G')->setWidth(20);
                $sheet->getColumnDimension('H')->setWidth(18);
            }
        ];
    }

    private function terbilang($angka)
    {
        $angka = abs($angka);
        $huruf = ["", "satu", "dua", "tiga", "empat", "lima", "enam",
                  "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];

        if ($angka < 12)
            return " " . $huruf[$angka];
        elseif ($angka < 20)
            return $this->terbilang($angka - 10) . " belas";
        elseif ($angka < 100)
            return $this->terbilang($angka / 10) . " puluh" . $this->terbilang($angka % 10);
        elseif ($angka < 200)
            return " seratus" . $this->terbilang($angka - 100);
        elseif ($angka < 1000)
            return $this->terbilang($angka / 100) . " ratus" . $this->terbilang($angka % 100);
        elseif ($angka < 2000)
            return " seribu" . $this->terbilang($angka - 1000);
        elseif ($angka < 1000000)
            return $this->terbilang($angka / 1000) . " ribu" . $this->terbilang($angka % 1000);
        elseif ($angka < 1000000000)
            return $this->terbilang($angka / 1000000) . " juta" . $this->terbilang($angka % 1000000);
    }
}