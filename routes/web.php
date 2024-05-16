<?php

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationController;

Route::get('/export/', [ApplicationController::class, 'export']);


Route::get('/mail/',
    function () {
        auth()->user()->notify(new \App\Notifications\ApplicationSubmitted(
            [
                'program'    => 'PIEC',
                'first_name' => 'Omar'
            ]
        ));
    }
);
