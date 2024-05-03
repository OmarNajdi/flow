<?php

namespace App\Filament\Widgets;

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
                Program::query()->whereDate('open_date', '<=', now())->whereDate('close_date', '>=', now())->count()),
            Stat::make('Submitted Applications', Program::query()->whereHas('applications')->count()),
            Stat::make('Registered Startups', User::all()->count()),
        ];
    }

}

