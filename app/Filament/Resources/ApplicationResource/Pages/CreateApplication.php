<?php

namespace App\Filament\Resources\ApplicationResource\Pages;

use App\Filament\Resources\ApplicationResource;
use App\Filament\Resources\ProgramResource;
use App\Models\Application;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Session;

class CreateApplication extends CreateRecord
{
    protected static string $resource = ApplicationResource::class;

    protected static bool $canCreateAnother = false;

    public static function getCreateLabel(): string
    {
        return 'Submit Application';
    }

    public function getCreateFormAction(): Action
    {
        return Action::make('create')
            ->label('Submit')
            ->submit('create')
            ->keyBindings(['mod+s']);
    }


    protected function authorizeAccess(): void
    {
        $program_id = request('program') ?? Session::get('program_id', request('program'));

        if ( ! $program_id) {
            $this->redirect(ProgramResource::getUrl('index'));
        }

        Session::put('program_id', $program_id);

        $application = Application::where('user_id', auth()->id())->where('program_id', $program_id)->first();

        // If user has an application with this program, redirect to edit page
        if ($application) {
            $this->redirect(ApplicationResource::getUrl('edit', [$application]));
        }
    }

}
