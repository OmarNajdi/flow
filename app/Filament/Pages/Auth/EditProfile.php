<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make(__('Personal Information'))->icon('heroicon-o-user')
                            ->schema([
                                Section::make(__('User Details'))
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
                                Section::make(__('Personal Details'))
                                    ->schema([
                                        DatePicker::make('dob')->label('Date of Birth')->native(false)->required(),
                                        PhoneInput::make('phone')->label('Phone')->required()
                                            ->defaultCountry('PS')
                                            ->preferredCountries(['ps', 'il'])
                                            ->showSelectedDialCode()
                                            ->validateFor()
                                            ->i18n([
                                                'il' => 'Palestine'
                                            ]),
                                        PhoneInput::make('whatsapp')->label('Whatsapp Number')->required()
                                            ->defaultCountry('PS')
                                            ->preferredCountries(['ps', 'il'])
                                            ->showSelectedDialCode()
                                            ->validateFor()
                                            ->i18n([
                                                'il' => 'Palestine'
                                            ]),
                                        Select::make('gender')->options([
                                            'Male'   => 'Male',
                                            'Female' => 'Female'
                                        ])->required(),
                                        Select::make('residence')->label('Governorate of Residence')->options([
                                            'Jenin'         => 'Jenin',
                                            'Tubas'         => 'Tubas',
                                            'Tulkarm'       => 'Tulkarm',
                                            'Nablus'        => 'Nablus',
                                            'Qalqilya'      => 'Qalqilya',
                                            'Salfit'        => 'Salfit',
                                            'Ramallah'      => 'Ramallah and al-Bireh',
                                            'Jericho'       => 'Jericho',
                                            'Jerusalem'     => 'Jerusalem',
                                            'Bethlehem'     => 'Bethlehem',
                                            'Hebron'        => 'Hebron',
                                            'North Gaza'    => 'North Gaza',
                                            'Gaza'          => 'Gaza',
                                            'Deir al-Balah' => 'Deir al-Balah',
                                            'Khan Yunis'    => 'Khan Yunis',
                                            'Rafah'         => 'Rafah',
                                            'Other'         => 'Other',
                                        ])->required()->reactive(),
                                        TextInput::make('residence_other')->label('Other Governorate')
                                            ->hidden(fn(callable $get
                                            ) => $get('residence') !== 'Other')->default(auth()->user()->residence_other),
                                        Select::make('description')->label('Describe Yourself')->options([
                                            'Student'      => 'Student',
                                            'Professional' => 'Professional',
                                            'Entrepreneur' => 'Entrepreneur',
                                            'Other'        => 'Other',
                                        ])->required()->reactive(),
                                        TextInput::make('description_other')->label('Describe Yourself')
                                            ->hidden(fn(callable $get) => $get('description') !== 'Other'),
                                        TextInput::make('occupation')->label('Occupation')->required()
                                            ->hidden(fn(callable $get) => in_array($get('description'),
                                                ['Student', 'Other'])),
                                    ])->columns(2),
                                Section::make(__('Social Profiles'))
                                    ->schema([
                                        TextInput::make('social.linkedin')->label('LinkedIn Profile URL'),
                                        TextInput::make('social.facebook')->label('Facebook Profile URL'),
                                        TextInput::make('social.twitter')->label('Twitter Profile URL'),
                                        TextInput::make('social.instagram')->label('Instagram Profile URL'),
                                        TextInput::make('social.github')->label('Github Profile URL'),
                                        TextInput::make('social.website')->label('Website URL'),
                                    ])->columns(2)
                            ]),
                        Tabs\Tab::make(__('Educational Background'))->icon('heroicon-o-academic-cap')
                            ->schema([
                                Section::make(__('Education'))
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
                                                DatePicker::make('start_date')->label('Start Date')->required(),
                                                Group::make([
                                                    Toggle::make('current')->label('Currently Studying There')->reactive(),
                                                ])->extraAttributes(['class' => 'h-full content-center']),
                                                DatePicker::make('end_date')->label('End Date')
                                                    ->hidden(fn(callable $get) => $get('current')),
                                            ])->columns(3)->reorderableWithButtons()->inlineLabel(false)->hiddenLabel()
                                    ])
                            ]),
                        Tabs\Tab::make(__('Professional Experience'))->icon('heroicon-o-briefcase')
                            ->schema([
                                Section::make(__('Experience'))
                                    ->schema([
                                        Repeater::make('experience')->addActionLabel(__('Add Position'))
                                            ->schema([
                                                Select::make('type')
                                                    ->options([
                                                        'full-time'     => 'Full-time',
                                                        'part-time'     => 'Part-time',
                                                        'internship'    => 'Internship',
                                                        'volunteer'     => 'Volunteer',
                                                        'self-employed' => 'Self-employed',
                                                        'freelance'     => 'Freelance',
                                                    ])
                                                    ->required(),
                                                TextInput::make('company')->label('Company Name')->required(),
                                                TextInput::make('title')->label('Title')->required(),
                                                DatePicker::make('start_date')->label('Start Date')->required(),
                                                Group::make([
                                                    Toggle::make('current')->label('Currently Working There')->reactive(),
                                                ])->extraAttributes(['class' => 'h-full content-center']),
                                                DatePicker::make('end_date')->label('End Date')
                                                    ->hidden(fn(callable $get) => $get('current')),
                                            ])->columns(3)->reorderableWithButtons()->inlineLabel(false)->hiddenLabel()
                                    ]),
                                Section::make(__('Skills'))
                                    ->schema([
                                        TagsInput::make('soft_skills')->label('Please list your Soft Skills')
                                            ->placeholder('Type and press Enter')->splitKeys(['Tab', ',']),
                                        TagsInput::make('technical_skills')->label('Please list your Technical Skills')
                                            ->placeholder('Type and press Enter')->splitKeys(['Tab', ','])
                                    ])
                            ])
                    ])->contained(false)
            ]);
    }
}
