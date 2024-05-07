<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationResource\Pages;
use App\Filament\Resources\ApplicationResource\RelationManagers;
use App\Models\Application;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationGroup = 'Programs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('user_id')->default(auth()->id()),
                Hidden::make('program_id')->default(request('program')),
                Wizard::make([
                    Wizard\Step::make('Personal Questions')->icon('heroicon-s-user')
                        ->schema([
                            TextInput::make('first_name')->label('First Name')->required()->reactive()->default(auth()->user()->first_name)
                                ->afterStateUpdated(fn(Set $set, ?string $state) => $set('first_name',
                                    ucwords($state))),
                            TextInput::make('last_name')->label('Last Name')->required()->reactive()->default(auth()->user()->last_name)
                                ->afterStateUpdated(fn(Set $set, ?string $state) => $set('last_name',
                                    ucwords($state))),
                            TextInput::make('email')->email()->label('Email')->required()->default(auth()->user()->email),
                            DatePicker::make('dob')->label('Date of Birth')->native(false)->required()->default(auth()->user()->dob),
                            TextInput::make('phone')->label('Phone')->required()->default(auth()->user()->phone),
                            TextInput::make('whatsapp')->label('Whatsapp Number')->required()->default(auth()->user()->whatsapp),
                            Select::make('gender')->options([
                                'Male'   => 'Male',
                                'Female' => 'Female'
                            ])->required()->default(auth()->user()->gender),
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
                            ])->required()->default(auth()->user()->residence),
                            Select::make('educational_level')->label('Educational Level')->options([
                                'High School'                 => 'High School',
                                'Vocational/Technical School' => 'Vocational/Technical School',
                                'Bachelor'                    => 'Bachelor\'s Degree',
                                'Master'                      => 'Master\'s Degree',
                                'PhD'                         => 'Doctorate/Ph.D.',
                                'Other'                       => 'Other (Please Specify)',
                            ])->required()->reactive()->default(auth()->user()->educational_level),
                            TextInput::make('educational_level_other')->label('Other Educational Level')
                                ->hidden(fn(callable $get
                                ) => $get('educational_level') !== 'Other')->default(auth()->user()->educational_level_other),
                            Select::make('description')->label('Describe Yourself')->options([
                                'Student'      => 'Student',
                                'Professional' => 'Professional',
                                'Entrepreneur' => 'Entrepreneur',
                                'Other'        => 'Other',
                            ])->required()->reactive()->default(auth()->user()->description),
                            TextInput::make('description_other')->label('Describe Yourself')
                                ->hidden(fn(callable $get
                                ) => $get('description') !== 'Other')->default(auth()->user()->description_other),
                            TextInput::make('occupation')->label('Occupation')->required()
                                ->hidden(fn(callable $get
                                ) => $get('description') === 'Student')->default(auth()->user()->occupation),
                        ])->columns(2),
                    Wizard\Step::make('Idea & Challenges')->icon('heroicon-s-bolt')
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
                            RichEditor::make('idea_description')->label('Please provide a brief description of your idea and what problem it aims to solve. (Please limit your response to 200 words)')
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
                    Wizard\Step::make('Professional and Personal Skills')->icon('heroicon-s-clipboard-document-list')
                        ->schema([
                            RichEditor::make('creative_solution')
                                ->label('Provide an example of a creative solution you developed to address a challenge. What inspired your approach, and what was the outcome? (Please limit your response to 150-200 words)')
                                ->required(),
                            RichEditor::make('random_objects_usage')
                                ->label('You have a box of random objects (rubber bands, Pencils, Tape, Plastic spoons, Bottle caps). How many different uses can you come up with for these items? (Please limit your response to 150-200 words)')
                                ->required(),
                            RichEditor::make('problem_solving_scenario')
                                ->label('Share a scenario where you faced a significant obstacle while working on a project. How did you identify the problem, and what steps did you take to overcome it? (Please limit your response to 150-200 words)')
                                ->required(),
                            RichEditor::make('motivation_participation')
                                ->label('What motivates you to participate in these ideation workshops, and how do you envision applying your passion or interests to generating new ideas or solutions? (Please limit your response to 150-200 words)')
                                ->required(),
                            RichEditor::make('collaboration_experience')
                                ->label('Can you share your experience with collaborating on creative projects or brainstorming sessions? Describe your role and contributions to the team\'s success. (Please limit your response to 150-200 words)')
                                ->required(),
                            RichEditor::make('participation_goals')
                                ->label('What do you hope to achieve by participating in the ideation workshop? Are there specific skills or insights you\'re looking to gain from the experience? (Please limit your response to 150-200 words)')
                                ->required(),
                        ]),
                    Wizard\Step::make('Generic Questions')->icon('heroicon-s-question-mark-circle')
                        ->schema([
                            RichEditor::make('skills_expertise')
                                ->label('Please tell us about your skills and areas of expertise. This could include technical skills such as programming languages, or data analysis techniques, as well as non-technical skills such as communication, problem-solving, project management, or leadership abilities. Feel free to highlight any relevant experiences or accomplishments. (Please limit your response to 150-200 words)')
                                ->required(),
                            Select::make('application_type')->label('Are you applying as an individual or as part of a team?')->options([
                                'Individual' => 'Individual',
                                'Team'       => 'Team',
                                'Other'      => 'Other',
                            ])->required()->reactive(),
                            TextInput::make('application_type_other')->label('Please Specify')
                                ->hidden(fn(callable $get) => $get('application_type') !== 'Other'),
                            Textarea::make('team_members')->label('Please list the names, roles, phone numbers, and emails of your team members.')
                                ->hidden(fn(callable $get) => $get('application_type') !== 'Team'),
                            Select::make('startup_experience')->label('Do you have any knowledge or experience in entrepreneurship/startups?')->options([
                                'Yes' => 'Yes',
                                'No'  => 'No',
                            ])->required()->reactive(),
                            Textarea::make('experience_specification')->label('Please specify your experience:')
                                ->hidden(fn(callable $get) => $get('startup_experience') !== 'Yes'),
                            TextInput::make('new_skill')->label('If you are looking to acquire one new skill, what would it be?')->required(),
                            Select::make('program_discovery')->label('How did you hear about the PIEC Programme?')->options([
                                'Facebook'           => 'Flow Accelerator Facebook Page',
                                'Instagram'          => 'Flow Accelerator Instagram Page',
                                'LinkedIn'           => 'Flow Accelerator LinkedIn Page',
                                'Other Social Media' => 'Other Social Media Channels',
                                'Friend'             => 'Friend/Colleague',
                                'Other'              => 'Other',
                            ])->required()->reactive(),
                            TextInput::make('program_discovery_other')->label('Please Specify')
                                ->hidden(fn(callable $get) => $get('program_discovery') !== 'Other'),
                            Select::make('commitment')->label('Are you able to commit to attending all scheduled related workshops and sessions throughout the innovation challenge over two days?')->options([
                                'Yes'   => 'Yes',
                                'No'    => 'No',
                                'Other' => 'Other',
                            ])->required()->reactive(),
                            TextInput::make('commitment_other')->label('Please Specify')
                                ->hidden(fn(callable $get) => $get('commitment') !== 'Other'),
                            Select::make('continuation_plan')->label('Do you plan to continue working on the idea you develop, by participating in incubation and acceleration programs after the innovation challenge concludes?')->options([
                                'Yes' => 'Yes',
                                'No'  => 'No',
                            ])->required(),
                            RichEditor::make('additional_info')->label('Anything you’d like to share with us? Please share links to any online portfolios, websites, or repositories showcasing your creative work. Briefly describe your role and contributions to each project.'),
                        ]),
                ])->columnSpan(2)->statePath('data'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('program.name')->label('Program'),
                TextColumn::make('status')->label('Status'),
                TextColumn::make('created_at')->label('Submitted at'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->recordUrl(function ($record) {
                if ($record->trashed()) {
                    return null;
                }

                return $record->status === 'draft' ? ApplicationResource::getUrl('edit',
                    [$record]) : ApplicationResource::getUrl('view', [$record]);
            });
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
            'view'   => Pages\ViewApplication::route('/{record}'),
            'edit'   => Pages\EditApplication::route('/{record}/edit'),
        ];
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canView(Model $record): bool
    {
        return $record->user_id === auth()->id();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }
}
