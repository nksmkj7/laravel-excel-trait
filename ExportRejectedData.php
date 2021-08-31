<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportRejectedData implements FromCollection, WithHeadings
{

    public function __construct($data,$headers)
    {
        $this->data = $data;
        $this->headers = $headers; 
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
        return $this->headers;
    }
}
