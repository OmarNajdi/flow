<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ApplicationResource;
use App\Filament\Resources\JobResource;
use App\Filament\Resources\ProgramResource;
use App\Models\Job;
use App\Models\Program;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{

    protected function getStats(): array
    {
        return [
            Stat::make('Open Programs',
                Program::query()->whereDate('open_date', '<=', now())->whereDate('close_date', '>=', now())->count())
                ->url(ProgramResource::getUrl('index'))
                ->extraAttributes(['class' => 'hover:bg-gradient-to-r hover:from-transparent hover:to-green-100']),
            Stat::make('Submitted Applications', Program::query()->whereHas('applications')->count())
                ->url(ApplicationResource::getUrl('index'))
                ->extraAttributes(['class' => 'hover:bg-gradient-to-r hover:from-transparent hover:to-green-100']),
            Stat::make('Open Jobs', Job::all()->count())
                ->url(JobResource::getUrl('index'))
                ->extraAttributes(['class' => 'hover:bg-gradient-to-r hover:from-transparent hover:to-green-100']),
        ];
    }

}

