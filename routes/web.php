<?php

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationController;

Route::get('/applications-export/', [ApplicationController::class, 'export'])->name('applications.export');
