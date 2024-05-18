<?php

namespace App\Filament\Resources\ApplicationResource\Pages;

use App\Filament\Resources\ApplicationResource;
use App\Filament\Resources\ProgramResource;
use App\Models\Application;
use App\Notifications\ApplicationSubmitted;
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
        if ( ! $application) {
            $application = Application::create([
                'user_id'    => auth()->id(),
                'program_id' => $program_id,
                'status'     => 'Draft',
                'data'       => [
                    'first_name'        => auth()->user()->first_name,
                    'last_name'         => auth()->user()->last_name,
                    'email'             => auth()->user()->email,
                    'dob'               => auth()->user()->dob,
                    'phone'             => auth()->user()->phone,
                    'whatsapp'          => auth()->user()->whatsapp,
                    'gender'            => auth()->user()->gender,
                    'residence'         => auth()->user()->residence,
                    'residence_other'   => auth()->user()->residence_other,
                    'description'       => auth()->user()->description,
                    'description_other' => auth()->user()->description_other,
                    'occupation'        => auth()->user()->occupation,
                ]
            ]);
        }
        $this->redirect(ApplicationResource::getUrl('edit', [$application]));
    }

    protected function afterCreate(): void
    {
        $recipient = auth()->user();

        $recipient->notify(
            new ApplicationSubmitted(
                [
                    'program'    => $this->record->program->name,
                    'first_name' => $this->record->data['first_name']
                ]
            )
        );
    }

}
