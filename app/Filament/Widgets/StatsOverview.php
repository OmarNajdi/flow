<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ApplicationResource;
use App\Filament\Resources\JobResource;
use App\Filament\Resources\ProgramResource;
use App\Models\Application;
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
                Program::query()->whereDate('open_date', '<=', now())
                    ->whereDate('close_date', '>=', now())
                    ->where('status', 'open')
                    ->count())
                ->label(__('Open Programs'))
                ->url(ProgramResource::getUrl('index'))
                ->extraAttributes(['class' => 'hover:bg-gradient-to-r hover:from-transparent hover:to-green-100 dark:hover:to-[#018578] transition-all hover:scale-105']),
            Stat::make('My Applications', Application::query()->where('user_id', auth()->id())->count())
                ->label(__('My Applications'))
                ->url(ApplicationResource::getUrl('index'))
                ->extraAttributes(['class' => 'hover:bg-gradient-to-r hover:from-transparent hover:to-green-100 dark:hover:to-[#018578] transition-all hover:scale-105']),
            Stat::make('Open Jobs', Job::all()->count())
                ->label(__('Open Jobs'))
                ->url(JobResource::getUrl('index'))
                ->extraAttributes(['class' => 'hover:bg-gradient-to-r hover:from-transparent hover:to-green-100 dark:hover:to-[#018578] transition-all hover:scale-105']),
        ];
    }

}

