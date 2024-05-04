<?php


namespace App\Filament\Pages\Auth;


use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Support\HtmlString;

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
                        Checkbox::make('terms')->required()->label(fn(
                        ) => new HtmlString('I accept the <a href="https://flow.ps/website-terms-of-use/" class="underline" target="_blank">terms of use</a> and <a href="https://flow.ps/privacy-notice/" class="underline" target="_blank">privacy policy</a>')),
                        Checkbox::make('newsletter')->label('Subscribe to our newsletter and promotions')->default(true),
                    ])
                    ->statePath('data'),
            ),
        ];
    }
}
