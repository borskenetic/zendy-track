<?php

namespace App\Exports;

use App\Models\AppModelsZendyLog;
use Maatwebsite\Excel\Concerns\FromCollection;

class ZendyLogsExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return AppModelsZendyLog::all();
    }
}
