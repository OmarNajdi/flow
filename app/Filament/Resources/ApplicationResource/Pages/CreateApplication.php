<?php

namespace App\Filament\Resources\ApplicationResource\Pages;

use App\Filament\Resources\ApplicationResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateApplication extends CreateRecord
{
    protected static string $resource = ApplicationResource::class;

    protected static bool $canCreateAnother = false;

    public static function getCreateLabel(): string
    {
        return 'Submit Application';
    }

    protected function getCreateFormAction(): Action
    {
        return Action::make('create')
            ->label('Submit')
            ->submit('create')
            ->keyBindings(['mod+s']);
    }

}
