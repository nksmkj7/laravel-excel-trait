<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Model\Station;
use App\Traits\ImportExcel;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StationImport implements ToCollection, WithHeadingRow
{
    use Importable, ImportExcel;

    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        $this->setModel(Station::class);
        return $this->insert($rows);
    }


    public function rules()
    {
        return [
            'title' => 'required|max:150|unique:stations,title',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Station title is missing',
            'title.max' => 'Stations title should not be longer than 150 character',
        ];
    }

    public function transform($data)
    {
        return [
            'title' => $data['title'],
        ];
    }
}
