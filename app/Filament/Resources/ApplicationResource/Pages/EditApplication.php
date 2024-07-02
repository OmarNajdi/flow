<?php

namespace App\Filament\Resources\ApplicationResource\Pages;

use App\Filament\Resources\ApplicationResource;
use App\Models\Application;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;


class EditApplication extends EditRecord
{
    protected static string $resource = ApplicationResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('Apply');
    }

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
