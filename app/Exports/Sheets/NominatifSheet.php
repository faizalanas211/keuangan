<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromView;
use App\Models\Perjalanan;

class NominatifSheet implements FromView
{
    protected $perjalanan;

    public function __construct($perjalanan)
    {
        $this->perjalanan = $perjalanan;
    }

    public function view(): \Illuminate\Contracts\View\View
    {
        return view('exports.nominatif', [
            'perjalanan' => $this->perjalanan
        ]);
    }
}
