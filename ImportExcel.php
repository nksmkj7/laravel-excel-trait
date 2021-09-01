<?php

namespace App\Traits;

use App\Exports\ExportRejectedData;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

trait ImportExcel
{
    protected $rejectedData = [], $rejectMessage, $model, $importCount = 0;

    public function setModel($model)
    {
        $this->model = app()->make($model);
    }

    public function getModel()
    {
        return $this->model;
    }

    protected function getMessageBag()
    {
        return new MessageBag();
    }

    public function totalRowsCount($rows)
    {
        $this->rowsCount = $rows->count();
    }

    public function rules()
    {
        return [];
    }

    public function messages()
    {
        return [];
    }

    public function headers(){
        return null;
    }

    public function rejectedExcelDownloadUrl(){
        return url('system/download-rejected-data/');
    }

    protected function prepareCsvData($data)
    {
        if ($data instanceof \Illuminate\Support\Collection) {
            $data = $data->toArray();
        }
        if (gettype($data) != 'array') {
            throw new Exception('transformData method expects either collection or array');
        }
        return $this->transform($data);
    }

    public function transform($data = [])
    {
        return $data;
    }

    public function validate($data, $callBack = null)
    {
        $validator = Validator::make($data, $this->rules(), $this->messages());
        if ($callBack) {
            $validator->after($callBack);
        }
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->rejectMessage =  $this->rejectMessage . ' ' . $error;
            }
            return false;
        }

        return true;
    }

    public function insert(Collection $rows)
    {
        $headers = $this->headers() ?? array_map(function($item){
            return Str::title(implode(' ',explode('_',$item)));
        },array_keys($rows->first()->toArray()));
        array_push($headers,"Error Message");
        $this->totalRowsCount($rows);
        $model = $this->getModel();
        if (!$model && !$model instanceof \Illuminate\Database\Eloquent\Model) {
            throw new Exception('Invalid model type');
        }
        foreach ($rows as $row) {
            $this->rejectMessage = '';
            $data = $this->prepareCsvData($row);
            $status = $this->validate($data);
            if (!$status) {
                array_push($this->rejectedData, $row->toArray() + ['error_message' => $this->rejectMessage]);
                continue;
            }
            try {
                $this->create($data);
                $this->importCount++;
            } catch (Exception $e) {
                array_push($this->rejectedData, $row->toArray() + ['error_message' => $e->getMessage()]);
                continue;
            }
        }
        return $this->exportRejectedData($headers);
    }


    public function exportRejectedData($headers)
    {
        $messageBag = $this->getMessageBag();
        $excelName = Str::random(6) . '-RejectedData.xlsx';
        if (count($this->rejectedData) == $this->rowsCount) {
            $messageBag->add('alert-danger', 'No data imported. Rejected data excel is downloaded automatically for your reference');
            Excel::store(new ExportRejectedData($this->rejectedData,$headers), '/rejected-excels/' . $excelName);
            session()->flash('rejected_data_url', $this->rejectedExcelDownloadUrl().'/'.$excelName);
        } else if (!empty($this->rejectedData)) {
            Excel::store(new ExportRejectedData($this->rejectedData,$headers), '/rejected-excels/' . $excelName);
            $messageBag->add('alert-success', "{$this->importCount} of {$this->rowsCount} data has been imported. Rejected data excel is downloaded automatically for your reference");
            session()->flash('rejected_data_url',$this->rejectedExcelDownloadUrl().'/'.$excelName);
        } else {
            $messageBag->add('alert-success', 'All data has been imported.');
        }
        session()->flash('errors', $messageBag);
        return count($this->rejectedData) > 0;
    }

    public function create($data)
    {
        $model = $this->getModel();
        return $model::create($data);
    }
}
