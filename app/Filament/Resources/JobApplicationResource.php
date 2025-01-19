<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobApplicationResource\Pages;
use App\Filament\Resources\JobApplicationResource\RelationManagers;
use App\Models\Application;
use App\Models\Job;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class JobApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string
    {
        return __('Jobs');
    }

    public static function getNavigationLabel(): string
    {
        return __('Job Applications');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Job Applications');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Personal Questions')->icon('heroicon-s-user')
                        ->schema([
                            TextInput::make('first_name')->label('First Name')->required()->reactive()->default(auth()->user()->first_name)
                                ->afterStateUpdated(fn(Set $set, ?string $state) => $set('first_name',
                                    ucwords($state))),
                            TextInput::make('last_name')->label('Last Name')->required()->reactive()->default(auth()->user()->last_name)
                                ->afterStateUpdated(fn(Set $set, ?string $state) => $set('last_name',
                                    ucwords($state))),
                            TextInput::make('email')->email()->label('Email')->required()->default(auth()->user()->email)->email(),
                            DatePicker::make('dob')->label('Date of Birth')->native(false)->required()->default(auth()->user()->dob),
                            PhoneInput::make('phone')->label('Phone')->required()->default(auth()->user()->phone)
                                ->defaultCountry('PS')
                                ->preferredCountries(['ps', 'il'])
                                ->showSelectedDialCode()
                                ->validateFor()
                                ->i18n([
                                    'il' => 'Palestine'
                                ]),
                            PhoneInput::make('whatsapp')->label('Whatsapp Number')->required()->default(auth()->user()->whatsapp)
                                ->defaultCountry('PS')
                                ->preferredCountries(['ps', 'il'])
                                ->showSelectedDialCode()
                                ->validateFor()
                                ->i18n([
                                    'il' => 'Palestine'
                                ]),
                            Select::make('gender')->label('Gender')->options([
                                'Male'   => __('Male'),
                                'Female' => __('Female')
                            ])->required()->default(auth()->user()->gender),
                            Select::make('residence')->label('Governorate of Residence')->options([
                                'Jenin'         => __('Jenin'),
                                'Tubas'         => __('Tubas'),
                                'Tulkarm'       => __('Tulkarm'),
                                'Nablus'        => __('Nablus'),
                                'Qalqilya'      => __('Qalqilya'),
                                'Salfit'        => __('Salfit'),
                                'Ramallah'      => __('Ramallah and al-Bireh'),
                                'Jericho'       => __('Jericho'),
                                'Jerusalem'     => __('Jerusalem'),
                                'Bethlehem'     => __('Bethlehem'),
                                'Hebron'        => __('Hebron'),
                                'North Gaza'    => __('North Gaza'),
                                'Gaza'          => __('Gaza'),
                                'Deir al-Balah' => __('Deir al-Balah'),
                                'Khan Yunis'    => __('Khan Yunis'),
                                'Rafah'         => __('Rafah'),
                                'Other'         => __('Other'),
                            ])->required()->reactive()->default(auth()->user()->residence),
                            TextInput::make('residence_other')->label('Other Governorate')
                                ->hidden(fn(callable $get
                                ) => $get('residence') !== 'Other')->default(auth()->user()->residence_other),
                            Select::make('description')->label('Describe Yourself')->options([
                                'Student'      => __('Student'),
                                'Professional' => __('Professional'),
                                'Entrepreneur' => __('Entrepreneur'),
                                'Other'        => __('Other'),
                            ])->required()->reactive()->default(auth()->user()->description),
                            TextInput::make('description_other')->label('Describe Yourself')
                                ->hidden(fn(callable $get
                                ) => $get('description') !== 'Other')->default(auth()->user()->description_other),
                            TextInput::make('occupation')->label('Occupation')->required()
                                ->hidden(fn(callable $get) => in_array($get('description'), ['Student', 'Other']))
                                ->default(auth()->user()->occupation),
                        ])->columns(2)->afterValidation(function (Get $get) use ($form) {
                            $application = $form->getModelInstance();
                            $application->update(
                                [
                                    'data' => array_merge($application->data, [
                                        'first_name'        => $get('first_name'),
                                        'last_name'         => $get('last_name'),
                                        'email'             => $get('email'),
                                        'dob'               => $get('dob'),
                                        'phone'             => $get('phone'),
                                        'whatsapp'          => $get('whatsapp'),
                                        'gender'            => $get('gender'),
                                        'residence'         => $get('residence'),
                                        'residence_other'   => $get('residence_other'),
                                        'description'       => $get('description'),
                                        'description_other' => $get('description_other'),
                                        'occupation'        => $get('occupation'),
                                    ])
                                ]);
                            Notification::make()
                                ->title(__('Saved successfully'))
                                ->success()
                                ->send();
                        }),
                    Wizard\Step::make('Educational Background')->icon('heroicon-o-academic-cap')
                        ->schema([
                            Section::make(__('Education'))
                                ->schema([
                                    Repeater::make('education')
                                        ->schema([
                                            Select::make('degree')
                                                ->options([
                                                    'High School'                 => __('High School'),
                                                    'Vocational/Technical School' => __('Vocational/Technical School'),
                                                    'Bachelor'                    => __('Bachelor\'s Degree'),
                                                    'Master'                      => __('Master\'s Degree'),
                                                    'PhD'                         => __('Doctorate/Ph.D.'),
                                                    'Certification'               => __('Certification'),
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
                                        ])->columns(3)->reorderableWithButtons()->inlineLabel(false)->hiddenLabel()->defaultItems(1)->required()
                                        ->addActionLabel(__('Add'))
                                ])
                        ])->afterValidation(function (Get $get) use ($form) {
                            $application = $form->getModelInstance();
                            $application->update(
                                [
                                    'data' => array_merge($application->data, [
                                        'education' => $get('education'),
                                    ])
                                ]);

                            Notification::make()
                                ->title(__('Saved successfully'))
                                ->success()
                                ->send();
                        }),
                    Wizard\Step::make('Professional Experience')->icon('heroicon-o-briefcase')
                        ->schema([
                            Section::make(__('Experience'))
                                ->schema([
                                    Repeater::make('experience')->addActionLabel(__('Add Position'))
                                        ->schema([
                                            Select::make('type')
                                                ->options([
                                                    'full-time'     => __('Full-time'),
                                                    'part-time'     => __('Part-time'),
                                                    'internship'    => __('Internship'),
                                                    'volunteer'     => __('Volunteer'),
                                                    'self-employed' => __('Self-employed'),
                                                    'freelance'     => __('Freelance'),
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
                                        ])->columns(3)->reorderableWithButtons()->inlineLabel(false)->hiddenLabel()->defaultItems(0)->required(fn(
                                            callable $get
                                        ) => $get('description') !== 'Other')
                                ]),
                            Section::make(__('Skills'))
                                ->schema([
                                    TagsInput::make('soft_skills')->label('Please list your Soft Skills')
                                        ->placeholder('Type and press Enter')->splitKeys([
                                            'Tab', ','
                                        ]),
                                    TagsInput::make('technical_skills')->label('Please list your Technical Skills')
                                        ->placeholder('Type and press Enter')->splitKeys([
                                            'Tab', ','
                                        ])
                                ])
                        ])->afterValidation(function (Get $get) use ($form) {
                            $application = $form->getModelInstance();
                            $application->update(
                                [
                                    'data' => array_merge($application->data, [
                                        'experience'       => $get('experience'),
                                        'soft_skills'      => $get('soft_skills'),
                                        'technical_skills' => $get('technical_skills'),
                                    ])
                                ]);

                            Notification::make()
                                ->title(__('Saved successfully'))
                                ->success()
                                ->send();
                        }),
                    Wizard\Step::make('Documents')->icon('heroicon-o-briefcase')
                        ->schema([
                            FileUpload::make('cv')->label('Upload your CV')->required()
                                ->multiple()->appendFiles()->maxFiles(1)->maxSize(10240)->directory('job-attachments'),
                            FileUpload::make('cover_letter')->label('Upload your Cover Letter')->required()
                                ->multiple()->appendFiles()->maxFiles(1)->maxSize(10240)->directory('job-attachments')
                        ]),
                ])->columnSpan(2)->statePath('data')->nextAction(
                    fn(\Filament\Forms\Components\Actions\Action $action
                    ) => $action->label('Save and Continue')->translateLabel(),
                ),
            ]);
    }

    public static function table(Table $table): Table
    {

        $columns = [
            TextColumn::make('job.title')->label('Job Title')->translateLabel(),
            TextColumn::make('data.first_name')->label('First Name'),
            TextColumn::make('data.last_name')->label('Last Name'),
            TextColumn::make('data.email')->label('Email')->translateLabel(),
            TextColumn::make('status')->label('Status')
                ->getStateUsing(fn($record) => match ($record->status) {
                    // ToDo: Fix isFuture to include current day
                    'Draft' => $record->job->close_date->isFuture() ? 'Draft' : 'Incomplete',
                    default => $record->status
                })
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    'Submitted' => 'success',
                    'Incomplete' => 'danger',
                    'Draft' => 'info',
                    'In Review' => 'warning',
                    'Decision Made' => 'gray',
                    default => 'gray',
                })
                ->sortable(),
        ];

        $header_actions = auth()->id() <= 6 ? [
            Tables\Actions\Action::make('Export')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(route('job-applications.export'))
                ->translateLabel()
        ] : [];

        return $table
            ->columns($columns)
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])->recordUrl(function ($record) {
                if ($record->trashed()) {
                    return null;
                }

                // ToDo: Fix isFuture to include current day
                return $record->status === 'Draft' && $record->job->close_date->isFuture() ? JobApplicationResource::getUrl('edit',
                    [$record]) : JobApplicationResource::getUrl('view', [$record]);
            })
            ->headerActions($header_actions);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make(__('Personal Questions'))
                    ->schema([
                        TextEntry::make('data.first_name')->label('First Name'),
                        TextEntry::make('data.last_name')->label('Last Name'),
                        TextEntry::make('data.email')->label('Email'),
                        TextEntry::make('data.dob')->label('Date of Birth'),
                        TextEntry::make('data.phone')->label('Phone'),
                        TextEntry::make('data.whatsapp')->label('Whatsapp'),
                        TextEntry::make('data.gender')->label('Gender'),
                        TextEntry::make('data.residence')->label('Residence'),
                        TextEntry::make('data.residence_other')->label('Other Governorate'),
                        TextEntry::make('data.description')->label('Describe Yourself'),
                        TextEntry::make('data.description_other')->label('Describe Yourself'),
                        TextEntry::make('data.occupation')->label('Occupation'),
                    ])->columns(3),
                \Filament\Infolists\Components\Section::make(__('Educational Background'))
                    ->schema([
                        RepeatableEntry::make('data.education')->label('Education')
                            ->schema([
                                TextEntry::make('degree')->label('Degree'),
                                TextEntry::make('school')->label('School/University'),
                                TextEntry::make('major')->label('Major/Field of study'),
                                TextEntry::make('start_date')->label('Start Date'),
                                TextEntry::make('current')->label('Currently Studying There'),
                                TextEntry::make('end_date')->label('End Date'),
                            ])->columns(3),
                    ])->columns(1),
                \Filament\Infolists\Components\Section::make(__('Professional Experience'))
                    ->schema([
                        RepeatableEntry::make('data.experience')->label('Experience')
                            ->schema([
                                TextEntry::make('type')->label('Type'),
                                TextEntry::make('company')->label('Company Name'),
                                TextEntry::make('title')->label('Title'),
                                TextEntry::make('start_date')->label('Start Date'),
                                TextEntry::make('current')->label('Currently Working There'),
                                TextEntry::make('end_date')->label('End Date'),
                            ])->columns(3),

                        TextEntry::make('data.soft_skills')->label('Please list your Soft Skills'),
                        TextEntry::make('data.technical_skills')->label('Please list your Technical Skills'),
                    ])->columns(1),
                \Filament\Infolists\Components\Section::make(__('Documents'))
                    ->schema([
                        RepeatableEntry::make('data.cv')->label('CV')
                            ->schema([
                                TextEntry::make('')->formatStateUsing(fn(string $state
                                ): HtmlString => new HtmlString("<a href='".Storage::url($state)."' download>".__('Download CV')."</a>"))
                            ])->columns(1),
                        RepeatableEntry::make('data.cover_letter')->label('Cover Letter')
                            ->schema([
                                TextEntry::make('')->formatStateUsing(fn(string $state
                                ): HtmlString => new HtmlString("<a href='".Storage::url($state)."' download>".__('Download Cover Letter')."</a>"))
                            ])->columns(1),
                    ])->columns(1),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListJobApplications::route('/'),
            'create' => Pages\CreateJobApplication::route('/create'),
            'view'   => Pages\ViewJobApplication::route('/{record}'),
            'edit'   => Pages\EditJobApplication::route('/{record}/edit'),
        ];
    }

    public static function canEdit(Model $record): bool
    {
        return $record->status === 'Draft' && ($record->user_id === auth()->id() || auth()->id() <= 6);
    }

    public static function canView(Model $record): bool
    {
        return $record->user_id === auth()->id() || auth()->id() <= 6;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->id() <= 6;
    }

    public static function getEloquentQuery(): Builder
    {
        return auth()->id() <= 6
            ? parent::getEloquentQuery()->whereRelation('job', function ($query) {
                $query->whereIn('status', ['open', 'draft']);
            }) : parent::getEloquentQuery()->where('user_id', auth()->id())->whereRelation('job', 'status', 'open');
    }

}
