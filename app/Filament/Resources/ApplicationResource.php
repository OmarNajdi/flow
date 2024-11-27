<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationResource\Pages;
use App\Filament\Resources\ApplicationResource\RelationManagers;
use App\Models\Application;
use App\Models\Program;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('Programs');
    }

    public static function getNavigationLabel(): string
    {
        return __('Applications');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Applications');
    }

    public static function form(Form $form): Form
    {
        $ideation_from = [
            Wizard\Step::make('Idea & Challenges')->icon('heroicon-s-bolt')
                ->schema([
                    Select::make('has_idea')->label('Do you currently have a business idea or project?')->options([
                        'Yes' => __('Yes'),
                        'No'  => __('No'),
                    ])->required()->reactive(),
                    Select::make('circular_economy')->label(fn(Application $record
                    ): string => 'Is your business idea or project focused on a specific sector within the '.optional($record->program)->activity.'?')->options([
                        'Yes' => __('Yes'),
                        'No'  => __('No'),
                    ])->required()->hidden(fn(callable $get) => $get('has_idea') !== 'Yes'),
                    Select::make('idea_stage')->label('In which stage is your idea?')->options([
                        'Idea Phase'                   => __('Idea Phase'),
                        'Proof of Concept'             => __('Proof of Concept'),
                        'Minimum Viable Product (MVP)' => __('Minimum Viable Product (MVP)'),
                        'Market-ready'                 => __('Market-ready'),
                    ])->required()->reactive()->hidden(fn(callable $get) => $get('has_idea') !== 'Yes'),
                    RichEditor::make('idea_sector')->label('Which sector is it, and what specific problem or challenge does your idea aim to address? (Please limit your response to 150 words.)')
                        ->required()->hidden(fn(callable $get) => $get('has_idea') !== 'Yes'),
                    RichEditor::make('idea_description')->label('Please provide a brief description of your idea (Please limit your response to 200 words)')
                        ->required()->hidden(fn(callable $get) => $get('has_idea') !== 'Yes'),
                    Select::make('has_challenge')
                        ->label(fn(Application $record
                        ): string => 'Do you have a specific challenge you aim to solve within the '.optional($record->program)->activity.' sectors?')
                        ->options([
                            'Yes' => __('Yes'),
                            'No'  => __('No'),
                        ])->required()->reactive()->hidden(fn(callable $get) => $get('has_idea') !== 'No'),
                    Textarea::make('challenge_description')->label('Which sector is it, and what specific challenge would you like to solve?')
                        ->required()->hidden(fn(callable $get
                        ) => $get('has_challenge') !== 'Yes' || $get('has_idea') !== 'No'),
                ])->afterValidation(function (Get $get) use ($form) {
                    $application = $form->getModelInstance();
                    $application->update(
                        [
                            'data' => array_merge($application->data, [
                                'has_idea'              => $get('has_idea'),
                                'circular_economy'      => $get('circular_economy'),
                                'idea_stage'            => $get('idea_stage'),
                                'idea_sector'           => $get('idea_sector'),
                                'idea_description'      => $get('idea_description'),
                                'has_challenge'         => $get('has_challenge'),
                                'challenge_description' => $get('challenge_description'),
                            ])
                        ]);
                    Notification::make()
                        ->title(__('Saved successfully'))
                        ->success()
                        ->send();
                }),
            Wizard\Step::make('Entrepreneurial Skills')->icon('heroicon-s-clipboard-document-list')
                ->schema([
                    RichEditor::make('creative_solution')
                        ->label('Provide an example of a creative solution you developed to address a challenge. What inspired your approach, and what was the outcome? (Please limit your response to 150-200 words)')
                        ->required(),
                    RichEditor::make('problem_solving_scenario')
                        ->label('Share a scenario where you faced a significant obstacle while working on a project. How did you identify the problem, and what steps did you take to overcome it? (Please limit your response to 150-200 words)')
                        ->required(),
                    RichEditor::make('participation_goals')
                        ->label('What do you hope to achieve by participating in the ideation workshop? Are there specific skills or insights you\'re looking to gain from the experience? (Please limit your response to 150-200 words)')
                        ->required(),
                ])->afterValidation(function (Get $get) use ($form) {
                    $application = $form->getModelInstance();
                    $application->update(
                        [
                            'data' => array_merge($application->data, [
                                'creative_solution'        => $get('creative_solution'),
                                'problem_solving_scenario' => $get('problem_solving_scenario'),
                                'participation_goals'      => $get('participation_goals'),
                            ])
                        ]);

                    Notification::make()
                        ->title(__('Saved successfully'))
                        ->success()
                        ->send();
                }),
            Wizard\Step::make('Generic Questions')->icon('heroicon-s-question-mark-circle')
                ->schema([
                    RichEditor::make('skills_expertise')
                        ->label('Please tell us about your skills and areas of expertise. This could include technical skills such as programming languages, or data analysis techniques, as well as non-technical skills such as communication, problem-solving, project management, or leadership abilities. Feel free to highlight any relevant experiences or accomplishments. (Please limit your response to 150-200 words)')
                        ->required(),
                    Select::make('individual_or_team')->label('Are you applying as an individual or as part of a team?')->options([
                        'Individual' => __('Individual'),
                        'Team'       => __('Team'),
                    ])->required()->reactive(),
                    TextInput::make('team_count')->label('How many team members will participate in the problem-solving workshop?')
                        ->numeric()
                        ->minValue(1)
                        ->required()
                        ->hidden(fn(callable $get) => $get('individual_or_team') !== 'Team'),
                    Repeater::make('team_members')->label('Team Members')->addActionLabel(__('Add Team Member'))
                        ->schema([
                            TextInput::make('name')->label('Name')->required(),
                            TextInput::make('role')->label('Role')->required(),
                            PhoneInput::make('phone')->label('Phone')->required()->default(auth()->user()->phone)
                                ->defaultCountry('PS')
                                ->preferredCountries(['ps', 'il'])
                                ->showSelectedDialCode()
                                ->validateFor()
                                ->i18n([
                                    'il' => 'Palestine'
                                ]),
                            TextInput::make('email')->label('Email')->required()->email(),
                        ])->columns(4)->reorderableWithButtons()->inlineLabel(false)->required()
                        ->hidden(fn(callable $get) => $get('individual_or_team') !== 'Team'),
                    Select::make('startup_experience')->label('Do you have any knowledge or experience in entrepreneurship/startups?')->options([
                        'Yes' => __('Yes'),
                        'No'  => __('No'),
                    ])->required()->reactive(),
                    Textarea::make('experience_specification')->label('Please specify your experience')
                        ->hidden(fn(callable $get) => $get('startup_experience') !== 'Yes'),
                    TextInput::make('new_skill')->label('If you are looking to acquire one new skill, what would it be?')->required(),
                    Select::make('program_discovery')->label('How did you hear about the PIEC Programme?')->options([
                        'Facebook'           => __('Facebook'),
                        'Instagram'          => __('Instagram'),
                        'LinkedIn'           => __('LinkedIn'),
                        'Other Social Media' => __('Other Social Media Channels'),
                        'Friend'             => __('Friend/Colleague'),
                        'Other'              => __('Other'),
                    ])->required()->reactive(),
                    TextInput::make('program_discovery_other')->label('Please Specify')
                        ->hidden(fn(callable $get) => $get('program_discovery') !== 'Other'),
                    Select::make('commitment')->label('Are you able to commit to attending all scheduled related workshops and sessions throughout the problem solving workshop over three days?')->options([
                        'Yes'   => __('Yes'),
                        'No'    => __('No'),
                        'Other' => __('Other'),
                    ])->required()->reactive(),
                    TextInput::make('commitment_other')->label('Please Specify')
                        ->hidden(fn(callable $get) => $get('commitment') !== 'Other'),
                    Select::make('continuation_plan')->label('Do you plan to continue working on the idea you develop, by participating in incubation and acceleration programs after the innovation challenge concludes?')->options([
                        'Yes' => __('Yes'),
                        'No'  => __('No'),
                    ])->required(),
                    RichEditor::make('additional_info')->label('Anything you\'d like to share with us? Please share links to any online portfolios, websites, or repositories showcasing your creative work. Briefly describe your role and contributions to each project'),
                ])->afterValidation(function (Get $get) use ($form) {
                    $application = $form->getModelInstance();
                    $application->update(
                        [
                            'data' => array_merge($application->data, [
                                'skills_expertise'         => $get('skills_expertise'),
                                'individual_or_team'       => $get('individual_or_team'),
                                'team_count'               => $get('team_count'),
                                'team_members'             => $get('team_members'),
                                'startup_experience'       => $get('startup_experience'),
                                'experience_specification' => $get('experience_specification'),
                                'new_skill'                => $get('new_skill'),
                                'program_discovery'        => $get('program_discovery'),
                                'program_discovery_other'  => $get('program_discovery_other'),
                                'commitment'               => $get('commitment'),
                                'commitment_other'         => $get('commitment_other'),
                                'continuation_plan'        => $get('continuation_plan'),
                                'additional_info'          => $get('additional_info'),
                            ])
                        ]);

                    Notification::make()
                        ->title(__('Saved successfully'))
                        ->success()
                        ->send();
                }),
            Wizard\Step::make('Review')->icon('heroicon-s-check-circle')
                ->schema([
                    Placeholder::make('review_section')->hiddenLabel()->content(
                        new HtmlString('<div style="font-size: 24px;text-align: center">'.__("Please review your application before submitting").'</div>')
                    ),
                    Section::make(__('Personal Information'))
                        ->schema([
                            Placeholder::make('review_first_name')->label('First Name')
                                ->content(fn(Application $record): string => $record->data['first_name'] ?? ''),
                            Placeholder::make('review_last_name')->label('Last Name')
                                ->content(fn(Application $record): string => $record->data['last_name'] ?? ''),
                            Placeholder::make('review_email')->label('Email')
                                ->content(fn(Application $record): string => $record->data['email'] ?? ''),
                            Placeholder::make('review_dob')->label('Date of Birth')
                                ->content(fn(Application $record): string => $record->data['dob'] ?? ''),
                            Placeholder::make('review_phone')->label('Phone')
                                ->content(fn(Application $record): string => $record->data['phone'] ?? ''),
                            Placeholder::make('review_whatsapp')->label('Whatsapp')
                                ->content(fn(Application $record): string => $record->data['whatsapp'] ?? ''),
                            Placeholder::make('review_gender')->label('Gender')
                                ->content(fn(Application $record): string => $record->data['gender'] ?? ''),
                            Placeholder::make('review_residence')->label('Governorate of Residence')
                                ->content(fn(Application $record): string => $record->data['residence'] ?? ''),
                            Placeholder::make('review_residence_other')->label('Other Governorate')
                                ->content(fn(Application $record
                                ): string => $record->data['residence_other'] ?? ''),
                            Placeholder::make('review_description')->label('Describe Yourself')
                                ->content(fn(Application $record
                                ): string => $record->data['description'] ?? ''),
                            Placeholder::make('review_description_other')->label('Describe Yourself (Other)')
                                ->content(fn(Application $record
                                ): string => $record->data['description_other'] ?? ''),
                            Placeholder::make('review_occupation')->label('Occupation')
                                ->content(fn(Application $record): string => $record->data['occupation'] ?? ''),
                        ])->columns(3),
                    Section::make(__('Idea & Challenges'))
                        ->schema([
                            Placeholder::make('review_has_idea')->label('Do you currently have a business idea or project?')
                                ->content(fn(Application $record): string => $record->data['has_idea'] ?? ''),
                            Placeholder::make('review_circular_economy')->label(fn(Application $record
                            ): string => 'Is your business idea or project focused on a specific sector within the '.optional($record->program)->activity.'?')
                                ->content(fn(Application $record
                                ): string => $record->data['circular_economy'] ?? ''),
                            Placeholder::make('review_idea_stage')->label('In which stage is your idea?')
                                ->content(fn(Application $record): string => $record->data['idea_stage'] ?? ''),
                            Placeholder::make('review_idea_sector')->label('Which sector is it, and what specific problem or challenge does your idea aim to address? (Please limit your response to 150 words.)')
                                ->content(fn(Application $record
                                ): HtmlString => new HtmlString($record->data['idea_sector'] ?? '')),
                            Placeholder::make('review_idea_description')->label('Please provide a brief description of your idea')
                                ->content(fn(Application $record
                                ): HtmlString => new HtmlString($record->data['idea_description'] ?? '')),
                            Placeholder::make('review_has_challenge')->label(fn(Application $record
                            ): string => 'Do you have a specific challenge you aim to solve within the '.optional($record->program)->activity.' sectors?')
                                ->content(fn(Application $record
                                ): string => $record->data['has_challenge'] ?? ''),
                            Placeholder::make('review_challenge_description')->label('Which sector is it, and what specific challenge would you like to solve?')
                                ->content(fn(Application $record
                                ): string => $record->data['challenge_description'] ?? ''),
                        ]),
                    Section::make(__('Entrepreneurial Skills'))
                        ->schema([
                            Placeholder::make('review_creative_solution')->label('Provide an example of a creative solution you developed to address a challenge. What inspired your approach, and what was the outcome?')
                                ->content(fn(Application $record
                                ): HtmlString => new HtmlString($record->data['creative_solution'] ?? '')),
                            Placeholder::make('review_problem_solving_scenario')->label('Share a scenario where you faced a significant obstacle while working on a project. How did you identify the problem, and what steps did you take to overcome it?')
                                ->content(fn(Application $record
                                ): HtmlString => new HtmlString($record->data['problem_solving_scenario'] ?? '')),
                            Placeholder::make('review_participation_goals')->label('What do you hope to achieve by participating in the ideation workshop? Are there specific skills or insights you\'re looking to gain from the experience?')
                                ->content(fn(Application $record
                                ): HtmlString => new HtmlString($record->data['participation_goals'] ?? '')),
                        ]),
                    Section::make(__('Generic Questions'))
                        ->schema([
                            Placeholder::make('review_skills_expertise')->label('Please tell us about your skills and areas of expertise. This could include technical skills such as programming languages, or data analysis techniques, as well as non-technical skills such as communication, problem-solving, project management, or leadership abilities. Feel free to highlight any relevant experiences or accomplishments.')
                                ->content(fn(Application $record
                                ): HtmlString => new HtmlString($record->data['skills_expertise'] ?? '')),
                            Placeholder::make('review_individual_or_team')->label('Are you applying as an individual or as part of a team?')
                                ->content(fn(Application $record): string => $record->data['individual_or_team'] ?? ''),
                            Placeholder::make('review_team_count')->label('How many team members will participate in the problem-solving workshop?')
                                ->content(fn(Application $record): string => $record->data['team_count'] ?? ''),
                            Placeholder::make('review_startup_experience')->label('Do you have any knowledge or experience in entrepreneurship/startups?')
                                ->content(fn(Application $record
                                ): string => $record->data['startup_experience'] ?? ''),
                            Placeholder::make('review_experience_specification')->label('Please specify your experience')
                                ->content(fn(Application $record
                                ): string => $record->data['experience_specification'] ?? ''),
                            Placeholder::make('review_new_skill')->label('If you are looking to acquire one new skill, what would it be?')
                                ->content(fn(Application $record): string => $record->data['new_skill'] ?? ''),
                            Placeholder::make('review_program_discovery')->label('How did you hear about the PIEC Programme?')
                                ->content(fn(Application $record
                                ): string => $record->data['program_discovery'] ?? ''),
                            Placeholder::make('review_program_discovery_other')->label('Please Specify')
                                ->content(fn(Application $record
                                ): string => $record->data['program_discovery_other'] ?? ''),
                            Placeholder::make('review_commitment')->label('Are you able to commit to attending all scheduled related workshops and sessions throughout the problem solving workshop over three days?')
                                ->content(fn(Application $record): string => $record->data['commitment'] ?? ''),
                            Placeholder::make('review_commitment_other')->label('Please Specify')
                                ->content(fn(Application $record
                                ): string => $record->data['commitment_other'] ?? ''),
                            Placeholder::make('review_continuation_plan')->label('Do you plan to continue working on the idea you develop, by participating in incubation and acceleration programs after the innovation challenge concludes?')
                                ->content(fn(Application $record
                                ): string => $record->data['continuation_plan'] ?? ''),
                            Placeholder::make('review_additional_info')->label('Anything you\'d like to share with us? Please share links to any online portfolios, websites, or repositories showcasing your creative work. Briefly describe your role and contributions to each project')
                                ->content(fn(Application $record
                                ): HtmlString => new HtmlString($record->data['additional_info'] ?? ''))
                        ])
                ])
        ];

        $pre_incubation_form = [
            Wizard\Step::make('Problem & Need')->icon('heroicon-s-bolt')
                ->schema([
                    RichEditor::make('problem')->label('What specific problem or need does your startup address?')->required(),
                    RichEditor::make('target')->label('Who is affected by this problem? And who’s your target segment?')->required(),
                    RichEditor::make('identify')->label('How did you identify this problem?')->required(),
                ])->afterValidation(function (Get $get) use ($form) {
                    $application = $form->getModelInstance();
                    $application->update(
                        [
                            'data' => array_merge($application->data, [
                                'problem'  => $get('problem'),
                                'target'   => $get('target'),
                                'identify' => $get('identify'),
                            ])
                        ]);

                    Notification::make()
                        ->title(__('Saved successfully'))
                        ->success()
                        ->send();
                }),
            Wizard\Step::make('Solution & Stage')->icon('heroicon-s-light-bulb')
                ->schema([
                    RichEditor::make('solution')->label('Describe your proposed solution to the problem')->required(),
                    RichEditor::make('unique')->label('What makes your solution unique or innovative?')->required(),
                    RichEditor::make('alternatives')->label('How does your solution address the problem better than existing alternatives?')->required(),
                    Select::make('sector')->label('What industry sector does your product/service target?')->options([
                        'Agriculture'                   => __('Agriculture'),
                        'Automotive'                    => __('Automotive'),
                        'Banking'                       => __('Banking & Finance'),
                        'Construction'                  => __('Construction'),
                        'Education'                     => __('Education'),
                        'Energy'                        => __('Energy'),
                        'Entertainment'                 => __('Entertainment'),
                        'Environmental Services'        => __('Environmental Services'),
                        'Fashion'                       => __('Fashion'),
                        'Food Processing and Nutrition' => __('Food Processing and Nutrition'),
                        'Healthcare'                    => __('Healthcare'),
                        'Hospitality'                   => __('Hospitality'),
                        'Information Technology (IT)'   => __('Information Technology (IT)'),
                        'Legal Services'                => __('Legal Services'),
                        'Logistics & Transportation'    => __('Logistics & Transportation'),
                        'Manufacturing'                 => __('Manufacturing'),
                        'Media & Communications'        => __('Media & Communications'),
                        'Real Estate'                   => __('Real Estate'),
                        'Sports & Recreation'           => __('Sports & Recreation'),
                        'Telecommunications'            => __('Telecommunications'),
                        'Travel & Tourism'              => __('Travel & Tourism'),
                        'Other'                         => __('Other'),
                    ])->required()->reactive(),
                    TextInput::make('sector_other')->label('Please Specify')
                        ->hidden(fn(callable $get) => $get('sector') !== 'Other'),
                    Select::make('stage')->label('What stage is your solution currently in?')->options([
                        'Idea'      => __('Idea'),
                        'Prototype' => __('Prototype'),
                        'MVP'       => __('MVP'),
                    ])->required()->reactive(),
                    Select::make('have_prototype')->label('Have you developed a prototype or proof-of-concept?')->options([
                        'Yes' => __('Yes'),
                        'No'  => __('No'),
                    ])->hidden(fn(callable $get) => $get('stage') !== 'Prototype')->reactive(),
                    RichEditor::make('prototype_details')->label('Please provide us with details')
                        ->hidden(fn(callable $get) => $get('have_prototype') !== 'Yes'),
                    TextInput::make('duration')->label('How long have you been working on this solution?'),
                    Select::make('customers')->label('Do you have any customers or users currently?')->options([
                        'Yes' => __('Yes'),
                        'No'  => __('No'),
                    ])->required()->reactive(),
                    TextInput::make('customers_count')->label('How many customers and what is their feedback?')
                        ->hidden(fn(callable $get) => $get('customers') !== 'Yes'),

                    Select::make('individual_or_team')->label('Are you applying as an individual or as part of a team?')->options([
                        'Individual' => __('Individual'),
                        'Team'       => __('Team'),
                    ])->required()->reactive(),
                    Repeater::make('team_members')->label('Team Members')->addActionLabel(__('Add Team Member'))
                        ->schema([
                            TextInput::make('name')->label('Name')->required(),
                            TextInput::make('role')->label('Role')->required(),
                            PhoneInput::make('phone')->label('Phone')->required()->default(auth()->user()->phone)
                                ->defaultCountry('PS')
                                ->preferredCountries(['ps', 'il'])
                                ->showSelectedDialCode()
                                ->validateFor()
                                ->i18n([
                                    'il' => 'Palestine'
                                ]),
                            TextInput::make('email')->label('Email')->required()->email(),
                        ])->columns(4)->reorderableWithButtons()->inlineLabel(false)
                        ->hidden(fn(callable $get) => $get('individual_or_team') !== 'Team')->required(),
                ])->afterValidation(function (Get $get) use ($form) {
                    $application = $form->getModelInstance();
                    $application->update(
                        [
                            'data' => array_merge($application->data, [
                                'solution'           => $get('solution'),
                                'unique'             => $get('unique'),
                                'alternatives'       => $get('alternatives'),
                                'sector'             => $get('sector'),
                                'sector_other'       => $get('sector_other'),
                                'stage'              => $get('stage'),
                                'have_prototype'     => $get('have_prototype'),
                                'prototype_details'  => $get('prototype_details'),
                                'duration'           => $get('duration'),
                                'customers'          => $get('customers'),
                                'customers_count'    => $get('customers_count'),
                                'individual_or_team' => $get('individual_or_team'),
                                'team_members'       => $get('team_members'),
                            ])
                        ]);

                    Notification::make()
                        ->title(__('Saved successfully'))
                        ->success()
                        ->send();
                }),
            Wizard\Step::make('Next Milestones')->icon('heroicon-s-forward')
                ->schema([
                    RichEditor::make('milestones')->label('What are the next key milestones you aim to achieve in the next 3-6 months?')->required(),
                    RichEditor::make('resources')->label('What resources or support do you need to achieve these milestones?'),
                ])->afterValidation(function (Get $get) use ($form) {
                    $application = $form->getModelInstance();
                    $application->update(
                        [
                            'data' => array_merge($application->data, [
                                'milestones' => $get('milestones'),
                                'resources'  => $get('resources'),
                            ])
                        ]);
                    Notification::make()
                        ->title(__('Saved successfully'))
                        ->success()
                        ->send();
                }),
            Wizard\Step::make('Additional Information')->icon('heroicon-s-information-circle')
                ->schema([
                    Textarea::make('why')->label('Why do you want to join our pre-incubation program?')->required(),
                    Textarea::make('achieve')->label('What do you hope to achieve by the end of the program?'),
                    Select::make('program_discovery')->label('How did you hear about the PIEC Programme?')->options([
                        'Facebook'           => __('Facebook'),
                        'Instagram'          => __('Instagram'),
                        'LinkedIn'           => __('LinkedIn'),
                        'Other Social Media' => __('Other Social Media Channels'),
                        'Friend'             => __('Friend/Colleague'),
                        'Other'              => __('Other'),
                    ])->required()->reactive(),
                    TextInput::make('program_discovery_other')->label('Please Specify')
                        ->hidden(fn(callable $get) => $get('program_discovery') !== 'Other'),
                    FileUpload::make('attachments')->label('Anything you\'d like to share with us? Share your pitch deck or any additional supporting documents if available.')
                        ->multiple()->appendFiles()->maxFiles(5)->maxSize(10240)->directory('application-attachments')
                        ->hint(__("Maximum size: 10MB, Maximum files: 5"))
                ])->afterValidation(function (Get $get) use ($form) {
                    $application = $form->getModelInstance();
                    $application->update(
                        [
                            'data' => array_merge($application->data, [
                                'why'                     => $get('why'),
                                'achieve'                 => $get('achieve'),
                                'program_discovery'       => $get('program_discovery'),
                                'program_discovery_other' => $get('program_discovery_other'),
                            ])
                        ]);
                    Notification::make()
                        ->title(__('Saved successfully'))
                        ->success()
                        ->send();
                }),
            Wizard\Step::make('Review')->icon('heroicon-s-check-circle')
                ->schema([
                    Placeholder::make('review_section')->hiddenLabel()->content(
                        new HtmlString('<div style="font-size: 24px;text-align: center">'.__("Please review your application before submitting").'</div>')
                    ),
                    Section::make(__('Personal Information'))
                        ->schema([
                            Placeholder::make('review_first_name')->label('First Name')
                                ->content(fn(Application $record): string => $record->data['first_name'] ?? ''),
                            Placeholder::make('review_last_name')->label('Last Name')
                                ->content(fn(Application $record): string => $record->data['last_name'] ?? ''),
                            Placeholder::make('review_email')->label('Email')
                                ->content(fn(Application $record): string => $record->data['email'] ?? ''),
                            Placeholder::make('review_dob')->label('Date of Birth')
                                ->content(fn(Application $record): string => $record->data['dob'] ?? ''),
                            Placeholder::make('review_phone')->label('Phone')
                                ->content(fn(Application $record): string => $record->data['phone'] ?? ''),
                            Placeholder::make('review_whatsapp')->label('Whatsapp')
                                ->content(fn(Application $record): string => $record->data['whatsapp'] ?? ''),
                            Placeholder::make('review_gender')->label('Gender')
                                ->content(fn(Application $record): string => $record->data['gender'] ?? ''),
                            Placeholder::make('review_residence')->label('Governorate of Residence')
                                ->content(fn(Application $record): string => $record->data['residence'] ?? ''),
                            Placeholder::make('review_residence_other')->label('Other Governorate')
                                ->content(fn(Application $record
                                ): string => $record->data['residence_other'] ?? ''),
                            Placeholder::make('review_description')->label('Describe Yourself')
                                ->content(fn(Application $record
                                ): string => $record->data['description'] ?? ''),
                            Placeholder::make('review_description_other')->label('Describe Yourself (Other)')
                                ->content(fn(Application $record
                                ): string => $record->data['description_other'] ?? ''),
                            Placeholder::make('review_occupation')->label('Occupation')
                                ->content(fn(Application $record): string => $record->data['occupation'] ?? ''),
                        ])->columns(3),
                    Section::make(__('Problem & Need'))
                        ->schema([
                            Placeholder::make('review_problem')->label('What specific problem or need does your startup address?')
                                ->content(fn(Application $record
                                ): HtmlString => new HtmlString($record->data['problem'] ?? '')),
                            Placeholder::make('review_target')->label('Who is affected by this problem? And who’s your target segment?')
                                ->content(fn(Application $record
                                ): HtmlString => new HtmlString($record->data['target'] ?? '')),
                            Placeholder::make('review_identify')->label('How did you identify this problem?')
                                ->content(fn(Application $record
                                ): HtmlString => new HtmlString($record->data['identify'] ?? '')),
                        ]),
                    Section::make(__('Solution & Stage'))
                        ->schema([
                            Placeholder::make('review_solution')->label('Describe your proposed solution to the problem')
                                ->content(fn(Application $record
                                ): HtmlString => new HtmlString($record->data['solution'] ?? '')),
                            Placeholder::make('review_unique')->label('What makes your solution unique or innovative?')
                                ->content(fn(Application $record
                                ): HtmlString => new HtmlString($record->data['unique'] ?? '')),
                            Placeholder::make('review_alternatives')->label('How does your solution address the problem better than existing alternatives?')
                                ->content(fn(Application $record
                                ): HtmlString => new HtmlString($record->data['alternatives'] ?? '')),
                            Placeholder::make('review_sector')->label('What industry sector does your product/service target?')
                                ->content(fn(Application $record): string => $record->data['sector'] ?? ''),
                            Placeholder::make('review_sector_other')->label('Please Specify')
                                ->content(fn(Application $record): string => $record->data['sector_other'] ?? ''),
                            Placeholder::make('review_stage')->label('What stage is your solution currently in?')
                                ->content(fn(Application $record): string => $record->data['stage'] ?? ''),
                            Placeholder::make('review_have_prototype')->label('Have you developed a prototype or proof-of-concept?')
                                ->content(fn(Application $record): string => $record->data['have_prototype'] ?? ''),
                            Placeholder::make('review_prototype_details')->label('Please provide us with details')
                                ->content(fn(Application $record): string => $record->data['prototype_details'] ?? ''),
                            Placeholder::make('review_duration')->label('How long have you been working on this solution?')
                                ->content(fn(Application $record): string => $record->data['duration'] ?? ''),
                            Placeholder::make('review_customers')->label('Do you have any customers or users currently?')
                                ->content(fn(Application $record): string => $record->data['customers'] ?? ''),
                            Placeholder::make('review_customers_count')->label('How many customers and what is their feedback?')
                                ->content(fn(Application $record): string => $record->data['customers_count'] ?? ''),
                            Placeholder::make('review_individual_or_team')->label('Are you applying as an individual or as part of a team?')
                                ->content(fn(Application $record): string => $record->data['individual_or_team'] ?? ''),
                        ]),
                    Section::make(__('Next Milestones'))
                        ->schema([
                            Placeholder::make('review_milestones')->label('What are the next key milestones you aim to achieve in the next 3-6 months?')
                                ->content(fn(Application $record
                                ): HtmlString => new HtmlString($record->data['milestones'] ?? '')),
                            Placeholder::make('review_resources')->label('What resources or support do you need to achieve these milestones?')
                                ->content(fn(Application $record
                                ): HtmlString => new HtmlString($record->data['resources'] ?? '')),
                        ]),
                    Section::make(__('Additional Information'))
                        ->schema([
                            Placeholder::make('review_why')->label('Why do you want to join our pre-incubation program?')
                                ->content(fn(Application $record): string => $record->data['why'] ?? ''),
                            Placeholder::make('review_achieve')->label('What do you hope to achieve by the end of the program?')
                                ->content(fn(Application $record): string => $record->data['achieve'] ?? ''),
                            Placeholder::make('review_program_discovery')->label('How did you hear about the PIEC Programme?')
                                ->content(fn(Application $record): string => $record->data['program_discovery'] ?? ''),
                            Placeholder::make('review_program_discovery_other')->label('Please Specify')
                                ->content(fn(Application $record
                                ): string => $record->data['program_discovery_other'] ?? ''),
                        ])
                ])
        ];

        $incubation_form = [
            Wizard\Step::make('Startup Overview')->icon('heroicon-s-light-bulb')
                ->schema([
                    Textarea::make('idea')->label('Please provide a brief description of your business or idea.')->required(),
                    Textarea::make('problem')->label('What problem are you solving?')->required(),
                    Textarea::make('fit')->label('How did you validate the problem-solution fit?')->required(),
                    Textarea::make('solution')->label('Describe your solution and how it addresses the problem. Please make sure to clarify your value proposition')->required(),
                    Select::make('sector')->label('What industry sector does your product/service target?')->options([
                        'Agriculture'                   => __('Agriculture'),
                        'Automotive'                    => __('Automotive'),
                        'Banking'                       => __('Banking & Finance'),
                        'Construction'                  => __('Construction'),
                        'Education'                     => __('Education'),
                        'Energy'                        => __('Energy'),
                        'Entertainment'                 => __('Entertainment'),
                        'Environmental Services'        => __('Environmental Services'),
                        'Fashion'                       => __('Fashion'),
                        'Food Processing and Nutrition' => __('Food Processing and Nutrition'),
                        'Healthcare'                    => __('Healthcare'),
                        'Hospitality'                   => __('Hospitality'),
                        'Information Technology (IT)'   => __('Information Technology (IT)'),
                        'Legal Services'                => __('Legal Services'),
                        'Logistics & Transportation'    => __('Logistics & Transportation'),
                        'Manufacturing'                 => __('Manufacturing'),
                        'Media & Communications'        => __('Media & Communications'),
                        'Real Estate'                   => __('Real Estate'),
                        'Sports & Recreation'           => __('Sports & Recreation'),
                        'Telecommunications'            => __('Telecommunications'),
                        'Travel & Tourism'              => __('Travel & Tourism'),
                        'Other'                         => __('Other'),
                    ])->required()->reactive(),
                    TextInput::make('sector_other')->label('Please Specify')
                        ->hidden(fn(callable $get) => $get('sector') !== 'Other'),
                ])->afterValidation(function (Get $get) use ($form) {
                    $application = $form->getModelInstance();
                    $application->update(
                        [
                            'data' => array_merge($application->data, [
                                'idea'         => $get('idea'),
                                'problem'      => $get('problem'),
                                'fit'          => $get('fit'),
                                'solution'     => $get('solution'),
                                'sector'       => $get('sector'),
                                'sector_other' => $get('sector_other'),
                            ])
                        ]);
                    Notification::make()
                        ->title(__('Saved successfully'))
                        ->success()
                        ->send();
                }),
            Wizard\Step::make('Current Status')->icon('heroicon-s-bolt')
                ->schema([
                    Select::make('stage')->label('What stage is your solution currently in?')->options([
                        'Idea'             => __('Idea'),
                        'Proof-of-Concept' => __('Proof-of-Concept'),
                        'Prototype'        => __('Prototype'),
                        'MVP'              => __('MVP'),
                    ])->required()->reactive(),
                    Select::make('features')->label('Have you identified the must-have features that your MVP should include?')->options([
                        'Yes' => __('Yes'),
                        'No'  => __('No'),
                    ])->hidden(fn(callable $get) => ! in_array($get('stage'),
                        ['Idea', 'Prototype', 'Proof-of-Concept']))->reactive(),
                    Textarea::make('milestones')->label('What key milestones have you achieved so far?')->required(),
                ])->afterValidation(function (Get $get) use ($form) {
                    $application = $form->getModelInstance();
                    $application->update(
                        [
                            'data' => array_merge($application->data, [
                                'stage'      => $get('stage'),
                                'features'   => $get('features'),
                                'milestones' => $get('milestones'),
                            ])
                        ]);
                    Notification::make()
                        ->title(__('Saved successfully'))
                        ->success()
                        ->send();
                }),
            Wizard\Step::make('Market Research')->icon('heroicon-s-building-storefront')
                ->schema([
                    TextInput::make('target')->label('Who are your target customers?')->required(),
                    Textarea::make('how_target')->label('How did you identify your target market? Why do you believe this target market presents a good opportunity to start your business? Describe any research methods used (surveys, interviews, focus groups, etc.)'),
                    Textarea::make('advantage')->label('What is your competitive advantage? How do you plan to differentiate your startup in the market?'),
                ])->afterValidation(function (Get $get) use ($form) {
                    $application = $form->getModelInstance();
                    $application->update(
                        [
                            'data' => array_merge($application->data, [
                                'target'     => $get('target'),
                                'how_target' => $get('how_target'),
                                'advantage'  => $get('advantage'),
                            ])
                        ]);
                    Notification::make()
                        ->title(__('Saved successfully'))
                        ->success()
                        ->send();
                }),
            Wizard\Step::make('Team')->icon('heroicon-s-users')
                ->schema([
                    Repeater::make('team_members')->label('Team Members')->addActionLabel(__('Add Team Member'))
                        ->schema([
                            TextInput::make('name')->label('Name')->required(),
                            TextInput::make('role')->label('Role')->required(),
                            PhoneInput::make('phone')->label('Phone')->required()->default(auth()->user()->phone)
                                ->defaultCountry('PS')
                                ->preferredCountries(['ps', 'il'])
                                ->showSelectedDialCode()
                                ->validateFor()
                                ->i18n([
                                    'il' => 'Palestine'
                                ]),
                            TextInput::make('email')->label('Email')->required()->email(),
                        ])->columns(4)->reorderableWithButtons()->inlineLabel(false)->required(),
                ])->afterValidation(function (Get $get) use ($form) {
                    $application = $form->getModelInstance();
                    $application->update(
                        [
                            'data' => array_merge($application->data, [
                                'team_members' => $get('team_members'),
                            ])
                        ]);
                    Notification::make()
                        ->title(__('Saved successfully'))
                        ->success()
                        ->send();
                }),
            Wizard\Step::make('Vision and Goals')->icon('heroicon-s-forward')
                ->schema([
                    Textarea::make('vision')->label('What is your vision for the startup in the next 1-3 years?')->required(),
                    Textarea::make('achieve')->label('What are the key milestones you aim to achieve during the incubation program?'),
                    Textarea::make('support')->label('How can Flow Accelerator support you in developing your MVP?'),
                ])->afterValidation(function (Get $get) use ($form) {
                    $application = $form->getModelInstance();
                    $application->update(
                        [
                            'data' => array_merge($application->data, [
                                'vision'  => $get('vision'),
                                'achieve' => $get('achieve'),
                                'support' => $get('support'),
                            ])
                        ]);
                    Notification::make()
                        ->title(__('Saved successfully'))
                        ->success()
                        ->send();
                }),
            Wizard\Step::make('Additional Information')->icon('heroicon-s-information-circle')
                ->schema([
                    Textarea::make('other')->label('Have you participated in any other incubation or acceleration programs? If yes, provide details.'),
                    Select::make('committed')->label('Are you committed to attending the training sessions, mentoring sessions, and completing deliverables?')->options([
                        'Yes' => __('Yes'),
                        'No'  => __('No'),
                    ])->required(),
                    TextInput::make('issues')->label('Are there any legal issues or intellectual property concerns related to your startup?'),
                    FileUpload::make('attachments')->label('Please upload your pitch deck or any relevant documents you would like to share with us.')
                        ->multiple()->appendFiles()->maxFiles(5)->maxSize(10240)->directory('application-attachments')
                        ->hint(__("Maximum size: 10MB, Maximum files: 5")),
                ])->afterValidation(function (Get $get) use ($form) {
                    $application = $form->getModelInstance();
                    $application->update(
                        [
                            'data' => array_merge($application->data, [
                                'other'     => $get('other'),
                                'committed' => $get('committed'),
                                'issues'    => $get('issues'),
                            ])
                        ]);
                    Notification::make()
                        ->title(__('Saved successfully'))
                        ->success()
                        ->send();
                }),
            Wizard\Step::make('Review')->icon('heroicon-s-check-circle')
                ->schema([
                    Placeholder::make('review_section')->hiddenLabel()->content(
                        new HtmlString('<div style="font-size: 24px;text-align: center">'.__("Please review your application before submitting").'</div>')
                    ),
                    Section::make(__('Personal Information'))
                        ->schema([
                            Placeholder::make('review_first_name')->label('First Name')
                                ->content(fn(Application $record): string => $record->data['first_name'] ?? ''),
                            Placeholder::make('review_last_name')->label('Last Name')
                                ->content(fn(Application $record): string => $record->data['last_name'] ?? ''),
                            Placeholder::make('review_email')->label('Email')
                                ->content(fn(Application $record): string => $record->data['email'] ?? ''),
                            Placeholder::make('review_dob')->label('Date of Birth')
                                ->content(fn(Application $record): string => $record->data['dob'] ?? ''),
                            Placeholder::make('review_phone')->label('Phone')
                                ->content(fn(Application $record): string => $record->data['phone'] ?? ''),
                            Placeholder::make('review_whatsapp')->label('Whatsapp')
                                ->content(fn(Application $record): string => $record->data['whatsapp'] ?? ''),
                            Placeholder::make('review_gender')->label('Gender')
                                ->content(fn(Application $record): string => $record->data['gender'] ?? ''),
                            Placeholder::make('review_residence')->label('Governorate of Residence')
                                ->content(fn(Application $record): string => $record->data['residence'] ?? ''),
                            Placeholder::make('review_residence_other')->label('Other Governorate')
                                ->content(fn(Application $record
                                ): string => $record->data['residence_other'] ?? ''),
                            Placeholder::make('review_description')->label('Describe Yourself')
                                ->content(fn(Application $record
                                ): string => $record->data['description'] ?? ''),
                            Placeholder::make('review_description_other')->label('Describe Yourself (Other)')
                                ->content(fn(Application $record
                                ): string => $record->data['description_other'] ?? ''),
                            Placeholder::make('review_occupation')->label('Occupation')
                                ->content(fn(Application $record): string => $record->data['occupation'] ?? ''),
                        ])->columns(3),
                    Section::make(__('Startup Overview'))
                        ->schema([
                            Placeholder::make('review_idea')->label('Please provide a brief description of your business or idea.')
                                ->content(fn(Application $record): string => $record->data['idea'] ?? ''),
                            Placeholder::make('review_problem')->label('What problem are you solving?')
                                ->content(fn(Application $record): string => $record->data['problem'] ?? ''),
                            Placeholder::make('review_fit')->label('How did you validate the problem-solution fit?')
                                ->content(fn(Application $record): string => $record->data['fit'] ?? ''),
                            Placeholder::make('review_solution')->label('Describe your solution and how it addresses the problem. Please make sure to clarify your value proposition')
                                ->content(fn(Application $record): string => $record->data['solution'] ?? ''),
                            Placeholder::make('review_sector')->label('What industry sector does your product/service target?')
                                ->content(fn(Application $record): string => $record->data['sector'] ?? ''),
                            Placeholder::make('review_sector_other')->label('Please Specify')
                                ->content(fn(Application $record): string => $record->data['sector_other'] ?? ''),
                        ]),
                    Section::make(__('Current Status'))
                        ->schema([
                            Placeholder::make('review_stage')->label('What stage is your solution currently in?')
                                ->content(fn(Application $record): string => $record->data['stage'] ?? ''),
                            Placeholder::make('review_features')->label('Have you identified the must-have features that your MVP should include?')
                                ->content(fn(Application $record): string => $record->data['features'] ?? ''),
                            Placeholder::make('review_milestones')->label('What key milestones have you achieved so far?')
                                ->content(fn(Application $record): string => $record->data['milestones'] ?? ''),
                        ]),
                    Section::make(__('Market Research and Target Customer'))
                        ->schema([
                            Placeholder::make('review_target')->label('Who are your target customers?')
                                ->content(fn(Application $record): string => $record->data['target'] ?? ''),
                            Placeholder::make('review_how_target')->label('How did you identify your target market? Why do you believe this target market presents a good opportunity to start your business? Describe any research methods used (surveys, interviews, focus groups, etc.)')
                                ->content(fn(Application $record): string => $record->data['how_target'] ?? ''),
                            Placeholder::make('review_advantage')->label('What is your competitive advantage? How do you plan to differentiate your startup in the market?')
                                ->content(fn(Application $record): string => $record->data['advantage'] ?? ''),
                        ]),
                    Section::make(__('Vision and Goals'))
                        ->schema([
                            Placeholder::make('review_vision')->label('What is your vision for the startup in the next 1-3 years?')
                                ->content(fn(Application $record): string => $record->data['vision'] ?? ''),
                            Placeholder::make('review_achieve')->label('What are the key milestones you aim to achieve during the incubation program?')
                                ->content(fn(Application $record): string => $record->data['achieve'] ?? ''),
                            Placeholder::make('review_support')->label('How can Flow Accelerator support you in developing your MVP?')
                                ->content(fn(Application $record): string => $record->data['support'] ?? ''),
                        ]),
                    Section::make(__('Additional Information'))
                        ->schema([
                            Placeholder::make('review_other')->label('Have you participated in any other incubation or acceleration programs? If yes, provide details.')
                                ->content(fn(Application $record): string => $record->data['other'] ?? ''),
                            Placeholder::make('review_committed')->label('Are you committed to attending the training sessions, mentoring sessions, and completing deliverables?')
                                ->content(fn(Application $record): string => $record->data['committed'] ?? ''),
                            Placeholder::make('review_issues')->label('Are there any legal issues or intellectual property concerns related to your startup?')
                                ->content(fn(Application $record): string => $record->data['issues'] ?? ''),
                        ])
                ])
        ];

        $pre_acceleration_form = [
            Wizard\Step::make('Solution')->icon('heroicon-s-light-bulb')
                ->schema([
                    Select::make('sector')->label('In which industry does your solution fit?')->options([
                        'Agriculture'                   => __('Agriculture'),
                        'Automotive'                    => __('Automotive'),
                        'Banking'                       => __('Banking & Finance'),
                        'Construction'                  => __('Construction'),
                        'Education'                     => __('Education'),
                        'Energy'                        => __('Energy'),
                        'Entertainment'                 => __('Entertainment'),
                        'Environmental Services'        => __('Environmental Services'),
                        'Fashion'                       => __('Fashion'),
                        'Food Processing and Nutrition' => __('Food Processing and Nutrition'),
                        'Healthcare'                    => __('Healthcare'),
                        'Hospitality'                   => __('Hospitality'),
                        'Information Technology (IT)'   => __('Information Technology (IT)'),
                        'Legal Services'                => __('Legal Services'),
                        'Logistics & Transportation'    => __('Logistics & Transportation'),
                        'Manufacturing'                 => __('Manufacturing'),
                        'Media & Communications'        => __('Media & Communications'),
                        'Real Estate'                   => __('Real Estate'),
                        'Sports & Recreation'           => __('Sports & Recreation'),
                        'Telecommunications'            => __('Telecommunications'),
                        'Travel & Tourism'              => __('Travel & Tourism'),
                        'Other'                         => __('Other'),
                    ])->required()->reactive(),
                    TextInput::make('sector_other')->label('Please Specify')
                        ->hidden(fn(callable $get) => $get('sector') !== 'Other'),
                    Select::make('stage')->label('What stage is your solution currently in?')->options([
                        'PoC-Prototype-MVP' => __('Proof-of-Concept / Prototype / MVP'),
                        'Launch'            => __('Market Launch'),
                    ])->required()->reactive(),


                    // Combined Questions
                    TextInput::make('solution_name')->label('What is the name of your solution?')->required(),
                    Textarea::make('solution')->label('In a short paragraph, describe your solution briefly?')->required(),
                    Select::make('solution_type')->label('Is your solution a software, hardware or System?')
                        ->options([
                            'Software' => __('Software'),
                            'Hardware' => __('Hardware'),
                            'System'   => __('System (SW & HW)'),
                            'Other'    => __('Other'),
                        ])->required()->reactive(),
                    TextInput::make('solution_type_other')->label('Please Specify')
                        ->hidden(fn(callable $get) => $get('solution_type') !== 'Other'),
                    Textarea::make('problem')->label('What Problem / Challenge does your solution tackle?')->required(),
                    Textarea::make('target')->label('Who are your target market and potential customers?')->required(),
                    Textarea::make('value_proposition')->label('How does your solution solve the problem and what\'s your value proposition?')->required(),
                    Textarea::make('competitive_advantage')->label('What is your competitive advantage?')->required(),
                    Select::make('impact')->label('What types of impact does your solution make?')
                        ->options([
                            'Academic Impact'      => __('Academic Impact'),
                            'Social Impact'        => __('Social Impact'),
                            'Economic Impact'      => __('Economic Impact'),
                            'Wellbeing Impact'     => __('Wellbeing Impact'),
                            'Environmental Impact' => __('Environmental Impact (for ex. Green Innovation, Circular Economy, Climate Change, etc)'),
                            'Other'                => __('Other'),
                        ])->required()->reactive(),
                    TextInput::make('impact_other')->label('Please Specify')
                        ->hidden(fn(callable $get) => $get('impact') !== 'Other'),
                    // End Combined Questions


                    // PoC-Prototype-MVP Questions
                    Select::make('validation_duration')->label('When did you start with the idea validation?')
                        ->options([
                            'Less than 1 year'  => __('Less than 1 year'),
                            '1-3 years'         => __('1-3 years'),
                            '3-5 years'         => __('3-5 years'),
                            'more than 5 years' => __('more than 5 years'),
                        ])->required()->hidden(fn(callable $get) => $get('stage') !== 'PoC-Prototype-MVP'),
                    Textarea::make('validation_process')->label('Tell us briefly about your proof of concept/idea validation process?')->required()
                        ->hidden(fn(callable $get) => $get('stage') !== 'PoC-Prototype-MVP'),
                    // End PoC-Prototype-MVP Questions

                    // Launch Questions
                    Select::make('startup_registered')->label('Is your Startup registered?')
                        ->options([
                            'Yes' => __('Yes'),
                            'No'  => __('No'),
                        ])->required()->hidden(fn(callable $get) => $get('stage') !== 'Launch'),
                    Select::make('solution_launch')->label('When did you launch your solution in the market?')
                        ->options([
                            'Less than 1 year'  => __('Less than 1 year'),
                            '1-3 years'         => __('1-3 years'),
                            '3-5 years'         => __('3-5 years'),
                            'more than 5 years' => __('more than 5 years'),
                        ])->required()->hidden(fn(callable $get) => $get('stage') !== 'Launch'),
                    Select::make('go_to_market_strategy')->label('What was your go-to-market Strategy?')
                        ->options([
                            'Email Announcements'    => __('Email Announcements'),
                            'Landing Page'           => __('Landing Page'),
                            'Social Media Promotion' => __('Social Media Promotion'),
                            'Website and SEO'        => __('Website and SEO'),
                            'Direct Sales'           => __('Direct Sales'),
                            'Other'                  => __('Other'),
                        ])->required()->reactive()->hidden(fn(callable $get) => $get('stage') !== 'Launch'),
                    TextInput::make('go_to_market_strategy_other')->label('Please Specify')
                        ->hidden(fn(callable $get
                        ) => $get('go_to_market_strategy') !== 'Other' || $get('stage') !== 'Launch'),
                    // End Launch Questions

                    // Combined Questions
                    Select::make('business_model')->label('What is your business model?')
                        ->options([
                            'B2B'   => __('B2B: Business to business'),
                            'B2C'   => __('B2C: Business to customer'),
                            'B2G'   => __('B2G: Business to government'),
                            'B2B2C' => __('B2B2C: Business to business to customer'),
                            'B2C2B' => __('B2C2B: Business to customer to business'),
                            'C2C'   => __('C2C: Consumer to Consumer'),
                            'C2B'   => __('C2B: Consumer to Business'),
                            'Other' => __('Other'),
                        ])->required()->reactive(),
                    TextInput::make('business_model_other')->label('Please Specify')
                        ->hidden(fn(callable $get) => $get('business_model') !== 'Other'),
                    Select::make('revenue_model')->label('What is your revenue model? How does your business generate revenue?')
                        ->options([
                            'Ad-Based Revenue Model'            => __('Ad-Based Revenue Model'),
                            'Affiliate Revenue Model'           => __('Affiliate Revenue Model'),
                            'Transactional Revenue Model'       => __('Transactional Revenue Model'),
                            'Subscription Revenue Model'        => __('Subscription Revenue Model'),
                            'Web Sales'                         => __('Web Sales'),
                            'Direct Sales'                      => __('Direct Sales'),
                            'Channel Sales (or Indirect Sales)' => __('Channel Sales (or Indirect Sales)'),
                            'Retail Sales'                      => __('Retail Sales'),
                            'Freemium Model'                    => __('Freemium Model'),
                            'Wholesale'                         => __('Wholesale'),
                            'SaaS (Software as a service)'      => __('SaaS (Software as a service)'),
                            'Other'                             => __('Other'),
                        ])->required()->reactive(),
                    TextInput::make('revenue_model_other')->label('Please Specify')
                        ->hidden(fn(callable $get) => $get('revenue_model') !== 'Other'),
                    Textarea::make('competitors')->label('Who are your current competitors? Locally and regionally?')->required(),
                    // End Combined Questions


                    // PoC-Prototype-MVP Questions
                    Select::make('funding')->label('Did you get any funding so far?')
                        ->options([
                            'Bootstrapping'      => __('Bootstrapping'),
                            'Family and Friends' => __('Family and Friends'),
                            'Loan'               => __('Loan'),
                            'Crowdfunding'       => __('Crowdfunding'),
                            'Grants'             => __('Grants'),
                            'Investment'         => __('Investment'),
                            'Other'              => __('Other'),
                            'No'                 => __('No, I didn\'t get any funding so far'),
                        ])->required()->reactive()->hidden(fn(callable $get) => $get('stage') !== 'PoC-Prototype-MVP'),
                    TextInput::make('funding_other')->label('Please Specify')
                        ->hidden(fn(callable $get
                        ) => $get('funding') !== 'Other' || $get('stage') !== 'PoC-Prototype-MVP'),
                    Select::make('challenges')->label('What are the greatest challenges facing the implementation of your idea? If any please choose.')
                        ->options([
                            'Small market'                                    => __('Small market'),
                            'Strict regulations'                              => __('Strict regulations'),
                            'Lack of financial resources'                     => __('Lack of financial resources'),
                            'Lack of Experience'                              => __('Lack of Experience'),
                            'Lack of information (No qualified team members)' => __('Lack of information (No qualified team members)'),
                            'Other'                                           => __('Other'),
                        ])->required()->reactive()->hidden(fn(callable $get) => $get('stage') !== 'PoC-Prototype-MVP'),
                    TextInput::make('challenges_other')->label('Please Specify')
                        ->hidden(fn(callable $get
                        ) => $get('challenges') !== 'Other' || $get('stage') !== 'PoC-Prototype-MVP'),
                    // End PoC-Prototype-MVP Questions

                    // Launch Questions
                    Textarea::make('traction')->label('What traction and leads were you able to gain? Please clarify.')->required()
                        ->hidden(fn(callable $get) => $get('stage') !== 'Launch'),
                    Textarea::make('market_validation')->label('What is your market validation strategy?')->required()
                        ->hidden(fn(callable $get) => $get('stage') !== 'Launch'),
                    Select::make('generating_revenue')->label('Is the solution generating revenue already?')
                        ->options([
                            'Yes' => __('Yes'),
                            'No'  => __('No'),
                        ])->required()->hidden(fn(callable $get) => $get('stage') !== 'Launch'),
                    Textarea::make('needs')->label('What are the three top needs for your solution that the funding would fulfill?')->required()
                        ->hidden(fn(callable $get) => $get('stage') !== 'Launch'),
                    // End Launch Questions


                    Select::make('sdg')->label('Does your solution meet one or more of the SDGs (Sustainable Development Goals)?')
                        ->options([
                            'It doesn\'t support any'                => __('It doesn\'t support any'),
                            'No Poverty'                             => __('No Poverty'),
                            'Zero Hunger'                            => __('Zero Hunger'),
                            'Good Health and Well-being'             => __('Good Health and Well-being'),
                            'Quality Education'                      => __('Quality Education'),
                            'Gender Equality'                        => __('Gender Equality'),
                            'Clean Water and Sanitation'             => __('Clean Water and Sanitation'),
                            'Affordable and Clean Energy'            => __('Affordable and Clean Energy'),
                            'Decent Work and Economic Growth'        => __('Decent Work and Economic Growth'),
                            'Industry Innovation and Infrastructure' => __('Industry Innovation and Infrastructure'),
                            'Reduced Inequalities'                   => __('Reduced Inequalities'),
                            'Sustainable Cities and Communities'     => __('Sustainable Cities and Communities'),
                            'Responsible Consumption and Production' => __('Responsible Consumption and Production'),
                            'Climate Action'                         => __('Climate Action'),
                            'Life below Water'                       => __('Life below Water'),
                            'Life on Land'                           => __('Life on Land'),
                            'Peace Justice and Strong Institutions'  => __('Peace Justice and Strong Institutions'),
                            'Partnerships for the Goals'             => __('Partnerships for the Goals'),
                        ])->required(),
                ])->afterValidation(function (Get $get) use ($form) {
                    $application = $form->getModelInstance();
                    $application->update(
                        [
                            'data' => array_merge($application->data, [
                                'sector'                => $get('sector'),
                                'sector_other'          => $get('sector_other'),
                                'stage'                 => $get('stage'),
                                'solution_name'         => $get('solution_name'),
                                'solution'              => $get('solution'),
                                'solution_type'         => $get('solution_type'),
                                'solution_type_other'   => $get('solution_type_other'),
                                'problem'               => $get('problem'),
                                'target'                => $get('target'),
                                'value_proposition'     => $get('value_proposition'),
                                'competitive_advantage' => $get('competitive_advantage'),
                                'impact'                => $get('impact'),
                                'impact_other'          => $get('impact_other'),
                                'validation_duration'   => $get('validation_duration'),
                                'validation_process'    => $get('validation_process'),
                                'startup_registered'    => $get('startup_registered'),
                                'solution_launch'       => $get('solution_launch'),
                                'go_to_market_strategy' => $get('go_to_market_strategy'),
                                'business_model'        => $get('business_model'),
                                'business_model_other'  => $get('business_model_other'),
                                'revenue_model'         => $get('revenue_model'),
                                'revenue_model_other'   => $get('revenue_model_other'),
                                'competitors'           => $get('competitors'),
                                'funding'               => $get('funding'),
                                'funding_other'         => $get('funding_other'),
                                'challenges'            => $get('challenges'),
                                'challenges_other'      => $get('challenges_other'),
                                'traction'              => $get('traction'),
                                'market_validation'     => $get('market_validation'),
                                'sdg'                   => $get('sdg'),
                                'generating_revenue'    => $get('generating_revenue'),
                                'needs'                 => $get('needs'),
                            ])
                        ]);
                    Notification::make()
                        ->title(__('Saved successfully'))
                        ->success()
                        ->send();
                }),
            Wizard\Step::make('Team')->icon('heroicon-s-users')
                ->schema([
                    Repeater::make('team_members')->label('Team Members')->addActionLabel(__('Add Team Member'))
                        ->schema([
                            TextInput::make('name')->label('Name')->required(),
                            TextInput::make('role')->label('Role')->required(),
                            PhoneInput::make('phone')->label('Phone')->required()->default(auth()->user()->phone)
                                ->defaultCountry('PS')
                                ->preferredCountries(['ps', 'il'])
                                ->showSelectedDialCode()
                                ->validateFor()
                                ->i18n([
                                    'il' => 'Palestine'
                                ]),
                            TextInput::make('email')->label('Email')->required()->email(),
                        ])->columns(4)->reorderableWithButtons()->inlineLabel(false)->required(),
                ])->afterValidation(function (Get $get) use ($form) {
                    $application = $form->getModelInstance();
                    $application->update(
                        [
                            'data' => array_merge($application->data, [
                                'team_members' => $get('team_members'),
                            ])
                        ]);
                    Notification::make()
                        ->title(__('Saved successfully'))
                        ->success()
                        ->send();
                }),
            Wizard\Step::make('Strategy')->icon('heroicon-s-forward')
                ->schema([
                    Textarea::make('strategy')->label('What are the upcoming milestones you aim to achieve throughout the programme. Please provide specific measurable milestones and the expected completion dates.')->required(),
                ])->afterValidation(function (Get $get) use ($form) {
                    $application = $form->getModelInstance();
                    $application->update(
                        [
                            'data' => array_merge($application->data, [
                                'strategy' => $get('strategy'),
                            ])
                        ]);
                    Notification::make()
                        ->title(__('Saved successfully'))
                        ->success()
                        ->send();
                }),
            Wizard\Step::make('General Information')->icon('heroicon-s-information-circle')
                ->schema([
                    Select::make('business_skills')->label('Which of the following business skills do you have?')
                        ->options([
                            'Finance'                         => __('Finance'),
                            'Sales'                           => __('Sales'),
                            'Marketing'                       => __('Marketing'),
                            'Project Management'              => __('Project Management'),
                            'Graphic Design and Copy Writing' => __('Graphic Design and Copy Writing'),
                            'None'                            => __('None'),
                            'Other'                           => __('Other'),
                        ])->required(),
                    Select::make('startup_experience')->label('Do you have any knowledge or experience in entrepreneurship/startups?')->options([
                        'Yes' => __('Yes'),
                        'No'  => __('No'),
                    ])->required()->reactive(),
                    Textarea::make('experience_specification')->label('Please specify your experience')
                        ->hidden(fn(callable $get) => $get('startup_experience') !== 'Yes'),
                    TextInput::make('new_skill')->label('If you are looking to acquire one new skill, what would it be?')->required(),
                    Select::make('program_discovery')->label('How did you hear about the Orange Corners Incubation Programme?')
                        ->options([
                            'Orange Corners Facebook Page'    => __('Orange Corners Facebook Page'),
                            'Orange Corners Instagram Page'   => __('Orange Corners Instagram Page'),
                            'Orange Corners LinkedIn Page'    => __('Orange Corners LinkedIn Page'),
                            'Orange Corners Website'          => __('Orange Corners Website'),
                            'Flow Accelerator Facebook Page'  => __('Flow Accelerator Facebook Page'),
                            'Flow Accelerator Instagram Page' => __('Flow Accelerator Instagram Page'),
                            'Flow Accelerator LinkedIn Page'  => __('Flow Accelerator LinkedIn Page'),
                            'Other Social Media Channels'     => __('Other Social Media Channels'),
                            'Friend/Colleague'                => __('Friend/Colleague'),
                            'Your Company'                    => __('Your Company'),
                        ])->required(),
                    Select::make('participation')->label('If you were selected, can you participate in a 3-day bootcamp?')
                        ->options([
                            'Yes' => __('Yes'),
                            'No'  => __('No'),
                        ])->required(),
                    TextInput::make('prototype_link')->label('Please share a link to the prototype of your product so that we can get a better understanding of its features and functionalities.')->url()->required(),
                    FileUpload::make('attachments')->label('Please attach the following (Pitch Deck, Business Plan, Supporting Document, Business Model Canvas)')
                        ->multiple()->appendFiles()->maxFiles(5)->maxSize(10240)->directory('application-attachments')
                        ->hint(__("Maximum size: 10MB, Maximum files: 5")),
                ])->afterValidation(function (Get $get) use ($form) {
                    $application = $form->getModelInstance();
                    $application->update(
                        [
                            'data' => array_merge($application->data, [
                                'business_skills'          => $get('business_skills'),
                                'startup_experience'       => $get('startup_experience'),
                                'experience_specification' => $get('experience_specification'),
                                'new_skill'                => $get('new_skill'),
                                'program_discovery'        => $get('program_discovery'),
                                'participation'            => $get('participation'),
                                'prototype_link'           => $get('prototype_link'),
                            ])
                        ]);
                    Notification::make()
                        ->title(__('Saved successfully'))
                        ->success()
                        ->send();
                }),
            Wizard\Step::make('Review')->icon('heroicon-s-check-circle')
                ->schema([
                    Placeholder::make('review_section')->hiddenLabel()->content(
                        new HtmlString('<div style="font-size: 24px;text-align: center">'.__("Please review your application before submitting").'</div>')
                    ),
                    Section::make(__('Personal Information'))
                        ->schema([
                            Placeholder::make('review_first_name')->label('First Name')
                                ->content(fn(Application $record): string => $record->data['first_name'] ?? ''),
                            Placeholder::make('review_last_name')->label('Last Name')
                                ->content(fn(Application $record): string => $record->data['last_name'] ?? ''),
                            Placeholder::make('review_email')->label('Email')
                                ->content(fn(Application $record): string => $record->data['email'] ?? ''),
                            Placeholder::make('review_dob')->label('Date of Birth')
                                ->content(fn(Application $record): string => $record->data['dob'] ?? ''),
                            Placeholder::make('review_phone')->label('Phone')
                                ->content(fn(Application $record): string => $record->data['phone'] ?? ''),
                            Placeholder::make('review_whatsapp')->label('Whatsapp')
                                ->content(fn(Application $record): string => $record->data['whatsapp'] ?? ''),
                            Placeholder::make('review_gender')->label('Gender')
                                ->content(fn(Application $record): string => $record->data['gender'] ?? ''),
                            Placeholder::make('review_residence')->label('Governorate of Residence')
                                ->content(fn(Application $record): string => $record->data['residence'] ?? ''),
                            Placeholder::make('review_residence_other')->label('Other Governorate')
                                ->content(fn(Application $record
                                ): string => $record->data['residence_other'] ?? ''),
                            Placeholder::make('review_description')->label('Describe Yourself')
                                ->content(fn(Application $record
                                ): string => $record->data['description'] ?? ''),
                            Placeholder::make('review_description_other')->label('Describe Yourself (Other)')
                                ->content(fn(Application $record
                                ): string => $record->data['description_other'] ?? ''),
                            Placeholder::make('review_occupation')->label('Occupation')
                                ->content(fn(Application $record): string => $record->data['occupation'] ?? ''),
                        ])->columns(3),
                    Section::make(__('Solution'))
                        ->schema([
                            Placeholder::make('review_sector')->label(__('In which industry does your solution fit?'))
                                ->content(fn(Application $record): string => $record->data['sector'] ?? ''),
                            Placeholder::make('review_sector_other')->label(__('Please Specify'))
                                ->content(fn(Application $record): string => $record->data['sector_other'] ?? ''),
                            Placeholder::make('review_stage')->label(__('What stage is your solution currently in?'))
                                ->content(fn(Application $record): string => $record->data['stage'] ?? ''),
                            Placeholder::make('review_solution_name')->label(__('What is the name of your solution?'))
                                ->content(fn(Application $record): string => $record->data['solution_name'] ?? ''),
                            Placeholder::make('review_solution')->label(__('In a short paragraph, describe your solution briefly?'))
                                ->content(fn(Application $record): string => $record->data['solution'] ?? ''),
                            Placeholder::make('review_solution_type')->label(__('Is your solution a software, hardware or System?'))
                                ->content(fn(Application $record): string => $record->data['solution_type'] ?? ''),
                            Placeholder::make('review_solution_type_other')->label(__('Please Specify'))
                                ->content(fn(Application $record
                                ): string => $record->data['solution_type_other'] ?? ''),
                            Placeholder::make('review_problem')->label(__('What Problem / Challenge does your solution tackle?'))
                                ->content(fn(Application $record): string => $record->data['problem'] ?? ''),
                            Placeholder::make('review_target')->label(__('Who are your target market and potential customers?'))
                                ->content(fn(Application $record): string => $record->data['target'] ?? ''),
                            Placeholder::make('review_value_proposition')->label(__('How does your solution solve the problem and what\'s your value proposition?'))
                                ->content(fn(Application $record): string => $record->data['value_proposition'] ?? ''),
                            Placeholder::make('review_competitive_advantage')->label(__('What is your competitive advantage?'))
                                ->content(fn(Application $record
                                ): string => $record->data['competitive_advantage'] ?? ''),
                            Placeholder::make('review_impact')->label(__('What types of impact does your solution make?'))
                                ->content(fn(Application $record): string => $record->data['impact'] ?? ''),
                            Placeholder::make('review_impact_other')->label(__('Please Specify'))
                                ->content(fn(Application $record): string => $record->data['impact_other'] ?? ''),
                            Placeholder::make('review_validation_duration')->label(__('When did you start with the idea validation?'))
                                ->content(fn(Application $record
                                ): string => $record->data['validation_duration'] ?? ''),
                            Placeholder::make('review_validation_process')->label(__('Tell us briefly about your proof of concept/idea validation process?'))
                                ->content(fn(Application $record): string => $record->data['validation_process'] ?? ''),
                            Placeholder::make('review_startup_registered')->label(__('Is your Startup registered?'))
                                ->content(fn(Application $record): string => $record->data['startup_registered'] ?? ''),
                            Placeholder::make('review_solution_launch')->label(__('When did you launch your solution in the market?'))
                                ->content(fn(Application $record): string => $record->data['solution_launch'] ?? ''),
                            Placeholder::make('review_go_to_market_strategy')->label(__('What was your go-to-market Strategy?'))
                                ->content(fn(Application $record
                                ): string => $record->data['go_to_market_strategy'] ?? ''),
                            Placeholder::make('review_go_to_market_strategy_other')->label(__('Please Specify'))
                                ->content(fn(Application $record
                                ): string => $record->data['go_to_market_strategy_other'] ?? ''),
                            Placeholder::make('review_business_model')->label(__('What is your business model?'))
                                ->content(fn(Application $record): string => $record->data['business_model'] ?? ''),
                            Placeholder::make('review_business_model_other')->label(__('Please Specify'))
                                ->content(fn(Application $record
                                ): string => $record->data['business_model_other'] ?? ''),
                            Placeholder::make('review_revenue_model')->label(__('What is your revenue model? How does your business generate revenue?'))
                                ->content(fn(Application $record): string => $record->data['revenue_model'] ?? ''),
                            Placeholder::make('review_revenue_model_other')->label(__('Please Specify'))
                                ->content(fn(Application $record
                                ): string => $record->data['revenue_model_other'] ?? ''),
                            Placeholder::make('review_competitors')->label(__('Who are your current competitors? Locally and regionally?'))
                                ->content(fn(Application $record): string => $record->data['competitors'] ?? ''),
                            Placeholder::make('review_funding')->label(__('Did you get any funding so far?'))
                                ->content(fn(Application $record): string => $record->data['funding'] ?? ''),
                            Placeholder::make('review_funding_other')->label(__('Please Specify'))
                                ->content(fn(Application $record): string => $record->data['funding_other'] ?? ''),
                            Placeholder::make('review_challenges')->label(__('What are the greatest challenges facing the implementation of your idea? If any please choose.'))
                                ->content(fn(Application $record): string => $record->data['challenges'] ?? ''),
                            Placeholder::make('review_challenges_other')->label(__('Please Specify'))
                                ->content(fn(Application $record): string => $record->data['challenges_other'] ?? ''),
                            Placeholder::make('review_traction')->label(__('What traction and leads were you able to gain? Please clarify.'))
                                ->content(fn(Application $record): string => $record->data['traction'] ?? ''),
                            Placeholder::make('review_market_validation')->label(__('What is your market validation strategy?'))
                                ->content(fn(Application $record): string => $record->data['market_validation'] ?? ''),
                            Placeholder::make('review_sdg')->label(__('Does your solution meet one or more of the SDGs (Sustainable Development Goals)?'))
                                ->content(fn(Application $record): string => $record->data['sdg'] ?? ''),
                            Placeholder::make('review_generating_revenue')->label(__('Is the solution generating revenue already?'))
                                ->content(fn(Application $record): string => $record->data['generating_revenue'] ?? ''),
                            Placeholder::make('review_needs')->label(__('What are the three top needs for your solution that the funding would fulfill?'))
                                ->content(fn(Application $record): string => $record->data['needs'] ?? ''),
                        ]),
                    Section::make(__('Strategy'))
                        ->schema([
                            Placeholder::make('review_strategy')->label('What are the upcoming milestones you aim to achieve throughout the programme. Please provide specific measurable milestones and the expected completion dates.')
                                ->content(fn(Application $record): string => $record->data['strategy'] ?? ''),
                        ]),
                    Section::make(__('General Information'))
                        ->schema([
                            Placeholder::make('review_business_skills')->label(__('Which of the following business skills do you have?'))
                                ->content(fn(Application $record): string => $record->data['business_skills'] ?? ''),
                            Placeholder::make('review_startup_experience')->label(__('Do you have any knowledge or experience in entrepreneurship/startups?'))
                                ->content(fn(Application $record): string => $record->data['startup_experience'] ?? ''),
                            Placeholder::make('review_experience_specification')->label(__('Please specify your experience'))
                                ->content(fn(Application $record
                                ): string => $record->data['experience_specification'] ?? ''),
                            Placeholder::make('review_new_skill')->label(__('If you are looking to acquire one new skill, what would it be?'))
                                ->content(fn(Application $record): string => $record->data['new_skill'] ?? ''),
                            Placeholder::make('review_program_discovery')->label(__('How did you hear about the Orange Corners Incubation Programme?'))
                                ->content(fn(Application $record): string => $record->data['program_discovery'] ?? ''),
                            Placeholder::make('review_participation')->label(__('If you were selected, can you participate in a 3-day bootcamp?'))
                                ->content(fn(Application $record): string => $record->data['participation'] ?? ''),
                            Placeholder::make('review_prototype_link')->label(__('Please share a link to the prototype of your product so that we can get a better understanding of its features and functionalities.'))
                                ->content(fn(Application $record): string => $record->data['prototype_link'] ?? ''),
                        ]),
                ])
        ];

        $acceleration_form = [
            Wizard\Step::make('Startup Overview')->icon('heroicon-s-light-bulb')
                ->schema([
                    Textarea::make('value_proposition')->label('Briefly describe your startup’s value proposition.')->required(),
                    Textarea::make('problem')->label('What problem are you solving?')->required(),
                    Textarea::make('solution')->label('Describe your solution and how it addresses the problem.')->required(),
                    Select::make('sector')->label('What industry sector does your product/service target?')->options([
                        'Agriculture'                   => __('Agriculture'),
                        'Automotive'                    => __('Automotive'),
                        'Banking'                       => __('Banking & Finance'),
                        'Construction'                  => __('Construction'),
                        'Education'                     => __('Education'),
                        'Energy'                        => __('Energy'),
                        'Entertainment'                 => __('Entertainment'),
                        'Environmental Services'        => __('Environmental Services'),
                        'Fashion'                       => __('Fashion'),
                        'Food Processing and Nutrition' => __('Food Processing and Nutrition'),
                        'Healthcare'                    => __('Healthcare'),
                        'Hospitality'                   => __('Hospitality'),
                        'Information Technology (IT)'   => __('Information Technology (IT)'),
                        'Legal Services'                => __('Legal Services'),
                        'Logistics & Transportation'    => __('Logistics & Transportation'),
                        'Manufacturing'                 => __('Manufacturing'),
                        'Media & Communications'        => __('Media & Communications'),
                        'Real Estate'                   => __('Real Estate'),
                        'Sports & Recreation'           => __('Sports & Recreation'),
                        'Telecommunications'            => __('Telecommunications'),
                        'Travel & Tourism'              => __('Travel & Tourism'),
                        'Other'                         => __('Other'),
                    ])->required()->reactive(),
                    TextInput::make('sector_other')->label('Please Specify')
                        ->hidden(fn(callable $get) => $get('sector') !== 'Other'),
                ])->afterValidation(function (Get $get) use ($form) {
                    $application = $form->getModelInstance();
                    $application->update([
                        'data' => array_merge($application->data, [
                            'value_proposition' => $get('value_proposition'),
                            'problem'           => $get('problem'),
                            'solution'          => $get('market_fit'),
                            'sector'            => $get('sector'),
                            'sector_other'      => $get('sector_other'),
                        ])
                    ]);
                    Notification::make()->title(__('Saved successfully'))->success()->send();
                }),
            Wizard\Step::make('Current Status')->icon('heroicon-s-check')
                ->schema([
                    Select::make('stage')->label('What is your current stage of development?')->options([
                        'MVP'                => __('Proof-of-Concept'),
                        'Market Ready'       => __('Market Ready'),
                        'Revenue-Generating' => __('Revenue-Generating'),
                    ])->required(),
                    Select::make('product_launched')->label('Have you launched any version of your product?')
                        ->options([
                            'Yes' => __('Yes'),
                            'No'  => __('No'),
                        ])->required()->reactive(),
                    Textarea::make('launch_details')->label('Can you provide details?')
                        ->hidden(fn(callable $get) => $get('product_launched') !== 'Yes'),

                    Textarea::make('current_traction')->label('What is your current traction (users, customers, revenue, partnerships, etc.)?'),
                    Select::make('funding_status')->label('What is your current funding status?')
                        ->options([
                            'Bootstrapped' => __('Bootstrapped'),
                            'Pre-seed'     => __('Pre-seed'),
                            'Seed'         => __('Seed'),
                            'Series A'     => __('Series A'),
                            'Other'        => __('Other'),
                        ])->reactive(),
                    TextInput::make('funding_status_other')->label('Please Specify')
                        ->hidden(fn(callable $get) => $get('funding_status') !== 'Other'),
                    Textarea::make('achieved_milestones')->label('What key milestones have you achieved so far?')->required(),
                ])->afterValidation(function (Get $get) use ($form) {
                    $application = $form->getModelInstance();
                    $application->update(
                        [
                            'data' => array_merge($application->data, [
                                'stage'                => $get('stage'),
                                'product_launched'     => $get('product_launched'),
                                'launch_details'       => $get('launch_details'),
                                'current_traction'     => $get('current_traction'),
                                'funding_status'       => $get('funding_status'),
                                'funding_status_other' => $get('funding_status_other'),
                                'achieved_milestones'  => $get('achieved_milestones'),
                            ])
                        ]);
                    Notification::make()->title(__('Saved successfully'))->success()->send();
                }),
            Wizard\Step::make('Market Research and Target Customer')->icon('heroicon-s-chart-bar')
                ->schema([
                    Textarea::make('target_customers')->label('Who are your target customers?')->required(),
                    Textarea::make('market_size')->label('What is the size of your target market? Provide any relevant data or estimates.')->required(),
                    Textarea::make('market_identification')->label('How did you identify your target market? Describe any research methods used (surveys, interviews, focus groups, etc.).')->required(),
                    Textarea::make('competitive_advantage')->label('What is your competitive advantage? How do you differentiate your startup in the market?')->required(),
                ])->afterValidation(function (Get $get) use ($form) {
                    $application = $form->getModelInstance();
                    $application->update(
                        [
                            'data' => array_merge($application->data, [
                                'target_customers'      => $get('target_customers'),
                                'market_size'           => $get('market_size'),
                                'market_identification' => $get('market_identification'),
                                'competitive_advantage' => $get('competitive_advantage'),
                            ])
                        ]);
                    Notification::make()->title(__('Saved successfully'))->success()->send();
                }),
            Wizard\Step::make('Business Model and Go-To-Market Strategy')->icon('heroicon-s-briefcase')
                ->schema([
                    Select::make('revenue_model')->label('Please describe your revenue model:')
                        ->options([
                            'Subscription Model'               => __('Subscription Model'),
                            'Transaction Fees'                 => __('Transaction Fees'),
                            'Advertising Model'                => __('Advertising Model'),
                            'Freemium Model'                   => __('Freemium Model'),
                            'E-commerce Model'                 => __('E-commerce Model'),
                            'Licensing Model'                  => __('Licensing Model'),
                            'Affiliate Marketing'              => __('Affiliate Marketing'),
                            'Pay-per-Use Model'                => __('Pay-per-Use Model'),
                            'Rental/Leasing Model'             => __('Rental/Leasing Model'),
                            'Franchise Model'                  => __('Franchise Model'),
                            'Consulting/Professional Services' => __('Consulting/Professional Services Model'),
                            'Crowdsourcing Model'              => __('Crowdsourcing Model'),
                            'Partnership Model'                => __('Partnership Model'),
                            'Other'                            => __('Other'),
                        ])->required()->reactive(),
                    TextInput::make('revenue_model_other')->label('Please Specify')
                        ->hidden(fn(callable $get) => $get('revenue_model') !== 'Other'),
                    Textarea::make('go_to_market_strategy')->label('What is your go-to-market strategy?')->required(),
                    Textarea::make('customer_acquisition')->label('What channels and tactics are you using for customer acquisition and growth?'),
                ])->afterValidation(function (Get $get) use ($form) {
                    $application = $form->getModelInstance();
                    $application->update(
                        [
                            'data' => array_merge($application->data, [
                                'revenue_model'         => $get('revenue_model'),
                                'revenue_model_other'   => $get('revenue_model_other'),
                                'go_to_market_strategy' => $get('go_to_market_strategy'),
                                'customer_acquisition'  => $get('customer_acquisition'),
                            ])
                        ]);
                    Notification::make()->title(__('Saved successfully'))->success()->send();
                }),
            Wizard\Step::make('Team')->icon('heroicon-s-users')
                ->schema([
                    TextInput::make('team_count')->label('How many members does your team consist of?')
                        ->numeric()
                        ->minValue(1)
                        ->required(),
                    Repeater::make('team_members')->label('Team Members')->addActionLabel(__('Add Team Member'))
                        ->schema([
                            TextInput::make('name')->label('Name')->required(),
                            TextInput::make('role')->label('Role')->required(),
                            PhoneInput::make('phone')->label('Phone')->required()->default(auth()->user()->phone)
                                ->defaultCountry('PS')
                                ->preferredCountries(['ps', 'il'])
                                ->showSelectedDialCode()
                                ->validateFor()
                                ->i18n([
                                    'il' => 'Palestine'
                                ]),
                            TextInput::make('email')->label('Email')->required()->email(),
                        ])->columns(4)->reorderableWithButtons()->inlineLabel(false)->required(),
                    Select::make('full_time_team_members')->label('Do you have at least two full-time team members dedicated to participating fully in all activities of the program?')
                        ->options([
                            'Yes'   => __('Yes'),
                            'No'    => __('No'),
                            'Other' => __('Other'),
                        ])->required()->reactive(),
                    TextInput::make('full_time_team_members_other')->label('Please Specify')->hidden(fn(callable $get
                    ) => $get('full_time_team_members') !== 'Other'),
                ])->afterValidation(function (Get $get) use ($form) {
                    $application = $form->getModelInstance();
                    $application->update(
                        [
                            'data' => array_merge($application->data, [
                                'team_count'                   => $get('team_count'),
                                'team_members'                 => $get('team_members'),
                                'full_time_team_members'       => $get('full_time_team_members'),
                                'full_time_team_members_other' => $get('full_time_team_members_other'),
                            ])
                        ]);
                    Notification::make()->title(__('Saved successfully'))->success()->send();
                }),
            Wizard\Step::make('Financials')->icon('heroicon-s-currency-dollar')
                ->schema([
                    TextInput::make('investment_to_date')->label('How much have you invested in the company to date?'),
                    Select::make('funding_raised')->label('Have you raised any funding for your company (incl. award/grant money)?')
                        ->options([
                            'Yes' => __('Yes'),
                            'No'  => __('No'),
                        ])->required(),
                    TextInput::make('additional_funding')->label('How much additional funding do you seek to raise during or after the acceleration program?')->required(),
                ])->afterValidation(function (Get $get) use ($form) {
                    $application = $form->getModelInstance();
                    $application->update(
                        [
                            'data' => array_merge($application->data, [
                                'investment_to_date' => $get('investment_to_date'),
                                'funding_raised'     => $get('funding_raised'),
                                'additional_funding' => $get('additional_funding'),
                            ])
                        ]);
                    Notification::make()->title(__('Saved successfully'))->success()->send();
                }),
            Wizard\Step::make('Legal Status and Registration')->icon('heroicon-s-shield-check')
                ->schema([
                    Select::make('registered_legal_entity')->label('Have you registered your startup as a legal entity?')
                        ->options([
                            'Yes' => __('Yes'),
                            'No'  => __('No'),
                        ])->required()->reactive(),
                    Textarea::make('registration_details')->label('Please provide details about the registration process (e.g., type of entity, country of registration, date of registration).')
                        ->hidden(fn(callable $get) => $get('registered_legal_entity') !== 'Yes'),
                    Textarea::make('legal_issues')->label('Are there any legal issues or intellectual property concerns related to your startup?')
                        ->hidden(fn(callable $get) => $get('registered_legal_entity') !== 'Yes'),
                    Select::make('in_process_registration')->label('Are you currently in the process of registering your startup?')
                        ->options([
                            'Yes' => __('Yes'),
                            'No'  => __('No'),
                        ])->hidden(fn(callable $get) => $get('registered_legal_entity') !== 'No')->reactive(),
                    Textarea::make('registration_timeline')->label('Please provide an estimated timeline for completion.')
                        ->hidden(fn(callable $get) => $get('in_process_registration') !== 'Yes'),
                    Textarea::make('current_registration_step')->label('Where are you now in this timeline (which step have you reached so far)?')
                        ->hidden(fn(callable $get) => $get('in_process_registration') !== 'Yes'),
                    Textarea::make('registration_steps_challenges')->label('What steps are required from this point forward to complete the registration process? Are there any legal issues or intellectual property concerns related to your startup?')
                        ->hidden(fn(callable $get) => $get('in_process_registration') !== 'Yes'),
                ])->afterValidation(function (Get $get) use ($form) {
                    $application = $form->getModelInstance();
                    $application->update(
                        [
                            'data' => array_merge($application->data, [
                                'registered_legal_entity'       => $get('registered_legal_entity'),
                                'registration_details'          => $get('registration_details'),
                                'legal_issues'                  => $get('legal_issues'),
                                'in_process_registration'       => $get('in_process_registration'),
                                'registration_timeline'         => $get('registration_timeline'),
                                'current_registration_step'     => $get('current_registration_step'),
                                'registration_steps_challenges' => $get('registration_steps_challenges'),
                            ])
                        ]);
                    Notification::make()->title(__('Saved successfully'))->success()->send();
                }),
            Wizard\Step::make('Vision and Goals')->icon('heroicon-s-light-bulb')
                ->schema([
                    Textarea::make('vision')->label('What is your vision for the startup in the next 1-3 years?'),
                    Textarea::make('aimed_milestones')->label('What are the key milestones you aim to achieve during the acceleration program?')->required(),
                    Textarea::make('support_from_accelerator')->label('How can Flow Accelerator support you in scaling your business?'),
                ])->afterValidation(function (Get $get) use ($form) {
                    $application = $form->getModelInstance();
                    $application->update(
                        [
                            'data' => array_merge($application->data, [
                                'vision'                   => $get('vision'),
                                'aimed_milestones'         => $get('aimed_milestones'),
                                'support_from_accelerator' => $get('support_from_accelerator'),
                            ])
                        ]);
                    Notification::make()->title(__('Saved successfully'))->success()->send();
                }),
            Wizard\Step::make('Additional Information')->icon('heroicon-s-information-circle')
                ->schema([
                    Select::make('participated_in_programs')->label('Have you participated in any other incubation or acceleration programs?')
                        ->options([
                            'Yes' => __('Yes'),
                            'No'  => __('No'),
                        ])->required()->reactive(),
                    Textarea::make('program_details')->label('If yes, please provide details.')
                        ->hidden(fn(callable $get) => $get('participated_in_programs') !== 'Yes'),
                    FileUpload::make('attachments')->label('Please upload your pitch deck or any relevant information you would like to share with us.')
                        ->multiple()->appendFiles()->maxFiles(5)->maxSize(10240)->directory('application-attachments')
                        ->hint(__("Maximum size: 10MB, Maximum files: 5")),
                ])->afterValidation(function (Get $get) use ($form) {
                    $application = $form->getModelInstance();
                    $application->update(
                        [
                            'data' => array_merge($application->data, [
                                'participated_in_programs' => $get('participated_in_programs'),
                                'program_details'          => $get('program_details'),
                                'attachments'              => $get('pitch_deck'),
                            ])
                        ]);
                    Notification::make()->title(__('Saved successfully'))->success()->send();
                }),
            Wizard\Step::make('Review')->icon('heroicon-s-check-circle')
                ->schema([
                    Placeholder::make('review_section')->hiddenLabel()->content(
                        new HtmlString('<div style="font-size: 24px;text-align: center">'.__("Please review your application before submitting").'</div>')
                    ),
                    Section::make(__('Personal Information'))
                        ->schema([
                            Placeholder::make('review_first_name')->label('First Name')
                                ->content(fn(Application $record): string => $record->data['first_name'] ?? ''),
                            Placeholder::make('review_last_name')->label('Last Name')
                                ->content(fn(Application $record): string => $record->data['last_name'] ?? ''),
                            Placeholder::make('review_email')->label('Email')
                                ->content(fn(Application $record): string => $record->data['email'] ?? ''),
                            Placeholder::make('review_dob')->label('Date of Birth')
                                ->content(fn(Application $record): string => $record->data['dob'] ?? ''),
                            Placeholder::make('review_phone')->label('Phone')
                                ->content(fn(Application $record): string => $record->data['phone'] ?? ''),
                            Placeholder::make('review_whatsapp')->label('Whatsapp')
                                ->content(fn(Application $record): string => $record->data['whatsapp'] ?? ''),
                            Placeholder::make('review_gender')->label('Gender')
                                ->content(fn(Application $record): string => $record->data['gender'] ?? ''),
                            Placeholder::make('review_residence')->label('Governorate of Residence')
                                ->content(fn(Application $record): string => $record->data['residence'] ?? ''),
                            Placeholder::make('review_residence_other')->label('Other Governorate')
                                ->content(fn(Application $record
                                ): string => $record->data['residence_other'] ?? ''),
                            Placeholder::make('review_description')->label('Describe Yourself')
                                ->content(fn(Application $record
                                ): string => $record->data['description'] ?? ''),
                            Placeholder::make('review_description_other')->label('Describe Yourself (Other)')
                                ->content(fn(Application $record
                                ): string => $record->data['description_other'] ?? ''),
                            Placeholder::make('review_occupation')->label('Occupation')
                                ->content(fn(Application $record): string => $record->data['occupation'] ?? ''),
                        ])->columns(3),
                    Section::make(__('Startup Overview'))
                        ->schema([
                            Placeholder::make('review_value_proposition')->label('Briefly describe your startup’s value proposition.')
                                ->content(fn(Application $record): string => $record->data['value_proposition'] ?? ''),
                            Placeholder::make('review_problem')->label('What problem are you solving?')
                                ->content(fn(Application $record): string => $record->data['problem'] ?? ''),
                            Placeholder::make('review_solution')->label('Describe your solution and how it addresses the problem.')
                                ->content(fn(Application $record): string => $record->data['solution'] ?? ''),
                            Placeholder::make('review_sector')->label('What industry sector does your product/service target?')
                                ->content(fn(Application $record): string => $record->data['sector'] ?? ''),
                            Placeholder::make('review_sector_other')->label('Please Specify')
                                ->content(fn(Application $record): string => $record->data['sector_other'] ?? ''),
                        ]),
                    Section::make(__('Current Status'))
                        ->schema([
                            Placeholder::make('review_stage')->label('What is your current stage of development?')
                                ->content(fn(Application $record): string => $record->data['stage'] ?? ''),
                            Placeholder::make('review_product_launched')->label('Have you launched any version of your product?')
                                ->content(fn(Application $record): string => $record->data['product_launched'] ?? ''),
                            Placeholder::make('review_launch_details')->label('Can you provide details?')
                                ->content(fn(Application $record): string => $record->data['launch_details'] ?? ''),
                            Placeholder::make('review_current_traction')->label('What is your current traction (users, customers, revenue, partnerships, etc.)?')
                                ->content(fn(Application $record): string => $record->data['current_traction'] ?? ''),
                            Placeholder::make('review_funding_status')->label('What is your current funding status?')
                                ->content(fn(Application $record): string => $record->data['funding_status'] ?? ''),
                            Placeholder::make('review_funding_status_other')->label('Please Specify')
                                ->content(fn(Application $record
                                ): string => $record->data['funding_status_other'] ?? ''),
                            Placeholder::make('review_achieved_milestones')->label('What key milestones have you achieved so far?')
                                ->content(fn(Application $record
                                ): string => $record->data['achieved_milestones'] ?? ''),
                        ]),
                    Section::make(__('Market Research and Target Customer'))
                        ->schema([
                            Placeholder::make('review_target_customers')->label('Who are your target customers?')
                                ->content(fn(Application $record): string => $record->data['target_customers'] ?? ''),
                            Placeholder::make('review_market_size')->label('What is the size of your target market? Provide any relevant data or estimates.')
                                ->content(fn(Application $record): string => $record->data['market_size'] ?? ''),
                            Placeholder::make('review_market_identification')->label('How did you identify your target market? Describe any research methods used (surveys, interviews, focus groups, etc.).')
                                ->content(fn(Application $record
                                ): string => $record->data['market_identification'] ?? ''),
                            Placeholder::make('review_competitive_advantage')->label('What is your competitive advantage? How do you differentiate your startup in the market?')
                                ->content(fn(Application $record
                                ): string => $record->data['competitive_advantage'] ?? ''),
                        ]),
                    Section::make(__('Business Model and Go-To-Market Strategy'))
                        ->schema([
                            Placeholder::make('review_revenue_model')->label('Please describe your revenue model:')
                                ->content(fn(Application $record): string => $record->data['revenue_model'] ?? ''),
                            Placeholder::make('review_revenue_model_other')->label('Please Specify')
                                ->content(fn(Application $record
                                ): string => $record->data['revenue_model_other'] ?? ''),
                            Placeholder::make('review_go_to_market_strategy')->label('What is your go-to-market strategy?')
                                ->content(fn(Application $record
                                ): string => $record->data['go_to_market_strategy'] ?? ''),
                            Placeholder::make('review_customer_acquisition')->label('What channels and tactics are you using for customer acquisition and growth?')
                                ->content(fn(Application $record
                                ): string => $record->data['customer_acquisition'] ?? ''),
                        ]),
                    Section::make(__('Team'))
                        ->schema([
                            Placeholder::make('review_team_count')->label('Team Count')
                                ->content(fn(Application $record): string => $record->data['team_count'] ?? ''),
                            Placeholder::make('review_team_members')->label('Team Members')
                                ->content(fn(Application $record
                                ): string => json_encode($record->data['team_members'] ?? [])),
                            Placeholder::make('review_full_time_team_members')->label('Full-Time Team Members')
                                ->content(fn(Application $record
                                ): string => $record->data['full_time_team_members'] ?? ''),
                            Placeholder::make('review_full_time_team_members_other')->label('Please Specify')
                                ->content(fn(Application $record
                                ): string => $record->data['full_time_team_members_other'] ?? ''),
                        ]),
                    Section::make(__('Financials'))
                        ->schema([
                            Placeholder::make('review_investment_to_date')->label('How much have you invested in the company to date?')
                                ->content(fn(Application $record): string => $record->data['investment_to_date'] ?? ''),
                            Placeholder::make('review_funding_raised')->label('Have you raised any funding for your company (incl. award/grant money)?')
                                ->content(fn(Application $record): string => $record->data['funding_raised'] ?? ''),
                            Placeholder::make('review_additional_funding')->label('How much additional funding do you seek to raise during or after the acceleration program?')
                                ->content(fn(Application $record): string => $record->data['additional_funding'] ?? ''),
                        ]),
                    Section::make(__('Legal Status and Registration'))
                        ->schema([
                            Placeholder::make('review_registered_legal_entity')->label('Have you registered your startup as a legal entity?')
                                ->content(fn(Application $record
                                ): string => $record->data['registered_legal_entity'] ?? ''),
                            Placeholder::make('review_registration_details')->label('Please provide details about the registration process (e.g., type of entity, country of registration, date of registration).')
                                ->content(fn(Application $record
                                ): string => $record->data['registration_details'] ?? ''),
                            Placeholder::make('review_legal_issues')->label('Are there any legal issues or intellectual property concerns related to your startup?')
                                ->content(fn(Application $record): string => $record->data['legal_issues'] ?? ''),
                            Placeholder::make('review_in_process_registration')->label('Are you currently in the process of registering your startup?')
                                ->content(fn(Application $record
                                ): string => $record->data['in_process_registration'] ?? ''),
                            Placeholder::make('review_registration_timeline')->label('Please provide an estimated timeline for completion.')
                                ->content(fn(Application $record
                                ): string => $record->data['registration_timeline'] ?? ''),
                            Placeholder::make('review_current_registration_step')->label('Where are you now in this timeline (which step have you reached so far)?')
                                ->content(fn(Application $record
                                ): string => $record->data['current_registration_step'] ?? ''),
                            Placeholder::make('review_registration_steps_challenges')->label('What steps are required from this point forward to complete the registration process? Are there any legal issues or intellectual property concerns related to your startup?')
                                ->content(fn(Application $record
                                ): string => $record->data['registration_steps_challenges'] ?? ''),
                        ]),
                    Section::make(__('Vision and Goals'))
                        ->schema([
                            Placeholder::make('review_vision')->label('What is your vision for the startup in the next 1-3 years?')
                                ->content(fn(Application $record): string => $record->data['vision'] ?? ''),
                            Placeholder::make('review_aimed_milestones')->label('What are the key milestones you aim to achieve during the acceleration program?')
                                ->content(fn(Application $record
                                ): string => $record->data['aimed_milestones'] ?? ''),
                            Placeholder::make('review_support_from_accelerator')->label('How can Flow Accelerator support you in scaling your business?')
                                ->content(fn(Application $record
                                ): string => $record->data['support_from_accelerator'] ?? ''),
                        ]),
                    Section::make(__('Additional Information'))
                        ->schema([
                            Placeholder::make('review_participated_in_programs')->label('Have you participated in any other incubation or acceleration programs?')
                                ->content(fn(Application $record
                                ): string => $record->data['participated_in_programs'] ?? ''),
                            Placeholder::make('review_program_details')->label('If yes, please provide details.')
                                ->content(fn(Application $record): string => $record->data['program_details'] ?? ''),
                        ]),
                ])
        ];

        $application = $form->getModelInstance();
        $wizard      = match (optional($application->program)->level) {
            'ideation and innovation' => $ideation_from,
            'pre-incubation' => $pre_incubation_form,
            'incubation' => $incubation_form,
            'pre-acceleration' => $pre_acceleration_form,
            'acceleration' => $acceleration_form,
            default => []
        };

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
                                        ])->columns(3)->reorderableWithButtons()->inlineLabel(false)->hiddenLabel()->defaultItems(0)
                                        ->required(fn(callable $get) => ! in_array($get('description'),
                                            ['Student', 'Other']))
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
                    ...$wizard
                ])->columnSpan(2)->statePath('data')->nextAction(
                    fn(Action $action) => $action->label('Save and Continue')->translateLabel(),
                ),
            ]);
    }

    public static function table(Table $table): Table
    {

        $columns = [
            TextColumn::make('program.name')->label('Program')->translateLabel(),
            TextColumn::make('program.level')->label('Level')
                ->formatStateUsing(fn(string $state): string => ucwords(__($state), '- '))
        ];

        if (auth()->id() <= 5) {
            $columns = array_merge($columns, [
                TextColumn::make('data.first_name')->label('First Name'),
                TextColumn::make('data.last_name')->label('Last Name'),
                TextColumn::make('data.email')->label('Email')->translateLabel(),
            ]);
        }

        $columns = array_merge($columns, [
            TextColumn::make('status')->label('Status')
                ->getStateUsing(fn($record) => match ($record->status) {
                    'Draft' => in_array($record->program->status, ['open', 'draft']) ? 'Draft' : 'Incomplete',
                    'Submitted' => in_array($record->program->status,
                        ['open', 'draft']) ? 'Submitted' : ucwords($record->program->status),
                    default => $record->status
                })->badge()
                ->color(fn(string $state): string => match ($state) {
                    'Submitted' => 'success',
                    'Incomplete' => 'danger',
                    'Draft' => 'info',
                    'In Review' => 'warning',
                    'Decision Made' => 'gray',
                    default => 'gray',
                })
                ->sortable(),
            TextColumn::make('created_at')->label('Created at')->dateTime('Y-m-d H:i'),
        ]);

        return $table
            ->columns($columns)
            ->filters([
                SelectFilter::make('program')->relationship('program', 'name',
                    fn(Builder $query) => $query->where('status', 'open'))->label('Program')
                    ->getOptionLabelFromRecordUsing(function (Program $record) {
                        $label = $record->name;
                        $label .= $record->level ? " - ".ucwords($record->level, '- ') : "";
                        $label .= $record->activity ? " - ".$record->activity : "";

                        return $label;
                    }),
                SelectFilter::make('status')->options([
                    'Draft'         => __('Draft'),
                    'Submitted'     => __('Submitted'),
                    'Incomplete'    => __('Incomplete'),
                    'In Review'     => __('In Review'),
                    'Decision Made' => __('Decision Made'),
                ])->label('Status'),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->recordUrl(function ($record) {
                if ($record->trashed()) {
                    return null;
                }

                return $record->status === 'Draft' ? ApplicationResource::getUrl('edit',
                    [$record]) : ApplicationResource::getUrl('view', [$record]);
            });
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {

        $ideation_infolist = [
            \Filament\Infolists\Components\Section::make(__('Idea & Challenges'))
                ->schema([
                    TextEntry::make('data.has_idea')->label('Do you currently have a business idea or project?')->html(),
                    TextEntry::make('data.circular_economy')->label(fn(Application $record
                    ): string => 'Is your business idea or project focused on a specific sector within the '.optional($record->program)->activity.'?')->html(),
                    TextEntry::make('data.idea_stage')->label('In which stage is your idea?')->html(),
                    TextEntry::make('data.idea_sector')->label('Which sector is it, and what specific problem or challenge does your idea aim to address? (Please limit your response to 150 words.)')->html(),
                    TextEntry::make('data.idea_description')->label('Please provide a brief description of your idea')->html(),
                    TextEntry::make('data.has_challenge')->label(fn(Application $record
                    ): string => 'Do you have a specific challenge you aim to solve within the '.optional($record->program)->activity.' sectors?')->html(),
                    TextEntry::make('data.challenge_description')->label('Which sector is it, and what specific challenge would you like to solve?')->html(),
                ])->columns(1),
            \Filament\Infolists\Components\Section::make(__('Entrepreneurial Skills'))
                ->schema([
                    TextEntry::make('data.creative_solution')->label('Provide an example of a creative solution you developed to address a challenge. What inspired your approach, and what was the outcome?')->html(),
                    TextEntry::make('data.problem_solving_scenario')->label('Share a scenario where you faced a significant obstacle while working on a project. How did you identify the problem, and what steps did you take to overcome it?')->html(),
                    TextEntry::make('data.participation_goals')->label('What do you hope to achieve by participating in the ideation workshop? Are there specific skills or insights you\'re looking to gain from the experience?')->html(),
                ])->columns(1),
            \Filament\Infolists\Components\Section::make(__('Generic Questions'))
                ->schema([
                    TextEntry::make('data.skills_expertise')->label('Please tell us about your skills and areas of expertise. This could include technical skills such as programming languages, or data analysis techniques, as well as non-technical skills such as communication, problem-solving, project management, or leadership abilities. Feel free to highlight any relevant experiences or accomplishments.')->html(),
                    TextEntry::make('individual_or_team')->label('Are you applying as an individual or as part of a team?')->html(),
                    TextEntry::make('data.team_count')->label('How many team members will participate in the problem-solving workshop?')->html(),
                    RepeatableEntry::make('data.team_members')->label('Team Members')
                        ->schema([
                            TextEntry::make('name')->label('Name'),
                            TextEntry::make('role')->label('Role'),
                            TextEntry::make('phone')->label('Phone'),
                            TextEntry::make('email')->label('Email'),
                        ])->columns(4),
                    TextEntry::make('data.startup_experience')->label('Do you have any knowledge or experience in entrepreneurship/startups?')->html(),
                    TextEntry::make('data.experience_specification')->label('Please specify your experience')->html(),
                    TextEntry::make('data.new_skill')->label('If you are looking to acquire one new skill, what would it be?')->html(),
                    TextEntry::make('data.program_discovery')->label('How did you hear about the PIEC Programme?')->html(),
                    TextEntry::make('data.program_discovery_other')->label('Please Specify')->html(),
                    TextEntry::make('data.commitment')->label('Are you able to commit to attending all scheduled related workshops and sessions throughout the problem solving workshop over three days?')->html(),
                    TextEntry::make('data.commitment_other')->label('Please Specify')->html(),
                    TextEntry::make('data.continuation_plan')->label('Do you plan to continue working on the idea you develop, by participating in incubation and acceleration programs after the innovation challenge concludes?')->html(),
                    TextEntry::make('data.additional_info')->label('Anything you\'d like to share with us? Please share links to any online portfolios, websites, or repositories showcasing your creative work. Briefly describe your role and contributions to each project')->html(),
                ])->columns(1),
        ];

        $pre_incubation_infolist = [
            \Filament\Infolists\Components\Section::make(__('Problem & Need'))
                ->schema([
                    TextEntry::make('data.problem')->label('What specific problem or need does your startup address?')->html(),
                    TextEntry::make('data.target')->label('Who is affected by this problem? And who’s your target segment?')->html(),
                    TextEntry::make('data.identify')->label('How did you identify this problem?')->html(),
                ])->columns(1),
            \Filament\Infolists\Components\Section::make(__('Solution & Stage'))
                ->schema([
                    TextEntry::make('data.solution')->label('Describe your proposed solution to the problem')->html(),
                    TextEntry::make('data.unique')->label('What makes your solution unique or innovative?')->html(),
                    TextEntry::make('data.alternatives')->label('How does your solution address the problem better than existing alternatives?')->html(),
                    TextEntry::make('data.sector')->label('What industry sector does your product/service target?')->html(),
                    TextEntry::make('data.sector_other')->label('Please Specify')->html(),
                    TextEntry::make('data.stage')->label('What stage is your solution currently in?')->html(),
                    TextEntry::make('data.have_prototype')->label('Have you developed a prototype or proof-of-concept?')->html(),
                    TextEntry::make('data.prototype_details')->label('Please provide us with details')->html(),
                    TextEntry::make('data.duration')->label('How long have you been working on this solution?')->html(),
                    TextEntry::make('data.customers')->label('Do you have any customers or users currently?')->html(),
                    TextEntry::make('data.customers_count')->label('How many customers and what is their feedback?')->html(),
                    TextEntry::make('data.individual_or_team')->label('Are you applying as an individual or as part of a team?')->html(),
                    RepeatableEntry::make('data.team_members')->label('Team Members')
                        ->schema([
                            TextEntry::make('name')->label('Name'),
                            TextEntry::make('role')->label('Role'),
                            TextEntry::make('phone')->label('Phone'),
                            TextEntry::make('email')->label('Email'),
                        ])->columns(4),
                ])->columns(1),
            \Filament\Infolists\Components\Section::make(__('Next Milestones'))
                ->schema([
                    TextEntry::make('data.milestones')->label('What are the next key milestones you aim to achieve in the next 3-6 months?')->html(),
                    TextEntry::make('data.resources')->label('What resources or support do you need to achieve these milestones?')->html(),
                ])->columns(1),
            \Filament\Infolists\Components\Section::make(__('Additional Information'))
                ->schema([
                    TextEntry::make('data.why')->label('Why do you want to join our pre-incubation program?')->html(),
                    TextEntry::make('data.achieve')->label('What do you hope to achieve by the end of the program?')->html(),
                    TextEntry::make('data.program_discovery')->label('How did you hear about the PIEC Programme?')->html(),
                    TextEntry::make('data.program_discovery_other')->label('Please Specify')->html(),
                    RepeatableEntry::make('data.attachments')->label('Attachments')
                        ->schema([
                            TextEntry::make('')->formatStateUsing(fn(string $state
                            ): HtmlString => new HtmlString("<a href='".Storage::url($state)."' download>".__('Download Attachment')."</a>"))
                        ])->columns(1),
                ])->columns(1),
        ];

        $incubation_infolist = [
            \Filament\Infolists\Components\Section::make(__('Startup Overview'))
                ->schema([
                    TextEntry::make('data.idea')->label('Please provide a brief description of your business or idea.'),
                    TextEntry::make('data.problem')->label('What problem are you solving?'),
                    TextEntry::make('data.fit')->label('How did you validate the problem-solution fit?'),
                    TextEntry::make('data.solution')->label('Describe your solution and how it addresses the problem. Please make sure to clarify your value proposition'),
                    TextEntry::make('data.sector')->label('What industry sector does your product/service target?'),
                    TextEntry::make('data.sector_other')->label('Please Specify'),
                ])->columns(1),
            \Filament\Infolists\Components\Section::make(__('Current Status'))
                ->schema([
                    TextEntry::make('data.stage')->label('What stage is your solution currently in?'),
                    TextEntry::make('data.features')->label('Have you identified the must-have features that your MVP should include?'),
                    TextEntry::make('data.milestones')->label('What key milestones have you achieved so far?'),
                ])->columns(1),
            \Filament\Infolists\Components\Section::make(__('Market Research'))
                ->schema([
                    TextEntry::make('data.target')->label('Who are your target customers?'),
                    TextEntry::make('data.how_target')->label('How did you identify your target market? Why do you believe this target market presents a good opportunity to start your business? Describe any research methods used (surveys, interviews, focus groups, etc.)'),
                    TextEntry::make('data.advantage')->label('What is your competitive advantage? How do you plan to differentiate your startup in the market?'),
                ])->columns(1),
            \Filament\Infolists\Components\Section::make(__('Team'))
                ->schema([
                    RepeatableEntry::make('data.team_members')->label('Team Members')
                        ->schema([
                            TextEntry::make('name')->label('Name'),
                            TextEntry::make('role')->label('Role'),
                            TextEntry::make('phone')->label('Phone'),
                            TextEntry::make('email')->label('Email'),
                        ])->columns(4),
                ])->columns(1),
            \Filament\Infolists\Components\Section::make(__('Vision and Goals'))
                ->schema([
                    TextEntry::make('data.vision')->label('What is your vision for the startup in the next 1-3 years?'),
                    TextEntry::make('data.achieve')->label('What are the key milestones you aim to achieve during the incubation program?'),
                    TextEntry::make('data.support')->label('How can Flow Accelerator support you in developing your MVP?'),
                ])->columns(1),
            \Filament\Infolists\Components\Section::make(__('Additional Information'))
                ->schema([
                    TextEntry::make('data.other')->label('Have you participated in any other incubation or acceleration programs? If yes, provide details.'),
                    TextEntry::make('data.committed')->label('Are you committed to attending the training sessions, mentoring sessions, and completing deliverables?'),
                    TextEntry::make('data.issues')->label('Are there any legal issues or intellectual property concerns related to your startup?'),
                    RepeatableEntry::make('data.attachments')->label('Attachments')
                        ->schema([
                            TextEntry::make('')->formatStateUsing(fn(string $state
                            ): HtmlString => new HtmlString("<a href='".Storage::url($state)."' download>".__('Download Attachment')."</a>"))
                        ])->columns(1),
                ])->columns(1),
        ];

        $pre_acceleration_infolist = [
            \Filament\Infolists\Components\Section::make(__('Solution'))
                ->schema([
                    TextEntry::make('data.sector')->label('In which industry does your solution fit?'),
                    TextEntry::make('data.sector_other')->label('Please Specify'),
                    TextEntry::make('data.stage')->label('What stage is your solution currently in?'),
                    TextEntry::make('data.solution_name')->label('What is the name of your solution?'),
                    TextEntry::make('data.solution')->label('In a short paragraph, describe your solution briefly?'),
                    TextEntry::make('data.solution_type')->label('Is your solution a software, hardware or System?'),
                    TextEntry::make('data.solution_type_other')->label('Please Specify'),
                    TextEntry::make('data.problem')->label('What Problem / Challenge does your solution tackle?'),
                    TextEntry::make('data.target')->label('Who are your target market and potential customers?'),
                    TextEntry::make('data.value_proposition')->label('How does your solution solve the problem and what\'s your value proposition?'),
                    TextEntry::make('data.competitive_advantage')->label('What is your competitive advantage?'),
                    TextEntry::make('data.impact')->label('What types of impact does your solution make?'),
                    TextEntry::make('data.impact_other')->label('Please Specify'),
                    TextEntry::make('data.validation_duration')->label('When did you start with the idea validation?'),
                    TextEntry::make('data.validation_process')->label('Tell us briefly about your proof of concept/idea validation process?'),
                    TextEntry::make('data.startup_registered')->label('Is your Startup registered?'),
                    TextEntry::make('data.solution_launch')->label('When did you launch your solution in the market?'),
                    TextEntry::make('data.go_to_market_strategy')->label('What was your go-to-market Strategy?'),
                    TextEntry::make('data.go_to_market_strategy_other')->label('Please Specify'),
                    TextEntry::make('data.business_model')->label('What is your business model?'),
                    TextEntry::make('data.business_model_other')->label('Please Specify'),
                    TextEntry::make('data.revenue_model')->label('What is your revenue model? How does your business generate revenue?'),
                    TextEntry::make('data.revenue_model_other')->label('Please Specify'),
                    TextEntry::make('data.competitors')->label('Who are your current competitors? Locally and regionally?'),
                    TextEntry::make('data.funding')->label('Did you get any funding so far?'),
                    TextEntry::make('data.funding_other')->label('Please Specify'),
                    TextEntry::make('data.challenges')->label('What are the greatest challenges facing the implementation of your idea? If any please choose.'),
                    TextEntry::make('data.challenges_other')->label('Please Specify'),
                    TextEntry::make('data.traction')->label('What traction and leads were you able to gain? Please clarify.'),
                    TextEntry::make('data.market_validation')->label('What is your market validation strategy?'),
                    TextEntry::make('data.generating_revenue')->label('Is the solution generating revenue already?'),
                    TextEntry::make('data.needs')->label('What are the three top needs for your solution that the funding would fulfill?'),
                    TextEntry::make('data.sdg')->label('Does your solution meet one or more of the SDGs (Sustainable Development Goals)?'),
                ])->columns(1),
            \Filament\Infolists\Components\Section::make(__('Team'))
                ->schema([
                    RepeatableEntry::make('data.team_members')->label('Team Members')
                        ->schema([
                            TextEntry::make('name')->label('Name'),
                            TextEntry::make('role')->label('Role'),
                            TextEntry::make('phone')->label('Phone'),
                            TextEntry::make('email')->label('Email'),
                        ])->columns(4),
                ])->columns(1),
            \Filament\Infolists\Components\Section::make(__('Strategy'))
                ->schema([
                    TextEntry::make('data.strategy')->label('What are the upcoming milestones you aim to achieve throughout the programme. Please provide specific measurable milestones and the expected completion dates.'),
                ])->columns(1),
            \Filament\Infolists\Components\Section::make(__('General Information'))
                ->schema([
                    TextEntry::make('data.business_skills')->label('Which of the following business skills do you have?'),
                    TextEntry::make('data.startup_experience')->label('Do you have any knowledge or experience in entrepreneurship/startups?'),
                    TextEntry::make('data.experience_specification')->label('Please specify your experience'),
                    TextEntry::make('data.new_skill')->label('If you are looking to acquire one new skill, what would it be?'),
                    TextEntry::make('data.program_discovery')->label('How did you hear about the Orange Corners Incubation Programme?'),
                    TextEntry::make('data.participation')->label('If you were selected, can you participate in a 3-day bootcamp?'),
                    TextEntry::make('data.prototype_link')->label('Please share a link to the prototype of your product so that we can get a better understanding of its features and functionalities.'),
                    RepeatableEntry::make('data.attachments')->label('Attachments')
                        ->schema([
                            TextEntry::make('')->formatStateUsing(fn(string $state
                            ): HtmlString => new HtmlString("<a href='".Storage::url($state)."' download>".__('Download Attachment')."</a>"))
                        ])->columns(1),
                ])->columns(1),
        ];

        $acceleration_infolist = [
            \Filament\Infolists\Components\Section::make(__('Startup Overview'))
                ->schema([
                    TextEntry::make('data.value_proposition')->label('Briefly describe your startup’s value proposition.'),
                    TextEntry::make('data.problem')->label('What problem are you solving?'),
                    TextEntry::make('data.solution')->label('Describe your solution and how it addresses the problem.'),
                    TextEntry::make('data.sector')->label('What industry sector does your product/service target?'),
                    TextEntry::make('data.sector_other')->label('Please Specify'),
                ])->columns(1),
            \Filament\Infolists\Components\Section::make(__('Current Status'))
                ->schema([
                    TextEntry::make('data.stage')->label('What is your current stage of development?'),
                    TextEntry::make('data.product_launched')->label('Have you launched any version of your product?'),
                    TextEntry::make('data.launch_details')->label('Can you provide details?'),
                    TextEntry::make('data.current_traction')->label('What is your current traction (users, customers, revenue, partnerships, etc.)?'),
                    TextEntry::make('data.funding_status')->label('What is your current funding status?'),
                    TextEntry::make('data.funding_status_other')->label('Please Specify'),
                    TextEntry::make('data.achieved_milestones')->label('What key milestones have you achieved so far?'),
                ])->columns(1),
            \Filament\Infolists\Components\Section::make(__('Market Research and Target Customer'))
                ->schema([
                    TextEntry::make('data.target_customers')->label('Who are your target customers?'),
                    TextEntry::make('data.market_size')->label('What is the size of your target market? Provide any relevant data or estimates.'),
                    TextEntry::make('data.market_identification')->label('How did you identify your target market? Describe any research methods used (surveys, interviews, focus groups, etc.).'),
                    TextEntry::make('data.competitive_advantage')->label('What is your competitive advantage? How do you differentiate your startup in the market?'),
                ])->columns(1),
            \Filament\Infolists\Components\Section::make(__('Business Model and Go-To-Market Strategy'))
                ->schema([
                    TextEntry::make('data.revenue_model')->label('Please describe your revenue model:'),
                    TextEntry::make('data.revenue_model_other')->label('Please Specify'),
                    TextEntry::make('data.go_to_market_strategy')->label('What is your go-to-market strategy?'),
                    TextEntry::make('data.customer_acquisition')->label('What channels and tactics are you using for customer acquisition and growth?'),
                ])->columns(1),
            \Filament\Infolists\Components\Section::make(__('Team'))
                ->schema([
                    TextEntry::make('data.team_count')->label('How many members does your team consist of?'),
                    RepeatableEntry::make('data.team_members')->label('Team Members')
                        ->schema([
                            TextEntry::make('name')->label('Name'),
                            TextEntry::make('role')->label('Role'),
                            TextEntry::make('phone')->label('Phone'),
                            TextEntry::make('email')->label('Email'),
                        ])->columns(4),
                    TextEntry::make('data.full_time_team_members')->label('Do you have at least two full-time team members dedicated to participating fully in all activities of the program?'),
                    TextEntry::make('data.full_time_team_members_other')->label('Please Specify'),
                ])->columns(1),
            \Filament\Infolists\Components\Section::make(__('Financials'))
                ->schema([
                    TextEntry::make('data.investment_to_date')->label('How much have you invested in the company to date?'),
                    TextEntry::make('data.funding_raised')->label('Have you raised any funding for your company (incl. award/grant money)?'),
                    TextEntry::make('data.additional_funding')->label('How much additional funding do you seek to raise during or after the acceleration program?'),
                ])->columns(1),
            \Filament\Infolists\Components\Section::make(__('Legal Status and Registration'))
                ->schema([
                    TextEntry::make('data.registered_legal_entity')->label('Have you registered your startup as a legal entity?'),
                    TextEntry::make('data.registration_details')->label('Please provide details about the registration process (e.g., type of entity, country of registration, date of registration).'),
                    TextEntry::make('data.legal_issues')->label('Are there any legal issues or intellectual property concerns related to your startup?'),
                    TextEntry::make('data.in_process_registration')->label('Are you currently in the process of registering your startup?'),
                    TextEntry::make('data.registration_timeline')->label('Please provide an estimated timeline for completion.'),
                    TextEntry::make('data.current_registration_step')->label('Where are you now in this timeline (which step have you reached so far)?'),
                    TextEntry::make('data.registration_steps_challenges')->label('What steps are required from this point forward to complete the registration process? Are there any legal issues or intellectual property concerns related to your startup?'),
                ])->columns(1),
            \Filament\Infolists\Components\Section::make(__('Vision and Goals'))
                ->schema([
                    TextEntry::make('data.vision')->label('What is your vision for the startup in the next 1-3 years?'),
                    TextEntry::make('data.aimed_milestones')->label('What are the key milestones you aim to achieve during the acceleration program?'),
                    TextEntry::make('data.support_from_accelerator')->label('How can Flow Accelerator support you in scaling your business?'),
                ])->columns(1),
            \Filament\Infolists\Components\Section::make(__('Additional Information'))
                ->schema([
                    TextEntry::make('data.participated_in_programs')->label('Have you participated in any other incubation or acceleration programs?'),
                    TextEntry::make('data.program_details')->label('If yes, please provide details.'),
                    RepeatableEntry::make('data.attachments')->label('Attachments')
                        ->schema([
                            TextEntry::make('')->formatStateUsing(fn(string $state
                            ): HtmlString => new HtmlString("<a href='".Storage::url($state)."' download>".__('Download Attachment')."</a>"))
                        ])->columns(1),
                ])->columns(1),
        ];

        // Get Application Model
        $application      = $infolist->getRecord();
        $program_infolist = match (optional($application->program)->level) {
            'ideation and innovation' => $ideation_infolist,
            'pre-incubation' => $pre_incubation_infolist,
            'incubation' => $incubation_infolist,
            'pre-acceleration' => $pre_acceleration_infolist,
            'acceleration' => $acceleration_infolist,
            default => []
        };

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
                ...$program_infolist
            ]);
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
        return $record->status === 'Draft'
               && in_array($record->program->status, ['open', 'draft'])
               && ($record->user_id === auth()->id() || auth()->id() <= 5);
    }

    public static function canView(Model $record): bool
    {
        return $record->user_id === auth()->id() || auth()->id() <= 5;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->id() <= 5;
    }

    public static function getEloquentQuery(): Builder
    {
        return auth()->id() <= 5
            ? parent::getEloquentQuery()->whereRelation('program', function ($query) {
                $query->whereIn('status', ['open', 'draft', 'in review']);
            }) : parent::getEloquentQuery()->where('user_id', auth()->id())->whereRelation('program', 'status', 'open');
    }

}
