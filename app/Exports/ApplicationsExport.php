<?php

namespace App\Exports;

use App\Models\Application;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;

class ApplicationsExport implements FromCollection, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Application::all();
    }

    public function map($application): array
    {
//        $data = json_decode($application->data, true);

//        dd($application->data);
        return [
//            $application->data,
//            $application->data['education / التعليم'][0] ?? '',
//            $application->data['experience'][0] ?? '',
//            $application->data['soft_skills'] ?? '',
            $application->data['technical_skills'][0] ?? '',
            ];
    }
}
