<?php

namespace App\Exports;

use App\Models\PejabatPeriode;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class NominatifPerjalananExport implements FromCollection, WithCustomStartCell, WithEvents
{
    protected $perjalanan;

    protected $jenisBiaya;

    protected $columns;

public function __construct($perjalanan)
{
    Carbon::setLocale('id');
    $this->perjalanan = $perjalanan;

    // ambil semua rincian unik (jenis + uraian)
    $this->columns = $perjalanan->pegawaiPerjalanan
        ->flatMap(fn($pp) => $pp->rincian)
        ->map(function ($r) {
            return [
                'jenis_id' => $r->jenis_biaya_id,
                'uraian'   => $r->uraian ?? '-',
                'label'    => strtoupper($r->uraian ?? '-'),
                'satuan'   => $r->satuan ?? 'VOL',
            ];
        })
        ->unique(fn($item) => $item['jenis_id'].'-'.$item['uraian'])
        ->values();
}

    public function startCell(): string
    {
        return 'A5';
    }

    public function collection()
{
    $rows = collect();
    $no = 1;
    $mulai = $this->perjalanan->tanggal_mulai;
    $akhir = $this->perjalanan->tanggal_akhir;
    $jadwal = $mulai->isSameDay($akhir)
        ? $mulai->translatedFormat('d F Y')
        : $mulai->translatedFormat('d').' - '.$akhir->translatedFormat('d F Y');

    foreach ($this->perjalanan->pegawaiPerjalanan as $pp) {

        $row = [
            $no++,
            $pp->pegawai->nama,
            $this->perjalanan->dari_kota,
            $this->perjalanan->tujuan_kota,
            $jadwal,
        ];

        $totalAll = 0;

        foreach ($this->columns as $col) {

            $r = $pp->rincian
                ->first(function ($item) use ($col) {
                    return $item->jenis_biaya_id == $col['jenis_id']
                        && ($item->uraian ?? '-') == $col['uraian'];
                });

            if ($r) {
                $row[] = $r->volume ?? '-';
                $row[] = $r->tarif ?? '-';
                $row[] = $r->total ?? '-';

                $totalAll += $r->total ?? 0;
            } else {
                $row[] = '-';
                $row[] = '-';
                $row[] = '-';
            }
        }

        $row[] = $totalAll ?: '-';
        $row[] = ''; // tanda tangan
        $row[] = $this->perjalanan->nama_kegiatan; // keterangan

        $rows->push($row);
    }

    return $rows;
}

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $totalRow = $lastRow + 1;

                $this->applyTitle($sheet);
                $this->applyHeader($sheet);
                $this->applyColumnWidth($sheet);
                $this->applyNumberFormat($sheet, $totalRow);
                $this->applyRowHeight($sheet, $lastRow);
                $this->applyTotal($sheet, $lastRow, $totalRow);

                $sheet->getStyle('A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow())
                    ->getFont()
                    ->setName('Arial')
                    ->setSize(10);

                $this->applyAlignment($sheet, $totalRow);
                $this->applyBorder($sheet, $totalRow);
                $this->applyFooter($sheet, $totalRow);

                $lastCol = $sheet->getHighestColumn();

                // kolom terakhir = keterangan
                $sheet->mergeCells("{$lastCol}5:{$lastCol}{$totalRow}");

                $sheet->getStyle("{$lastCol}5:{$lastCol}{$totalRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);
            }
        ];
    }

    /* ================= METHODS ================= */

    private function applyTitle($sheet)
{
    $lastCol = $sheet->getHighestColumn(); // 🔥 ambil kolom terakhir

    $sheet->mergeCells("A1:{$lastCol}1");
    $sheet->setCellValue('A1', 'DAFTAR NOMINATIF PEGAWAI');

    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('A1')->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setVertical(Alignment::VERTICAL_CENTER);

    $sheet->getRowDimension(1)->setRowHeight(30);
}

    private function applyHeader($sheet)
{
    // FIXED
    $sheet->mergeCells('A3:A4')->setCellValue('A3','NO');
    $sheet->mergeCells('B3:B4')->setCellValue('B3','NAMA');
    $sheet->mergeCells('C3:D3')->setCellValue('C3','TUJUAN');
    $sheet->setCellValue('C4','DARI');
    $sheet->setCellValue('D4','KE');

    $sheet->mergeCells('E3:E4')->setCellValue('E3','JADWAL PELAKSANAAN');

    // START DINAMIS
    $colIndex = 6; // F

    foreach ($this->columns as $col) {

        $start = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
        $end   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 2);

        // header utama
        $sheet->mergeCells("$start"."3:$end"."3");
        $sheet->setCellValue("$start"."3", $col['label']);

        // subheader
        $sheet->setCellValue("$start"."4", strtoupper($col['satuan']));
        $sheet->setCellValue(
            \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1).'4',
            'TARIF'
        );
        $sheet->setCellValue(
            \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 2).'4',
            'JUMLAH'
        );

        $colIndex += 3;
    }

    // ===== JUMLAH DIBAYAR =====
$jumlahCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);

$sheet->mergeCells("{$jumlahCol}3:{$jumlahCol}4");
$sheet->setCellValue("{$jumlahCol}3", 'JUMLAH DIBAYAR');

$colIndex++; // 🔥 pindah kolom!

// ===== TANDA TANGAN =====
$ttdCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);

$sheet->mergeCells("{$ttdCol}3:{$ttdCol}4");
$sheet->setCellValue("{$ttdCol}3", 'TANDA TANGAN');

$colIndex++; // 🔥 pindah lagi

// ===== KETERANGAN =====
$ketCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);

$sheet->mergeCells("{$ketCol}3:{$ketCol}4");
$sheet->setCellValue("{$ketCol}3", 'KET.');

// STYLE
$sheet->getStyle("A3:{$ketCol}4")->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
    ->setVertical(Alignment::VERTICAL_CENTER);
}

    private function applyColumnWidth($sheet)
    {
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(28); // nama agak lebar
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(10);

        // kolom dinamis
        $lastCol = $sheet->getHighestColumn();
        $lastIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($lastCol);

        for ($i = 6; $i <= $lastIndex; $i++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($col)->setWidth(10);
        }
    }

    private function applyNumberFormat($sheet, $totalRow)
    {
        $lastCol = $sheet->getHighestColumn();

        // semua kolom angka (mulai F)
        $sheet->getStyle("F5:{$lastCol}{$totalRow}")
            ->getNumberFormat()
            ->setFormatCode('#,##0');

        $sheet->getStyle("F5:{$lastCol}{$totalRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
            ->setVertical(Alignment::VERTICAL_CENTER);
    }

    private function applyRowHeight($sheet, $lastRow)
    {
        for ($i = 5; $i <= $lastRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(45);
        }
    }

    private function applyTotal($sheet, $lastRow, $totalRow)
{
    // label kiri
    $sheet->mergeCells("A{$totalRow}:E{$totalRow}");
    $sheet->setCellValue("A{$totalRow}", "JUMLAH");

    $colIndex = 6; // mulai dari F

    foreach ($this->columns as $col) {

    $volCol   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
    $tarifCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
    $jumlahCol= \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 2);

    // TOTAL kolom jumlah
    $sheet->setCellValue(
        "{$jumlahCol}{$totalRow}",
        "=IF(COUNT({$jumlahCol}5:{$jumlahCol}{$lastRow})=0,\"-\",SUM({$jumlahCol}5:{$jumlahCol}{$lastRow}))"
    );

    // WARNA ABU UNTUK VOL & TARIF
    $sheet->getStyle("{$volCol}{$totalRow}:{$tarifCol}{$totalRow}")
        ->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFBFBFBF');

    $colIndex += 3;

    // kolom tanda tangan (setelah jumlah dibayar)
    $ttdCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);

    $sheet->getStyle("{$ttdCol}{$totalRow}")
        ->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFBFBFBF');
}

    // ===== TOTAL DIBAYAR (kolom terakhir) =====
    $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);

    $sheet->setCellValue(
        "{$lastCol}{$totalRow}",
        "=IF(COUNT({$lastCol}5:{$lastCol}{$lastRow})=0,\"-\",SUM({$lastCol}5:{$lastCol}{$lastRow}))"
    );

    // bold
    $sheet->getStyle("A{$totalRow}:{$lastCol}{$totalRow}")
        ->getFont()->setBold(false);
}

    private function applyAlignment($sheet, $totalRow)
    {
        $sheet->getStyle("A5:A$totalRow")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle("B5:B$totalRow")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle("C5:E$totalRow")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle("F5:F$totalRow")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle("G5:G$totalRow")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle("H5:J$totalRow")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
            ->setVertical(Alignment::VERTICAL_CENTER);
    }

    private function applyBorder($sheet, $totalRow)
    {
        $lastCol = $sheet->getHighestColumn();

$sheet->getStyle("A3:{$lastCol}{$totalRow}")
    ->getBorders()
    ->getAllBorders()
    ->setBorderStyle(Border::BORDER_THIN);
    }

    private function applyFooter($sheet, $totalRow)
    {
        $footer = $totalRow + 3;

        $tanggal = $this->perjalanan->tanggal_mulai;

        $ppk = PejabatPeriode::getByTanggal('Pejabat Pembuat Komitmen', $tanggal);
        $bendahara = PejabatPeriode::getByTanggal('Bendahara Pengeluaran', $tanggal);

        // ===== PPK =====
        $sheet->setCellValue("A$footer", "Mengetahui:");
        $sheet->setCellValue("A".($footer+1), "Pejabat Pembuat Komitmen,");

        if ($ppk) {
            $sheet->setCellValue("A".($footer+5), $ppk->pegawai->nama);
            $sheet->setCellValue("A".($footer+6), "NIP ".$ppk->pegawai->nip);
        }

        // ===== Bendahara =====
        $sheet->setCellValue(
            "H$footer",
            "Kab. Semarang, ".$this->perjalanan->tanggal_terima->translatedFormat('d F Y')
        );
        $sheet->setCellValue("H".($footer+1), "Bendahara,");

        if ($bendahara) {
            $sheet->setCellValue("H".($footer+5), $bendahara->pegawai->nama);
            $sheet->setCellValue("H".($footer+6), "NIP ".$bendahara->pegawai->nip);
        }
    }
}