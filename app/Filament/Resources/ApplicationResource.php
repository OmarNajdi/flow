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
                    TextInput::make('team_count')->label('How many team members will participate in the problem-solving workshop?')->numeric()->minValue(1)->required(),
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
                    Select::make('commitment')->label('Are you able to commit to attending all scheduled related workshops and sessions throughout the problem solving workshop over four days?')->options([
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
                            Placeholder::make('review_commitment')->label('Are you able to commit to attending all scheduled related workshops and sessions throughout the problem solving workshop over four days?')
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
                                ->content(fn(Application $record): HtmlString => new HtmlString($record->data['problem'] ?? '')),
                            Placeholder::make('review_target')->label('Who is affected by this problem? And who’s your target segment?')
                                ->content(fn(Application $record): HtmlString => new HtmlString($record->data['target'] ?? '')),
                            Placeholder::make('review_identify')->label('How did you identify this problem?')
                                ->content(fn(Application $record): HtmlString => new HtmlString($record->data['identify'] ?? '')),
                        ]),
                    Section::make(__('Solution & Stage'))
                        ->schema([
                            Placeholder::make('review_solution')->label('Describe your proposed solution to the problem')
                                ->content(fn(Application $record): HtmlString => new HtmlString($record->data['solution'] ?? '')),
                            Placeholder::make('review_unique')->label('What makes your solution unique or innovative?')
                                ->content(fn(Application $record): HtmlString => new HtmlString($record->data['unique'] ?? '')),
                            Placeholder::make('review_alternatives')->label('How does your solution address the problem better than existing alternatives?')
                                ->content(fn(Application $record): HtmlString => new HtmlString($record->data['alternatives'] ?? '')),
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
                                ->content(fn(Application $record): HtmlString => new HtmlString($record->data['milestones'] ?? '')),
                            Placeholder::make('review_resources')->label('What resources or support do you need to achieve these milestones?')
                                ->content(fn(Application $record): HtmlString => new HtmlString($record->data['resources'] ?? '')),
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

        $application = $form->getModelInstance();
        $wizard      = match (optional($application->program)->level) {
            'ideation and innovation' => $ideation_from,
            'pre-incubation' => $pre_incubation_form,
            default => $ideation_from
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
                    'Draft' => $record->program->status === 'open' ? 'Draft' : 'Incomplete',
                    'Submitted' => $record->program->status === 'open' ? 'Submitted' : ucwords($record->program->status),
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

        $header_actions = auth()->id() <= 5 ? [
            Tables\Actions\Action::make('Export')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(route('applications.export'))
                ->translateLabel()
        ] : [];

        return $table
            ->columns($columns)
            ->filters([
                SelectFilter::make('program')->relationship('program', 'name',fn (Builder $query) => $query->where('status', 'open'))->label('Program')
                    ->getOptionLabelFromRecordUsing(fn(Program $record) => "$record->name - $record->level"),
                SelectFilter::make('status')->options([
                    'Draft'        => __('Draft'),
                    'Submitted'    => __('Submitted'),
                    'Incomplete'   => __('Incomplete'),
                    'In Review'    => __('In Review'),
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

                return $record->status === 'draft' ? ApplicationResource::getUrl('edit',
                    [$record]) : ApplicationResource::getUrl('view', [$record]);
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
                    TextEntry::make('data.commitment')->label('Are you able to commit to attending all scheduled related workshops and sessions throughout the problem solving workshop over four days?')->html(),
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
                            TextEntry::make('')->formatStateUsing(fn (string $state): HtmlString => new HtmlString("<a href='".Storage::url($state)."' download>".__('Download Attachment')."</a>"))
                        ])->columns(1),
                ])->columns(1),
        ];

        // Get Application Model
        $application      = $infolist->getRecord();
        $program_infolist = match (optional($application->program)->level) {
            'ideation and innovation' => $ideation_infolist,
            'pre-incubation' => $pre_incubation_infolist,
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
        return $record->status === 'Draft' && $record->program->status == 'open' && ($record->user_id === auth()->id() || auth()->id() <= 5);
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
            ? parent::getEloquentQuery()->whereRelation('program', 'status', 'open')
            : parent::getEloquentQuery()->where('user_id', auth()->id())->whereRelation('program', 'status', 'open');
    }

}
