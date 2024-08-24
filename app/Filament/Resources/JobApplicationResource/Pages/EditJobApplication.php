<?php

namespace App\Filament\Resources\JobApplicationResource\Pages;

use App\Filament\Resources\JobApplicationResource;
use App\Notifications\JobApplicationSubmitted;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditJobApplication extends EditRecord
{
    protected static string $resource = JobApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            Actions\DeleteAction::make(),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return Action::make('update')
            ->label('Submit')
            ->translateLabel()
            ->submit('update')
            ->keyBindings(['mod+s']);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $data['status'] = 'Submitted';
        $record->update($data);

        $recipient = auth()->user();

        $recipient->notify(
            new JobApplicationSubmitted(
                [
                    'job'        => $record->job?->title,
                    'first_name' => $record->data['first_name']
                ]
            )
        );

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return route('filament.admin.pages.dashboard');
    }

    protected function authorizeAccess(): void
    {
        if ($this->record->status != 'Draft') {
            $this->redirect($this->getResource()::getUrl('view', [$this->record]));
        }
    }
}
