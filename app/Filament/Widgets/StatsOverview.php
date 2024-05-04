<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ApplicationResource;
use App\Filament\Resources\ProgramResource;
use App\Models\Program;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{

    protected function getStats(): array
    {
        return [
            Stat::make('Open Programs',
                Program::query()->whereDate('open_date', '<=', now())->whereDate('close_date', '>=', now())->count())
            ->url(ProgramResource::getUrl('index')),
            Stat::make('Submitted Applications', Program::query()->whereHas('applications')->count())->url(ApplicationResource::getUrl('index')),
            Stat::make('Registered Startups', User::all()->count()),
        ];
    }

}

