<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class AmplopExport implements FromCollection, WithEvents
{
    protected $peserta;

    public function __construct($peserta)
    {
        $this->peserta = $peserta;
    }

    public function collection()
    {
        return new Collection([]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $peserta = $this->peserta;
                $perjalanan = $peserta->perjalananDinas;
                $namaPeserta = $peserta instanceof \App\Models\PerjalananDinasPegawai
                                ? $peserta->pegawai->nama
                                : $peserta->nama;

                // =========================
                // FILTER RINCIAN
                // exclude penginapan
                // =========================
                $rincian = $peserta->rincian->filter(function ($r) {

                    $nama = strtolower($r->jenisBiaya->nama_biaya);

                    return !str_contains($nama, 'penginapan');
                });

                $total = $rincian->sum('total');

                // =========================
                // TITLE
                // =========================
                $sheet->mergeCells('A1:D1');
                $sheet->setCellValue(
                    'A1',
                    'Perjalanan Dinas Fullboard kegiatan '.$perjalanan->nama_kegiatan
                );

                $sheet->mergeCells('A3:D3');
                $sheet->setCellValue(
                    'A3',
                    'Nama: '.$namaPeserta
                );

                // =========================
                // HEADER TABEL
                // =========================
                $startRow = 5;

                $sheet->setCellValue("A{$startRow}", 'No.');

                $sheet->mergeCells("B{$startRow}:C{$startRow}");
                $sheet->setCellValue("B{$startRow}", 'Perincian Biaya');

                $sheet->setCellValue("D{$startRow}", 'Jumlah');

                // =========================
                // DATA
                // =========================
                $row = $startRow + 1;
                $no = 1;

                foreach ($rincian as $r) {

                    $uraian = $r->jenisBiaya->nama_biaya;

                    if ($r->uraian) {
                        $uraian .= ' '.$r->uraian;
                    }

                    $uraian .= ' : '
                        .(int)$r->volume.' '
                        .$r->satuan
                        .' x Rp'
                        .number_format($r->tarif, 0, ',', '.');

                    $sheet->setCellValue("A{$row}", $no++);
                    $sheet->mergeCells("B{$row}:C{$row}");
                    $sheet->setCellValue("B{$row}", $uraian);
                    $sheet->setCellValue(
                        "D{$row}",
                        $r->total
                    );

                    $row++;
                }

                // =========================
                // TOTAL
                // =========================
                $sheet->mergeCells("A{$row}:C{$row}");
                $sheet->setCellValue("A{$row}", 'Jumlah');

                $dataStartRow = $startRow + 1;
                $dataEndRow = $row - 1;

                $sheet->setCellValue(
                    "D{$row}",
                    "=SUM(D{$dataStartRow}:D{$dataEndRow})"
                );

                $totalRow = $row;

                $row++;

                // =========================
                // TERBILANG (MASUK TABEL)
                // =========================
                $sheet->mergeCells("A{$row}:D{$row}");

                $sheet->setCellValue(
                    "A{$row}",
                    'Terbilang: '.trim($this->terbilang($total)).' Rupiah'
                );

                $terbilangRow = $row;

                // =========================
                // STYLE
                // =========================
                $sheet->getStyle("D".($startRow+1).":D{$terbilangRow}")
                    ->getNumberFormat()
                    ->setFormatCode('"Rp"#,##0');

                $sheet->getStyle("A1:D{$row}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_TOP);

                // =========================
                // BORDER HEADER + TOTAL + TERBILANG
                // =========================
                $sheet->getStyle("A{$startRow}:D{$terbilangRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // =========================
                // HILANGKAN BORDER ANTAR BARIS
                // DI KOLOM RINCIAN
                // =========================
                $dataStart = $startRow + 1;
                $dataEnd = $totalRow - 1;

                for ($i = $dataStart; $i <= $dataEnd; $i++) {

                    // no border atas bawah
                    $sheet->getStyle("A{$i}:D{$i}")
                        ->getBorders()
                        ->getTop()
                        ->setBorderStyle(Border::BORDER_NONE);

                    $sheet->getStyle("A{$i}:D{$i}")
                        ->getBorders()
                        ->getBottom()
                        ->setBorderStyle(Border::BORDER_NONE);
                }

                // wrap text
                $sheet->getStyle("B1:B{$row}")
                    ->getAlignment()
                    ->setWrapText(true);

                // width
                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(60);
                $sheet->getColumnDimension('C')->setWidth(10);
                $sheet->getColumnDimension('D')->setWidth(20);

                // hide gridlines
                $sheet->setShowGridlines(false);

                // landscape
                $sheet->getPageSetup()->setOrientation(
                    \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
                );

                // fit to page
                $sheet->getPageSetup()->setFitToWidth(1);

                // center vertical
                $sheet->getStyle("A1:D{$terbilangRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // font
                $sheet->getStyle("A1:D{$terbilangRow}")
                    ->getFont()
                    ->setName('Arial')
                    ->setSize(11);

                // =========================
                // BOLD
                // =========================

                // judul
                $sheet->getStyle('A1')
                    ->getFont()
                    ->setBold(true);

                // nama
                $sheet->getStyle('A3')
                    ->getFont()
                    ->setBold(true);

                // header tabel
                $sheet->getStyle("A{$startRow}:D{$startRow}")
                    ->getFont()
                    ->setBold(true);

                // jumlah
                $sheet->getStyle("A{$totalRow}:D{$totalRow}")
                    ->getFont()
                    ->setBold(true);

                // terbilang
                $sheet->getStyle("A{$terbilangRow}")
                    ->getFont()
                    ->setBold(true);

                $sheet->getStyle("A{$startRow}:D{$startRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle("D".($startRow+1).":D{$terbilangRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // =========================
                // PAGE SETUP
                // =========================

                // landscape
                $sheet->getPageSetup()->setOrientation(
                    PageSetup::ORIENTATION_LANDSCAPE
                );

                // custom size envelope #10
                // ukuran approx: 10.48 x 24.13 cm
                $sheet->getPageSetup()->setPaperSize(20);

                // fit 1 halaman
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(1);

                // margin biar ga mepet saat print
                $sheet->getPageMargins()->setTop(0.3);
                $sheet->getPageMargins()->setRight(0.25);
                $sheet->getPageMargins()->setLeft(0.25);
                $sheet->getPageMargins()->setBottom(0.3);

                // center horizontal saat print
                $sheet->getPageSetup()->setHorizontalCentered(true);
                            }
                        ];
                
    }

    private function terbilang($angka)
    {
        $angka = abs($angka);

        $huruf = [
            "",
            "Satu",
            "Dua",
            "Tiga",
            "Empat",
            "Lima",
            "Enam",
            "Tujuh",
            "Delapan",
            "Sembilan",
            "Sepuluh",
            "Sebelas"
        ];

        if ($angka < 12) {
            return " " . $huruf[$angka];
        } elseif ($angka < 20) {
            return $this->terbilang($angka - 10) . " Belas";
        } elseif ($angka < 100) {
            return $this->terbilang($angka / 10) . " Puluh" . $this->terbilang($angka % 10);
        } elseif ($angka < 200) {
            return " Seratus" . $this->terbilang($angka - 100);
        } elseif ($angka < 1000) {
            return $this->terbilang($angka / 100) . " Ratus" . $this->terbilang($angka % 100);
        } elseif ($angka < 2000) {
            return " Seribu" . $this->terbilang($angka - 1000);
        } elseif ($angka < 1000000) {
            return $this->terbilang($angka / 1000) . " Ribu" . $this->terbilang($angka % 1000);
        }

        return (string) $angka;
    }
}