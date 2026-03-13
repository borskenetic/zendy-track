<?php

namespace App\Imports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\ToModel;

class StudentsImport implements ToModel
{
    public function model(array $row)
    {
        return new Student([
            'id_number' => $row[0],
            'lastname' => $row[1],
            'firstname' => $row[2],
            'middle_initial' => $row[3],
            'birthday' => $row[4],
            'qrcode' => $row[5],
            'course' => $row[6],
            'year' => $row[7],
            'mobile_number' => $row[8],
            'address' => $row[9],
        ]);
    }
}