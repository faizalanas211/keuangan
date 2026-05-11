<?php

namespace App\Exports;

use App\Models\JenisBiaya;
use App\Models\PejabatPeriode;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
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

    $this->columns = JenisBiaya::all()->map(function ($j) use ($perjalanan) {

        $sample = collect()
            ->merge($perjalanan->pegawaiPerjalanan->flatMap->rincian)
            ->merge($perjalanan->nonpegawai->flatMap->rincian)
            ->firstWhere('jenis_biaya_id', $j->id);

        $uraian = $sample->uraian ?? null;
        $isTransport = str_contains(
            strtolower($j->nama_biaya),
            'transport'
        );

        return [
            'jenis_id' => $j->id,
            'label' => strtoupper(
                $j->nama_biaya .
                (
                    !$isTransport && $uraian
                        ? " ($uraian)"
                        : ""
                )
            ),
            'is_transport' => str_contains(strtolower($j->nama_biaya), 'transport'),
            'satuan' => $sample->satuan ?? 'VOL'
        ];
    });
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

    // =====================
    // PEGAWAI
    // =====================
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

            $items = $pp->rincian->where('jenis_biaya_id', $col['jenis_id']);

            if ($items->isNotEmpty()) {

                if ($col['is_transport']) {

                    $total = $items->sum('total');
                    $row[] = $total ?: '-';
                    $totalAll += $total;

                } else {

                    $volume = $items->sum('volume');
                    $tarif  = $items->avg('tarif');
                    $total  = $items->sum('total');

                    $row[] = $volume ?: '-';
                    $row[] = $tarif ?: '-';
                    $row[] = $total ?: '-';

                    $totalAll += $total;
                }

            } else {

                if ($col['is_transport']) {
                    $row[] = '-';
                } else {
                    $row[] = '-';
                    $row[] = '-';
                    $row[] = '-';
                }
            }
        }

        $row[] = $totalAll ?: '-';
        $row[] = '';
        $row[] = $this->perjalanan->nama_kegiatan;

        $rows->push($row);
    }

    // =====================
    // NON PEGAWAI
    // =====================
    foreach ($this->perjalanan->nonpegawai as $np) {

        $row = [
            $no++,
            $np->nama,
            $this->perjalanan->dari_kota,
            $this->perjalanan->tujuan_kota,
            $jadwal,
        ];

        $totalAll = 0;

        foreach ($this->columns as $col) {

            $items = $np->rincian->where('jenis_biaya_id', $col['jenis_id']);

            if ($items->isNotEmpty()) {

                if ($col['is_transport']) {

                    $total = $items->sum('total');
                    $row[] = $total ?: '-';
                    $totalAll += $total;

                } else {

                    $volume = $items->sum('volume');
                    $tarif  = $items->avg('tarif');
                    $total  = $items->sum('total');

                    $row[] = $volume ?: '-';
                    $row[] = $tarif ?: '-';
                    $row[] = $total ?: '-';

                    $totalAll += $total;
                }

            } else {

                if ($col['is_transport']) {
                    $row[] = '-';
                } else {
                    $row[] = '-';
                    $row[] = '-';
                    $row[] = '-';
                }
            }
        }
        
        $row[] = $totalAll ?: '-';
        $row[] = '';
        $row[] = $this->perjalanan->nama_kegiatan;

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

        $start = Coordinate::stringFromColumnIndex($colIndex);

        if ($col['is_transport']) {

    // ✅ HEADER UTAMA
    $sheet->setCellValue("$start"."3", $col['label']); // misal: UANG TRANSPORT

    // ✅ SUBHEADER (baris 4)
    $sheet->setCellValue(
        "$start"."4",
        strtoupper($this->perjalanan->alat_angkutan ?? 'TRANSPORT')
    );

    $colIndex += 1;
} else {

            $end = Coordinate::stringFromColumnIndex($colIndex + 2);

            $sheet->mergeCells("$start"."3:$end"."3");
            $sheet->setCellValue("$start"."3", $col['label']);

            $sheet->setCellValue("$start"."4", strtoupper($col['satuan'] ?? 'VOL'));
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex + 1).'4', 'TARIF');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex + 2).'4', 'JUMLAH');

            $colIndex += 3;
        }
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
        $sheet->getColumnDimension('E')->setWidth(20);

        // kolom dinamis
        $lastCol = $sheet->getHighestColumn();
        $lastIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($lastCol);

        for ($i = 6; $i <= $lastIndex; $i++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($col)->setWidth(15);
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

    $colIndex = 6;

foreach ($this->columns as $col) {

    if ($col['is_transport']) {

        $colLetter = Coordinate::stringFromColumnIndex($colIndex);

        // ✅ TOTAL TRANSPORT
        $sheet->setCellValue(
            "{$colLetter}{$totalRow}",
            "=IF(COUNT({$colLetter}5:{$colLetter}{$lastRow})=0,\"-\",SUM({$colLetter}5:{$colLetter}{$lastRow}))"
        );

        $colIndex += 1;

    } else {

        $volCol   = Coordinate::stringFromColumnIndex($colIndex);
        $tarifCol = Coordinate::stringFromColumnIndex($colIndex + 1);
        $jumlahCol= Coordinate::stringFromColumnIndex($colIndex + 2);

        // ✅ TOTAL JUMLAH
        $sheet->setCellValue(
            "{$jumlahCol}{$totalRow}",
            "=IF(COUNT({$jumlahCol}5:{$jumlahCol}{$lastRow})=0,\"-\",SUM({$jumlahCol}5:{$jumlahCol}{$lastRow}))"
        );

        // ✅ ABUIN VOL & TARIF
        $sheet->getStyle("{$volCol}{$totalRow}:{$tarifCol}{$totalRow}")
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFBFBFBF');

        $colIndex += 3;
    }
}


// ===== TOTAL DIBAYAR =====
$jumlahDibayarCol = Coordinate::stringFromColumnIndex($colIndex);

$sheet->setCellValue(
    "{$jumlahDibayarCol}{$totalRow}",
    "=IF(COUNT({$jumlahDibayarCol}5:{$jumlahDibayarCol}{$lastRow})=0,\"-\",SUM({$jumlahDibayarCol}5:{$jumlahDibayarCol}{$lastRow}))"
);

// abuin tanda tangan
$ttdCol = Coordinate::stringFromColumnIndex($colIndex + 1);
$sheet->getStyle("{$ttdCol}{$totalRow}")
    ->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()->setARGB('FFBFBFBF');

    // ===== TOTAL DIBAYAR (kolom terakhir) =====
    $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);

    $sheet->setCellValue(
        "{$lastCol}{$totalRow}",
        "=IF(COUNT({$lastCol}5:{$lastCol}{$lastRow})=0,\"-\",SUM({$lastCol}5:{$lastCol}{$lastRow}))"
    );

    // bold
    $sheet->getStyle("A{$totalRow}:{$lastCol}{$totalRow}")
        ->getFont()->setBold(false);

    $sheet->getRowDimension($totalRow)->setRowHeight(40);
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