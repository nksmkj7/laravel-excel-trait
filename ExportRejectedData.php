<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportRejectedData implements FromCollection, WithHeadings
{

    public function __construct($data)
    {
        $this->data = $data;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return (collect($this->data));
    }

    public function headings(): array
    {
        return [
            'Title',
            "Reason of rejection"
        ];
    }
}
