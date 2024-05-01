<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationResource\Pages;
use App\Filament\Resources\ApplicationResource\RelationManagers;
use App\Models\Application;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Personal Questions ')
                        ->schema([
                            TextInput::make('first_name')->label('First Name')->required(),
                            TextInput::make('last_name')->label('Last Name')->required(),
                            TextInput::make('email')->label('Email')->required(),
                            DatePicker::make('dob')->label('Date of Birth')->native(false)->required(),
                            TextInput::make('phone')->label('Phone')->required(),
                            TextInput::make('whatsapp')->label('Whatsapp Number')->required(),
                            Select::make('gender')->options([
                                'Male'   => 'Male',
                                'Female' => 'Female'
                            ])->required(),
                            Select::make('residence')->label('Governorate of Residence')->options([
                                'Jenin'                 => 'Jenin / جنين',
                                'Tubas'                 => 'Tubas',
                                'Tulkarm'               => 'Tulkarm',
                                'Nablus'                => 'Nablus',
                                'Qalqilya'              => 'Qalqilya',
                                'Salfit'                => 'Salfit',
                                'Ramallah and al-Bireh' => 'Ramallah and al-Bireh',
                                'Jericho'               => 'Jericho',
                                'Jerusalem'             => 'Jerusalem',
                                'Bethlehem'             => 'Bethlehem',
                                'Hebron'                => 'Hebron',
                                'North Gaza'            => 'North Gaza',
                                'Gaza'                  => 'Gaza',
                                'Deir al-Balah'         => 'Deir al-Balah',
                                'Khan Yunis'            => 'Khan Yunis',
                                'Rafah'                 => 'Rafah',
                            ])->required(),
                            Select::make('educational_level')->label('Educational Level')->options([
                                'High School'                 => 'High School',
                                'Vocational/Technical School' => 'Vocational/Technical School',
                                'Bachelor'                    => 'Bachelor\'s Degree',
                                'Master'                      => 'Master\'s Degree',
                                'PhD'                         => 'Doctorate/Ph.D.',
                                'Other'                       => 'Other (Please Specify)',
                            ])->required()->reactive(),
                            TextInput::make('educational_level_other')->label('Other Educational Level')
                                ->hidden(fn(callable $get) => $get('educational_level') !== 'Other'),
                            Select::make('description')->label('Describe Yourself')->options([
                                'Student'      => 'Student',
                                'Professional' => 'Professional',
                                'Entrepreneur' => 'Entrepreneur',
                                'Other'        => 'Other',
                            ])->required()->reactive(),
                            TextInput::make('description_other')->label('Describe Yourself')
                                ->hidden(fn(callable $get) => $get('description') !== 'Other'),
                            TextInput::make('ocuppation')->label('Occupation')->required()
                                ->hidden(fn(callable $get) => $get('description') === 'Student'),
                        ])->columns(2),
                    Wizard\Step::make('Idea & Challenges')
                        ->schema([
                            Select::make('has_idea')->label('Do you currently have a business idea or project?')->options([
                                'Yes' => 'Yes',
                                'No'  => 'No',
                            ])->required()->reactive(),
                            Select::make('idea_stage')->label('In which stage is your idea?')->options([
                                'Idea Phase'                   => 'Idea Phase',
                                'Proof of Concept'             => 'Proof of Concept',
                                'Minimum Viable Product (MVP)' => 'Minimum Viable Product (MVP)',
                                'Market-ready'                 => 'Market-ready',
                            ])->required()->reactive()->hidden(fn(callable $get) => $get('has_idea') !== 'Yes'),
                            Textarea::make('idea_description')->label('Please provide a brief description of your idea and what problem it aims to solve. (Please limit your response to 200 words)')
                                ->required()->hidden(fn(callable $get) => $get('has_idea') !== 'Yes'),
                            Select::make('uses_ai')->label('Does your business idea or project utilize Artificial Intelligence (AI)?')->options([
                                'Yes' => 'Yes',
                                'No'  => 'No',
                            ])->required()->reactive()->hidden(fn(callable $get) => $get('has_idea') !== 'Yes'),
                            Textarea::make('ai_role')->label('How do you envision Artificial Intelligence playing a role in your solution?')->required()
                                ->hidden(fn(callable $get) => $get('has_idea') !== 'Yes' || $get('uses_ai') !== 'Yes'),
                            Textarea::make('ai_future_plan')->label('How do you plan to incorporate AI or technological innovation into your project in the future?')
                                ->required()->hidden(fn(callable $get
                                ) => $get('has_idea') !== 'Yes' || $get('uses_ai') !== 'No'),
                            Select::make('has_challenge')->label('Do you have a specific challenge you would solve with Artificial Intelligence (AI)?')->options([
                                'Yes' => 'Yes',
                                'No'  => 'No',
                            ])->required()->reactive()->hidden(fn(callable $get) => $get('has_idea') !== 'No'),
                            Textarea::make('challenge_description')->label('What specific challenge would you like to solve, and how would you use Artificial Intelligence (AI) to address it')
                                ->required()->hidden(fn(callable $get
                                ) => $get('has_challenge') !== 'Yes' || $get('has_idea') !== 'No'),
                        ]),
                    Wizard\Step::make('Professional and Personal Skills')
                        ->schema([
                            // ...
                        ]),
                    Wizard\Step::make('Generic Questions')
                        ->schema([
                            // ...
                        ]),
                ])->columnSpan(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('program.name')->label('Program'),
                TextColumn::make('status')->label('Status'),
                TextColumn::make('created_at')->label('Created At'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListApplications::route('/'),
            'create' => Pages\CreateApplication::route('/create'),
            'edit'   => Pages\EditApplication::route('/{record}/edit'),
        ];
    }

}
