<?php

namespace App\Filament\Resources\JobApplicationResource\Pages;

use App\Filament\Resources\ApplicationResource;
use App\Filament\Resources\JobApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewJobApplication extends ViewRecord
{
    protected static string $resource = JobApplicationResource::class;

    public function getTitle(): string
    {
        return __('View Job Application');
    }
}
