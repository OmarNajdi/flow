<?php

namespace App\Http\Controllers;

use App\Exports\JobApplicationsExport;
use App\Exports\ApplicationsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ApplicationController extends Controller
{
    public function export(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'program_id'    => 'required|exists:programs,id',
            'program_level' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect('programs');
        }

        return Excel::download(new ApplicationsExport(id: $request->program_id, level: $request->program_level),
            'applications.xlsx');
    }

    public function export_jobs()
    {
        return Excel::download(new JobApplicationsExport, 'job_applications.xlsx');
    }
}
