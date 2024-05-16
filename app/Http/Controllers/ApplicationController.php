<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\ApplicationsExport;
use Maatwebsite\Excel\Facades\Excel;

class ApplicationController extends Controller
{
    public function export()
    {
        return Excel::download(new ApplicationsExport, 'applications.xlsx');
    }
}
