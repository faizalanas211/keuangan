<?php

namespace App\Helpers;

class Terbilang
{
    public static function convert($angka)
    {
        $angka = abs($angka);
        $bilangan = [
            '', 'satu', 'dua', 'tiga', 'empat', 'lima',
            'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'
        ];

        if ($angka < 12) {
            return $bilangan[$angka];
        } elseif ($angka < 20) {
            return self::convert($angka - 10) . ' belas';
        } elseif ($angka < 100) {
            return self::convert(floor($angka / 10)) . ' puluh ' . self::convert($angka % 10);
        } elseif ($angka < 200) {
            return 'seratus ' . self::convert($angka - 100);
        } elseif ($angka < 1000) {
            return self::convert(floor($angka / 100)) . ' ratus ' . self::convert($angka % 100);
        } elseif ($angka < 2000) {
            return 'seribu ' . self::convert($angka - 1000);
        } elseif ($angka < 1000000) {
            return self::convert(floor($angka / 1000)) . ' ribu ' . self::convert($angka % 1000);
        } elseif ($angka < 1000000000) {
            return self::convert(floor($angka / 1000000)) . ' juta ' . self::convert($angka % 1000000);
        } elseif ($angka < 1000000000000) {
            return self::convert(floor($angka / 1000000000)) . ' miliar ' . self::convert($angka % 1000000000);
        }

        return 'terlalu besar';
    }
}
