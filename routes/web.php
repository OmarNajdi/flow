<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationController;

Route::get('/applications-export/', [ApplicationController::class, 'export'])->name('applications.export');
Route::get('/job-applications-export/', [ApplicationController::class, 'export_jobs'])->name('job-applications.export');
Route::get('/users-export/', [UserController::class, 'export'])->name('users.export');
