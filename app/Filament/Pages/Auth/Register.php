<?php


namespace App\Filament\Pages\Auth;


use Filament\Forms\Components\Component;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Pages\Auth\Register as BaseRegister;

/**
 * @property Form $form
 */
class Register extends BaseRegister
{


    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        TextInput::make('first_name')->label('First Name')->required()->maxLength(255)->autofocus()->reactive()
                            ->afterStateUpdated(fn(Set $set, ?string $state) => $set('first_name', ucwords($state))),
                        TextInput::make('last_name')->label('Last Name')->required()->maxLength(255)->autofocus()->reactive()
                            ->afterStateUpdated(fn(Set $set, ?string $state) => $set('last_name', ucwords($state))),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }
}
