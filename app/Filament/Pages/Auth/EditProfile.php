<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Personal Information')
                            ->schema([
                                Section::make('User Details')
                                    ->schema([
                                        TextInput::make('first_name')->label('First Name')->required()->maxLength(255)->autofocus()->reactive()
                                            ->afterStateUpdated(fn(Set $set, ?string $state) => $set('first_name',
                                                ucwords($state))),
                                        TextInput::make('last_name')->label('Last Name')->required()->maxLength(255)->autofocus()->reactive()
                                            ->afterStateUpdated(fn(Set $set, ?string $state) => $set('last_name',
                                                ucwords($state))),
                                        $this->getEmailFormComponent(),
                                        $this->getPasswordFormComponent(),
                                        $this->getPasswordConfirmationFormComponent(),
                                    ])->columns(2),
                                Section::make('Personal Details')
                                    ->schema([
                                        DatePicker::make('dob')->label('Date of Birth')->native(false)->required(),
                                        TextInput::make('phone')->label('Phone')->required(),
                                        TextInput::make('whatsapp')->label('Whatsapp Number')->required(),
                                        Select::make('gender')->options([
                                            'Male'   => 'Male',
                                            'Female' => 'Female'
                                        ])->required(),
                                        Select::make('residence')->label('Governorate of Residence')->options([
                                            'Jenin'                 => 'Jenin',
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
                                        TextInput::make('occupation')->label('Occupation')->required()
                                            ->hidden(fn(callable $get) => $get('description') === 'Student'),
                                    ])->columns(2),
                                Section::make('Social Profiles')
                                    ->schema([
                                        TextInput::make('social.linkedin')->label('LinkedIn Profile URL'),
                                        TextInput::make('social.facebook')->label('Facebook Profile URL'),
                                        TextInput::make('social.twitter')->label('Twitter Profile URL'),
                                        TextInput::make('social.instagram')->label('Instagram Profile URL'),
                                        TextInput::make('social.github')->label('Github Profile URL'),
                                        TextInput::make('social.website')->label('Website URL'),
                                    ])->columns(2)
                            ]),
                        Tabs\Tab::make('Professional Background')
                            ->schema([
                                Section::make('Education')
                                    ->schema([
                                        Repeater::make('education')
                                            ->schema([
                                                Select::make('degree')
                                                    ->options([
                                                        'High School'                 => 'High School',
                                                        'Vocational/Technical School' => 'Vocational/Technical School',
                                                        'Bachelor'                    => 'Bachelor\'s Degree',
                                                        'Master'                      => 'Master\'s Degree',
                                                        'PhD'                         => 'Doctorate/Ph.D.',
                                                        'Certification'               => 'Certification',
                                                    ])
                                                    ->required(),
                                                TextInput::make('school')->label('School/University')->required(),
                                                TextInput::make('major')->label('Major/Field of study')->required(),
                                                DatePicker::make('start_date')->label('Start Date')->required()->extraInputAttributes(['type' => 'month']),
                                                Group::make([
                                                    Toggle::make('current')->label('Currently Studying There')->reactive(),
                                                ])->extraAttributes(['class' => 'h-full content-center']),
                                                DatePicker::make('end_date')->label('End Date')->extraInputAttributes(['type' => 'month'])
                                                    ->hidden(fn(callable $get) => $get('current')),

                                            ])->columns(3)->reorderableWithButtons()->inlineLabel(false)->hiddenLabel()
                                    ]),
                                Section::make('Soft Skills')
                                    ->schema([
                                        Repeater::make('skills')
                                            ->schema([
                                                TextInput::make('skill')->label('Skill')->required()
                                            ])->reorderableWithButtons()->inlineLabel(false)->hiddenLabel()
                                    ])
                            ])
                    ])->contained(false)
            ]);
    }
}
