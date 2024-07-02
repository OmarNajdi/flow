<?php

namespace App\Filament\Resources\ProgramResource\Pages;

use App\Filament\Resources\ProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewProgram extends ViewRecord
{
    protected static string $resource = ProgramResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('Program Details');
    }
}
