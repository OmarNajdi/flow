<?php

namespace App\Http\Controllers;

use App\Exports\JobApplicationsExport;
use App\Exports\ApplicationsExport;
use Maatwebsite\Excel\Facades\Excel;

class ApplicationController extends Controller
{
    public function export()
    {
        return Excel::download(new ApplicationsExport, 'applications.xlsx');
    }

    public function export_jobs()
    {
        return Excel::download(new JobApplicationsExport, 'job_applications.xlsx');
    }
}
