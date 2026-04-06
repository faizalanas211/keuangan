<?php

namespace App\Exports;

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

    public function __construct($perjalanan)
    {
        Carbon::setLocale('id');
        $this->perjalanan = $perjalanan;
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function collection()
    {
        $rows = collect();
        $no = 1;

        foreach ($this->perjalanan->pegawaiPerjalanan as $pp) {

            $transport = $pp->rincian
                ->filter(fn($r) => str_contains(strtolower($r->jenisBiaya->nama_biaya), 'transport'))
                ->sum('total');

            $harian = $pp->rincian
                ->filter(fn($r) => str_contains(strtolower($r->jenisBiaya->nama_biaya), 'harian perjalanan dinas'))
                ->sum('total');

            $hari = $this->perjalanan->tanggal_mulai
                ->diffInDays($this->perjalanan->tanggal_akhir) + 1;

            $tarif = $hari > 0 ? ($harian / $hari) : 0;
            $jumlahDibayar = $transport + $harian;

            $rows->push([
                $no++,
                $pp->pegawai->nama,
                $this->perjalanan->dari_kota,
                $this->perjalanan->tujuan_kota,
                $this->perjalanan->tanggal_mulai->translatedFormat('d F Y'),

                $transport ?: '-',
                $harian ? $hari : '-',
                $harian ? $tarif : '-',
                $harian ?: '-',
                $jumlahDibayar ?: '-',

                '',
                $this->perjalanan->nama_kegiatan
            ]);
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

                $sheet->mergeCells("L5:L$totalRow");

                $sheet->getStyle("L5:L$totalRow")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $this->applyAlignment($sheet, $totalRow);
                $this->applyBorder($sheet, $totalRow);
                $this->applyFooter($sheet, $totalRow);
            }
        ];
    }

    /* ================= METHODS ================= */

    private function applyTitle($sheet)
    {
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', 'DAFTAR NOMINATIF PEGAWAI');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getRowDimension(1)->setRowHeight(30);
    }

    private function applyHeader($sheet)
    {
        $sheet->mergeCells('A3:A4');
        $sheet->mergeCells('B3:B4');
        $sheet->mergeCells('C3:D3');
        $sheet->mergeCells('E3:E4');
        $sheet->mergeCells('G3:I3');
        $sheet->mergeCells('J3:J4');
        $sheet->mergeCells('K3:K4');
        $sheet->mergeCells('L3:L4');

        $sheet->setCellValue('A3', 'NO');
        $sheet->setCellValue('B3', 'NAMA');
        $sheet->setCellValue('C3', 'TUJUAN');
        $sheet->setCellValue('E3', 'JADWAL PELAKSANAAN');
        $sheet->setCellValue('F3', 'TRANSPOR');
        $sheet->setCellValue('G3', 'UANG HARIAN');
        $sheet->setCellValue('J3', 'JUMLAH DIBAYAR');
        $sheet->setCellValue('K3', 'TANDA TANGAN');
        $sheet->setCellValue('L3', 'KET.');

        $sheet->setCellValue('C4', 'DARI');
        $sheet->setCellValue('D4', 'KE');
        $sheet->setCellValue('F4', strtoupper($this->perjalanan->alat_angkutan));
        $sheet->setCellValue('G4', 'HARI');
        $sheet->setCellValue('H4', 'TARIF');
        $sheet->setCellValue('I4', 'JUMLAH');

        $sheet->getStyle('A3:L4')->getFont()->setBold(true);
        $sheet->getStyle('A3:L4')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
    }

    private function applyColumnWidth($sheet)
    {
        foreach ([
            'A'=>6,'B'=>25,'C'=>18,'D'=>18,'E'=>20,
            'F'=>15,'G'=>8,'H'=>15,'I'=>15,'J'=>18,'K'=>20,'L'=>30
        ] as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
    }

    private function applyNumberFormat($sheet, $totalRow)
    {
        $sheet->getStyle("F5:F$totalRow")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("H5:J$totalRow")->getNumberFormat()->setFormatCode('#,##0');
    }

    private function applyRowHeight($sheet, $lastRow)
    {
        for ($i = 5; $i <= $lastRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(45);
        }
    }

    private function applyTotal($sheet, $lastRow, $totalRow)
    {
        $sheet->mergeCells("A$totalRow:E$totalRow");
        $sheet->setCellValue("A$totalRow", "JUMLAH");

        $sheet->setCellValue("F$totalRow", "=IF(COUNT(F5:F$lastRow)=0,\"-\",SUM(F5:F$lastRow))");
        $sheet->setCellValue("I$totalRow", "=IF(COUNT(I5:I$lastRow)=0,\"-\",SUM(I5:I$lastRow))");
        $sheet->setCellValue("J$totalRow", "=IF(COUNT(J5:J$lastRow)=0,\"-\",SUM(J5:J$lastRow))");

        $sheet->getStyle("G$totalRow:H$totalRow")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFBFBFBF');

        $sheet->getStyle("K$totalRow")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFBFBFBF');

        $sheet->getStyle("A$totalRow:L$totalRow")->getFont()->setBold(true);
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
        $sheet->getStyle("A3:L$totalRow")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
    }

    private function applyFooter($sheet, $totalRow)
    {
        $footer = $totalRow + 3;

        $sheet->setCellValue("A$footer", "Mengetahui:");
        $sheet->setCellValue("A".($footer+1), "Pejabat Pembuat Komitmen,");
        $sheet->setCellValue("A".($footer+5), "Ngatirah, M.Si.");
        $sheet->setCellValue("A".($footer+6), "NIP 197903132006042002");

        $sheet->setCellValue("H$footer",
            "Kab. Semarang, ".$this->perjalanan->tanggal_mulai->translatedFormat('d F Y')
        );
        $sheet->setCellValue("H".($footer+1), "Bendahara,");
        $sheet->setCellValue("H".($footer+5), "Danang Eko Prasetyo");
        $sheet->setCellValue("H".($footer+6), "NIP 198001132009101002");
    }
}