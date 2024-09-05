<?php

namespace App\Exports;

use App\Models\Application;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JobApplicationsExport implements FromCollection, WithMapping, WithHeadings, WithColumnFormatting, WithStyles
{


    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Application::whereNotNull('career_id')->get();
    }

    public function map($application): array
    {

        return [
            $application->created_at ?? '',
            $application->status ?? '',
            $application->data['first_name'] ?? '',
            $application->data['last_name'] ?? '',
            $application->data['email'] ?? '',
            $application->data['dob'] ?? '',
            $application->data['phone'] ?? '',
            $application->data['whatsapp'] ?? '',
            $application->data['gender'] ?? '',
            $application->data['residence'] ?? '',
            $application->data['residence_other'] ?? '',
            $application->data['description'] ?? '',
            $application->data['description_other'] ?? '',
            $application->data['occupation'] ?? '',
            $application->data['education'][0]['degree'] ?? '',
            $application->data['education'][0]['school'] ?? '',
            $application->data['education'][0]['major'] ?? '',
            $application->data['education'][0]['start_date'] ?? '',
            $application->data['education'][0]['current'] ?? '',
            $application->data['education'][0]['end_date'] ?? '',
            $application->data['experience'][0]['type'] ?? '',
            $application->data['experience'][0]['company'] ?? '',
            $application->data['experience'][0]['title'] ?? '',
            $application->data['experience'][0]['start_date'] ?? '',
            $application->data['experience'][0]['current'] ?? '',
            $application->data['experience'][0]['end_date'] ?? '',
            implode(', ', $application->data['soft_skills'] ?? []),
            implode(', ', $application->data['technical_skills'] ?? []),
        ];
    }

    public function headings(): array
    {
        return [
            'Created At',
            'Status',
            'First Name',
            'Last Name',
            'Email',
            'Date of Birth',
            'Phone',
            'Whatsapp',
            'Gender',
            'Residence',
            'Other Governorate',
            'Describe Yourself',
            'Describe Yourself (Other)',
            'Occupation',
            'Degree',
            'School/University',
            'Major/Field of study',
            'Start Date',
            'Currently Studying There',
            'End Date',
            'Experience Type',
            'Company Name',
            'Title',
            'Start Date',
            'Currently Working There',
            'End Date',
            'Soft Skills',
            'Technical Skills',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E'  => "0",
            'F'  => "0",
            'AT' => "0",
        ];

    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'alignment' => ['horizontal' => 'center', 'vertical' => 'center']],
        ];
    }
}
