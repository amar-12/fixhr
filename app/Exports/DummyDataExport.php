<?php


namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class DummyDataExport implements FromArray
{
    protected $data;

    // Constructor to pass the dummy data
    public function __construct($data)
    {
        $this->data = $data;
    }

    // Return the data for export
    public function array(): array
    {
        return $this->data;
    }
}

