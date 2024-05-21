<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationResource\Pages;
use App\Filament\Resources\ApplicationResource\RelationManagers;
use App\Models\Application;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
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
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use HTMLPurifier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Livewire\Component;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

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
//                Hidden::make('user_id')->default(auth()->id()),
//                Hidden::make('program_id')->default(request('program')),
                Wizard::make([
                    Wizard\Step::make('Personal Questions')->icon('heroicon-s-user')
                        ->schema([
                            TextInput::make('first_name')->label('First Name / الاسم الأول')->required()->reactive()->default(auth()->user()->first_name)
                                ->afterStateUpdated(fn(Set $set, ?string $state) => $set('first_name',
                                    ucwords($state))),
                            TextInput::make('last_name')->label('Last Name / اسم العائلة')->required()->reactive()->default(auth()->user()->last_name)
                                ->afterStateUpdated(fn(Set $set, ?string $state) => $set('last_name',
                                    ucwords($state))),
                            TextInput::make('email')->email()->label('Email / البريد الإلكتروني')->required()->default(auth()->user()->email)->email(),
                            DatePicker::make('dob')->label('Date of Birth / تاريخ الميلاد')->native(false)->required()->default(auth()->user()->dob),
                            PhoneInput::make('phone')->label('Phone / رقم الهاتف')->required()->default(auth()->user()->phone)
                                ->defaultCountry('PS')
                                ->preferredCountries(['ps', 'il'])
                                ->showSelectedDialCode()
                                ->validateFor()
                                ->i18n([
                                    'il' => 'Palestine'
                                ]),
                            PhoneInput::make('whatsapp')->label('Whatsapp Number / رقم الواتساب')->required()->default(auth()->user()->whatsapp)
                                ->defaultCountry('PS')
                                ->preferredCountries(['ps', 'il'])
                                ->showSelectedDialCode()
                                ->validateFor()
                                ->i18n([
                                    'il' => 'Palestine'
                                ]),
                            Select::make('gender')->label('Gender / الجنس')->options([
                                'Male'   => 'Male / ذكر',
                                'Female' => 'Female / أنثى'
                            ])->required()->default(auth()->user()->gender),
                            Select::make('residence')->label('Governorate of Residence / المحافظة')->options([
                                'Jenin'         => 'Jenin / جنين',
                                'Tubas'         => 'Tubas / طوباس',
                                'Tulkarm'       => 'Tulkarm / طولكرم',
                                'Nablus'        => 'Nablus / نابلس',
                                'Qalqilya'      => 'Qalqilya / قلقيلية',
                                'Salfit'        => 'Salfit / سلفيت',
                                'Ramallah'      => 'Ramallah and al-Bireh / رام الله والبيرة',
                                'Jericho'       => 'Jericho / أريحا',
                                'Jerusalem'     => 'Jerusalem / القدس',
                                'Bethlehem'     => 'Bethlehem / بيت لحم',
                                'Hebron'        => 'Hebron / الخليل',
                                'North Gaza'    => 'North Gaza / شمال غزة',
                                'Gaza'          => 'Gaza / غزة',
                                'Deir al-Balah' => 'Deir al-Balah / دير البلح',
                                'Khan Yunis'    => 'Khan Yunis / خان يونس',
                                'Rafah'         => 'Rafah / رفح',
                                'Other'         => 'Other / أخرى',
                            ])->required()->reactive()->default(auth()->user()->residence),
                            TextInput::make('residence_other')->label('Other Governorate / محافظة أخرى')
                                ->hidden(fn(callable $get
                                ) => $get('residence') !== 'Other')->default(auth()->user()->residence_other),
                            Select::make('description')->label('Describe Yourself / صِف نفسك')->options([
                                'Student'      => 'Student / طالب',
                                'Professional' => 'Professional / موظف',
                                'Entrepreneur' => 'Entrepreneur / رائد أعمال',
                                'Other'        => 'Other / أخرى',
                            ])->required()->reactive()->default(auth()->user()->description),
                            TextInput::make('description_other')->label('Describe Yourself / صِف نفسك')
                                ->hidden(fn(callable $get
                                ) => $get('description') !== 'Other')->default(auth()->user()->description_other),
                            TextInput::make('occupation')->label('Occupation / المهنة')->required()
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
                        }),
                    Wizard\Step::make('Educational Background')->icon('heroicon-o-academic-cap')
                        ->schema([
                            Section::make('Education / التعليم')
                                ->schema([
                                    Repeater::make('education / التعليم')
                                        ->schema([
                                            Select::make('degree / الدرجة')
                                                ->options([
                                                    'High School'                 => 'High School / الثانوية العامة',
                                                    'Vocational/Technical School' => 'Vocational/Technical School / مدرسة مهنية',
                                                    'Bachelor'                    => 'Bachelor\'s Degree /  بكالوريوس',
                                                    'Master'                      => 'Master\'s Degree /  ماجستير',
                                                    'PhD'                         => 'Doctorate/Ph.D. / الدكتوراة',
                                                    'Certification'               => 'Certification / شهادة اخرى',
                                                ])
                                                ->required(),
                                            TextInput::make('school')->label('School/University / المدرسة/الجامعة')->required(),
                                            TextInput::make('major')->label('Major/Field of study / التخصص/مجال الدراسة')->required(),
                                            DatePicker::make('start_date')->label('Start Date / تاريخ البدء')->required()->extraInputAttributes(['type' => 'month']),
                                            Group::make([
                                                Toggle::make('current')->label('Currently Studying There / ما زلت أدرس ')->reactive(),
                                            ])->extraAttributes(['class' => 'h-full content-center']),
                                            DatePicker::make('end_date')->label('End Date / تاريخ الانتهاء')->extraInputAttributes(['type' => 'month'])
                                                ->hidden(fn(callable $get) => $get('current')),
                                        ])->columns(3)->reorderableWithButtons()->inlineLabel(false)->hiddenLabel()->defaultItems(1)->required()
                                ])
                        ])->afterValidation(function (Get $get) use ($form) {
                            $application = $form->getModelInstance();
                            $application->update(
                                [
                                    'data' => array_merge($application->data, [
                                        'education / التعليم' => $get('education / التعليم'),
                                    ])
                                ]);
                        }),
                    Wizard\Step::make('Professional Experience')->icon('heroicon-o-briefcase')
                        ->schema([
                            Section::make('Experience / الخبرة')
                                ->schema([
                                    Repeater::make('experience')->addActionLabel('Add Position / أضف منصب')
                                        ->schema([
                                            Select::make('type')
                                                ->options([
                                                    'full-time'     => 'Full-time / دوام كامل',
                                                    'part-time'     => 'Part-time / دوام جزئي',
                                                    'internship'    => 'Internship / التدريب',
                                                    'volunteer'     => 'Volunteer / التطوع',
                                                    'self-employed' => 'Self-employed / العمل الحر',
                                                    'freelance'     => 'Freelance / العمل الحر',
                                                ])
                                                ->required(),
                                            TextInput::make('company')->label('Company Name / اسم الشركة')->required(),
                                            TextInput::make('title')->label('Title / المسمى الوظيفي')->required(),
                                            DatePicker::make('start_date')->label('Start Date / تاريخ البدء')->required()->extraInputAttributes(['type' => 'month']),
                                            Group::make([
                                                Toggle::make('current')->label('Currently Working There / ما زلت أعمل هناك')->reactive(),
                                            ])->extraAttributes(['class' => 'h-full content-center']),
                                            DatePicker::make('end_date')->label('End Date / تاريخ الانتهاء')->extraInputAttributes(['type' => 'month'])
                                                ->hidden(fn(callable $get) => $get('current')),
                                        ])->columns(3)->reorderableWithButtons()->inlineLabel(false)->hiddenLabel()->defaultItems(0)->required(fn(
                                            callable $get
                                        ) => $get('description') !== 'Other')
                                ]),
                            Section::make('Skills')
                                ->schema([
                                    TagsInput::make('soft_skills')->label('Soft Skills / المهارات الشخصية')
                                        ->placeholder('Type and press Enter / اكتب واضغط على Enter')->splitKeys([
                                            'Tab', ','
                                        ]),
                                    TagsInput::make('technical_skills')->label('Technical Skills / المهارات التقنية')
                                        ->placeholder('Type and press Enter / اكتب واضغط على Enter')->splitKeys([
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
                        }),
                    Wizard\Step::make('Idea & Challenges')->icon('heroicon-s-bolt')
                        ->schema([
                            Select::make('has_idea')->label('Do you currently have a business idea or project? / هل لديك فكرة أو مشروع تجاري حاليًا؟')->options([
                                'Yes' => 'Yes / نعم',
                                'No'  => 'No / لا',
                            ])->required()->reactive(),
                            Select::make('idea_stage')->label('In which stage is your idea? / في أي مرحلة حالياً فكرتك؟')->options([
                                'Idea Phase'                   => 'Idea Phase / مرحلة الفكرة',
                                'Proof of Concept'             => 'Proof of Concept / إثبات المفهوم',
                                'Minimum Viable Product (MVP)' => 'Minimum Viable Product (MVP) / الحد الأدنى من المنتج',
                                'Market-ready'                 => 'Market-ready / جاهز للسوق',
                            ])->required()->reactive()->hidden(fn(callable $get) => $get('has_idea') !== 'Yes'),
                            RichEditor::make('idea_description')->label('Please provide a brief description of your idea and what problem it aims to solve. (Please limit your response to 200 words) / إذا كانت الإجابة نعم، يرجى تقديم وصف موجز لفكرتك والمشكلة التي تهدف إلى حلها. (يُرجى تحديد إجابتك ب 200 كلمة)')
                                ->required()->hidden(fn(callable $get) => $get('has_idea') !== 'Yes'),
                            Select::make('uses_ai')->label('Does your business idea or project utilize Artificial Intelligence (AI)? / هل تستخدم فكرتك  أو مشروعك الذكاء الاصطناعي؟')->options([
                                'Yes' => 'Yes / نعم',
                                'No'  => 'No / لا',
                            ])->required()->reactive()->hidden(fn(callable $get) => $get('has_idea') !== 'Yes'),
                            Textarea::make('ai_role')->label('How do you envision Artificial Intelligence playing a role in your solution? / ما هو دور الذكاء الاصطناعي في حلك؟')->required()
                                ->hidden(fn(callable $get) => $get('has_idea') !== 'Yes' || $get('uses_ai') !== 'Yes'),
                            Textarea::make('ai_future_plan')->label('How do you plan to incorporate AI or technological innovation into your project in the future? / إذا لم تكن فكرتك متعلقة بالذكاء الاصطناعي، كيف تخطط لدمج الذكاء الاصطناعي أو الابتكار التكنولوجي في مشروعك في المستقبل؟')
                                ->required()->hidden(fn(callable $get
                                ) => $get('has_idea') !== 'Yes' || $get('uses_ai') !== 'No'),
                            Select::make('has_challenge')->label('Do you have a specific challenge you would solve with Artificial Intelligence (AI)? / هل لديك تحدي محدد تود حله باستخدام الذكاء الاصطناعي؟')->options([
                                'Yes' => 'Yes / نعم',
                                'No'  => 'No / لا',
                            ])->required()->reactive()->hidden(fn(callable $get) => $get('has_idea') !== 'No'),
                            Textarea::make('challenge_description')->label('What specific challenge would you like to solve, and how would you use Artificial Intelligence (AI) to address it / إذا كانت الإجابة نعم، ما هو التحدي المحدد الذي ترغب في حله، وكيف ستستخدم الذكاء الاصطناعي (AI) لمعالجته؟')
                                ->required()->hidden(fn(callable $get
                                ) => $get('has_challenge') !== 'Yes' || $get('has_idea') !== 'No'),
                        ])->afterValidation(function (Get $get) use ($form) {
                            $application = $form->getModelInstance();
                            $application->update(
                                [
                                    'data' => array_merge($application->data, [
                                        'has_idea'              => $get('has_idea'),
                                        'idea_stage'            => $get('idea_stage'),
                                        'idea_description'      => $get('idea_description'),
                                        'uses_ai'               => $get('uses_ai'),
                                        'ai_role'               => $get('ai_role'),
                                        'ai_future_plan'        => $get('ai_future_plan'),
                                        'has_challenge'         => $get('has_challenge'),
                                        'challenge_description' => $get('challenge_description'),
                                    ])
                                ]);
                        }),
                    Wizard\Step::make('Entrepreneurial Skills')->icon('heroicon-s-clipboard-document-list')
                        ->schema([
                            RichEditor::make('creative_solution')
                                ->label('Provide an example of a creative solution you developed to address a challenge. What inspired your approach, and what was the outcome? (Please limit your response to 150-200 words) / قدم مثالًا على حل إبداعي قمت بتطويره لمعالجة تحدي. ما هو مصدر إلهامك وماذا كانت النتيجة؟ الحد الأقصى 150-200 كلمة')
                                ->required(),
                            RichEditor::make('random_objects_usage')
                                ->label('You have a box of random objects (rubber bands, Pencils, Tape, Plastic spoons, Bottle caps). How many different uses can you come up with for these items? (Please limit your response to 150-200 words) / لديك صندوق من الأغراض العشوائية (أشرطة مطاطية، وأقلام رصاص، وأشرطة لاصقة، وملاعق بلاستيكية، وأغطية زجاجات). كم عدد الاستخدامات المختلفة التي يمكنك ابتكارها لهذه العناصر؟ الحد الأقصى 150-200 ')
                                ->required(),
                            RichEditor::make('problem_solving_scenario')
                                ->label('Share a scenario where you faced a significant obstacle while working on a project. How did you identify the problem, and what steps did you take to overcome it? (Please limit your response to 150-200 words) / شاركنا بسيناريو حيث واجهت عقبة كبيرة أثناء العمل على مشروع. كيف حددت المشكلة، وما هي الخطوات التي اتخذتها للتغلب عليها؟ الحد الأقصى 150-200 كلمة')
                                ->required(),
                            RichEditor::make('motivation_participation')
                                ->label('What motivates you to participate in these ideation workshops, and how do you envision applying your passion or interests to generating new ideas or solutions? (Please limit your response to 150-200 words) / ما الذي يحفزك على المشاركة في ورش العمل الخاصة بالتفكير الإبداعي، وكيف تتصور تطبيق شغفك أو اهتماماتك لتوليد أفكار أو حلول جديدة؟ الحد الأقصى 150-200 كلمة')
                                ->required(),
                            RichEditor::make('collaboration_experience')
                                ->label('Can you share your experience with collaborating on creative projects or brainstorming sessions? Describe your role and contributions to the team\'s success. (Please limit your response to 150-200 words) / هل يمكنك مشاركة تجربتك في التعاون على المشاريع الإبداعية أو جلسات التفكير الجماعي؟ صف دورك ومساهماتك في نجاح الفريق. (يرجى تحديد إجابتك بـ 150-200 كلمة)')
                                ->required(),
                            RichEditor::make('participation_goals')
                                ->label('What do you hope to achieve by participating in the ideation workshop? Are there specific skills or insights you\'re looking to gain from the experience? (Please limit your response to 150-200 words) / ما الذي تأمل في تحقيقه من خلال المشاركة في ورشة عمل الأفكار؟ هل هناك مهارات أو رؤى محددة تتطلع إلى اكتسابها من هذه التجربة؟ بحد أقصى 150-200 كلمة')
                                ->required(),
                        ])->afterValidation(function (Get $get) use ($form) {
                            $application = $form->getModelInstance();
                            $application->update(
                                [
                                    'data' => array_merge($application->data, [
                                        'creative_solution'        => $get('creative_solution'),
                                        'random_objects_usage'     => $get('random_objects_usage'),
                                        'problem_solving_scenario' => $get('problem_solving_scenario'),
                                        'motivation_participation' => $get('motivation_participation'),
                                        'collaboration_experience' => $get('collaboration_experience'),
                                        'participation_goals'      => $get('participation_goals'),
                                    ])
                                ]);
                        }),
                    Wizard\Step::make('Generic Questions')->icon('heroicon-s-question-mark-circle')
                        ->schema([
                            RichEditor::make('skills_expertise')
                                ->label('Please tell us about your skills and areas of expertise. This could include technical skills such as programming languages, or data analysis techniques, as well as non-technical skills such as communication, problem-solving, project management, or leadership abilities. Feel free to highlight any relevant experiences or accomplishments. (Please limit your response to 150-200 words) / يرجى إخبارنا عن مهاراتك ومجالات خبرتك. يمكن أن يشمل ذلك المهارات التقنية مثل لغات البرمجة أو تقنيات تحليل البيانات، بالإضافة إلى المهارات غير التقنية مثل التواصل، أو حل المشكلات أو إدارة المشاريع أو القدرات القيادية. لا تتردد في إبراز أي خبرات أو إنجازات ذات صلة. 150-200 كلمة كحد أقصى.')
                                ->required(),
                            Select::make('application_type')->label('Are you applying as an individual or as part of a team? / هل تتقدم بطلبك كفرد أو كجزء من فريق؟')->options([
                                'Individual' => 'Individual / فردي',
                                'Team'       => 'Team / فريق',
                                'Other'      => 'Other / أخرى',
                            ])->required()->reactive(),
                            TextInput::make('application_type_other')->label('Please Specify / يرجى التحديد')
                                ->hidden(fn(callable $get) => $get('application_type') !== 'Other'),
                            Repeater::make('team_members')->label('Team Members / أعضاء الفريق')->addActionLabel('Add Team Member / أضف عضو فريق')
                                ->schema([
                                    TextInput::make('name')->label('Name / الاسم')->required(),
                                    TextInput::make('role')->label('Role / الدور')->required(),
                                    PhoneInput::make('phone')->label('Phone / رقم الهاتف')->required()->default(auth()->user()->phone)
                                        ->defaultCountry('PS')
                                        ->preferredCountries(['ps', 'il'])
                                        ->showSelectedDialCode()
                                        ->validateFor()
                                        ->i18n([
                                            'il' => 'Palestine'
                                        ]),
                                    TextInput::make('email')->label('Email / البريد الإلكتروني')->required()->email(),
                                ])->columns(4)->reorderableWithButtons()->inlineLabel(false)
                                ->hidden(fn(callable $get) => $get('application_type') !== 'Team'),
                            Select::make('startup_experience')->label('Do you have any knowledge or experience in entrepreneurship/startups? / هل لديك أي معرفة أو خبرة في ريادة الأعمال / الشركات الناشئة؟')->options([
                                'Yes' => 'Yes / نعم',
                                'No'  => 'No / لا',
                            ])->required()->reactive(),
                            Textarea::make('experience_specification')->label('Please specify your experience: / يرجى تحديد خبرتك:')
                                ->hidden(fn(callable $get) => $get('startup_experience') !== 'Yes'),
                            TextInput::make('new_skill')->label('If you are looking to acquire one new skill, what would it be? / إذا كنت تتطلع إلى اكتساب مهارة جديدة واحدة، فما هي؟')->required(),
                            Select::make('program_discovery')->label('How did you hear about the PIEC Programme? / كيف سمعت البرنامج؟')->options([
                                'Facebook'           => 'Facebook / صفحة مسرعة الأعمال فلو على فيسبوك',
                                'Instagram'          => 'Instagram / صفحة مسرعة الأعمال فلو على إنستغرام',
                                'LinkedIn'           => 'LinkedIn / صفحة مسرعة الأعمال فلو على لينكد إن',
                                'Other Social Media' => 'Other Social Media Channels / قنوات التواصل الاجتماعي الأخرى',
                                'Friend'             => 'Friend/Colleague / صديق/زميل',
                                'Other'              => 'Other / أخرى',
                            ])->required()->reactive(),
                            TextInput::make('program_discovery_other')->label('Please Specify / يرجى التحديد')
                                ->hidden(fn(callable $get) => $get('program_discovery') !== 'Other'),
                            Select::make('commitment')->label('Are you able to commit to attending all scheduled related workshops and sessions throughout the innovation challenge over two days? / هل أنت قادر على الالتزام بحضور جميع ورش العمل والجلسات ذات الصلة خلال تحدي الابتكار على مدار يومين؟')->options([
                                'Yes'   => 'Yes / نعم',
                                'No'    => 'No / لا',
                                'Other' => 'Other / أخرى',
                            ])->required()->reactive(),
                            TextInput::make('commitment_other')->label('Please Specify / يرجى التحديد')
                                ->hidden(fn(callable $get) => $get('commitment') !== 'Other'),
                            Select::make('continuation_plan')->label('Do you plan to continue working on the idea you develop, by participating in incubation and acceleration programs after the innovation challenge concludes? / هل تخطط لمتابعة العمل على الفكرة التي تطورها، من خلال المشاركة في برامج الحاضنة والتسريع بعد انتهاء تحدي الابتكار؟')->options([
                                'Yes' => 'Yes / نعم',
                                'No'  => 'No / لا',
                            ])->required(),
                            RichEditor::make('additional_info')->label('Anything you’d like to share with us? Please share links to any online portfolios, websites, or repositories showcasing your creative work. Briefly describe your role and contributions to each project. / هل هناك أي شيء تود مشاركته معنا؟ يرجى مشاركة الروابط إلى أي محافظ على الإنترنت، أو مواقع إلكترونية، أو مستودعات تعرض أعمالك الإبداعية. اصف بإيجاز دورك ومساهماتك في كل مشروع.'),
                        ])->afterValidation(function (Get $get) use ($form) {
                            $application = $form->getModelInstance();
                            $application->update(
                                [
                                    'data' => array_merge($application->data, [
                                        'skills_expertise'         => $get('skills_expertise'),
                                        'application_type'         => $get('application_type'),
                                        'application_type_other'   => $get('application_type_other'),
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
                        }),
                    Wizard\Step::make('Review')->icon('heroicon-s-check-circle')
                        ->schema([
                            Placeholder::make('review_section')->hiddenLabel()->content(
                                new HtmlString('<div style="font-size: 24px;text-align: center">Please review your application before submitting / يرجى مراجعة طلبك قبل التقديم</div>')
                            ),
                            Section::make('Personal Information')
                                ->schema([
                                    Placeholder::make('review_first_name')->label('First Name / الاسم الأول')
                                        ->content(fn(Application $record): string => $record->data['first_name'] ?? ''),
                                    Placeholder::make('review_last_name')->label('Last Name / الاسم الأخير')
                                        ->content(fn(Application $record): string => $record->data['last_name'] ?? ''),
                                    Placeholder::make('review_email')->label('Email / البريد الإلكتروني')
                                        ->content(fn(Application $record): string => $record->data['email'] ?? ''),
                                    Placeholder::make('review_dob')->label('Date of Birth / تاريخ الميلاد')
                                        ->content(fn(Application $record): string => $record->data['dob'] ?? ''),
                                    Placeholder::make('review_phone')->label('Phone / رقم الهاتف')
                                        ->content(fn(Application $record): string => $record->data['phone'] ?? ''),
                                    Placeholder::make('review_whatsapp')->label('Whatsapp / واتساب')
                                        ->content(fn(Application $record): string => $record->data['whatsapp'] ?? ''),
                                    Placeholder::make('review_gender')->label('Gender / الجنس')
                                        ->content(fn(Application $record): string => $record->data['gender'] ?? ''),
                                    Placeholder::make('review_residence')->label('Governorate of Residence / المحافظة')
                                        ->content(fn(Application $record): string => $record->data['residence'] ?? ''),
                                    Placeholder::make('review_residence_other')->label('Other Governorate / محافظة أخرى')
                                        ->content(fn(Application $record
                                        ): string => $record->data['residence_other'] ?? ''),
                                    Placeholder::make('review_description')->label('Describe Yourself / صِف نفسك')
                                        ->content(fn(Application $record
                                        ): string => $record->data['description'] ?? ''),
                                    Placeholder::make('review_description_other')->label('Describe Yourself (Other) / صِف نفسك')
                                        ->content(fn(Application $record
                                        ): string => $record->data['description_other'] ?? ''),
                                    Placeholder::make('review_occupation')->label('Occupation / المهنة')
                                        ->content(fn(Application $record): string => $record->data['occupation'] ?? ''),
                                ])->columns(3),
                            Section::make('Idea & Challenges')
                                ->schema([
                                    Placeholder::make('review_has_idea')->label('Do you currently have a business idea or project? / هل لديك فكرة أو مشروع تجاري حاليًا؟')
                                        ->content(fn(Application $record): string => $record->data['has_idea'] ?? ''),
                                    Placeholder::make('review_idea_stage')->label('In which stage is your idea? / في أي مرحلة حالياً فكرتك؟')
                                        ->content(fn(Application $record): string => $record->data['idea_stage'] ?? ''),
                                    Placeholder::make('review_idea_description')->label('Please provide a brief description of your idea and what problem it aims to solve./ إذا كانت الإجابة نعم، يرجى تقديم وصف موجز لفكرتك والمشكلة التي تهدف إلى حلها. ')
                                        ->content(fn(Application $record
                                        ): string => $record->data['idea_description'] ?? ''),
                                    Placeholder::make('review_uses_ai')->label('Does your business idea or project utilize Artificial Intelligence (AI)? / هل تستخدم فكرتك  أو مشروعك الذكاء الاصطناعي؟')
                                        ->content(fn(Application $record): string => $record->data['uses_ai'] ?? ''),
                                    Placeholder::make('review_ai_role')->label('How do you envision Artificial Intelligence playing a role in your solution? / ما هو دور الذكاء الاصطناعي في حلك؟')
                                        ->content(fn(Application $record): string => $record->data['ai_role'] ?? ''),
                                    Placeholder::make('review_ai_future_plan')->label('How do you plan to incorporate AI or technological innovation into your project in the future? / إذا لم تكن فكرتك متعلقة بالذكاء الاصطناعي، كيف تخطط لدمج الذكاء الاصطناعي أو الابتكار التكنولوجي في مشروعك في المستقبل؟')
                                        ->content(fn(Application $record
                                        ): string => $record->data['ai_future_plan'] ?? ''),
                                    Placeholder::make('review_has_challenge')->label('Do you have a specific challenge you would solve with Artificial Intelligence (AI)? / هل لديك تحدي محدد تود حله باستخدام الذكاء الاصطناعي؟')
                                        ->content(fn(Application $record
                                        ): string => $record->data['has_challenge'] ?? ''),
                                    Placeholder::make('review_challenge_description')->label('What specific challenge would you like to solve, and how would you use Artificial Intelligence (AI) to address it / إذا كانت الإجابة نعم، ما هو التحدي المحدد الذي ترغب في حله، وكيف ستستخدم الذكاء الاصطناعي (AI) لمعالجته؟')
                                        ->content(fn(Application $record
                                        ): string => $record->data['challenge_description'] ?? ''),
                                ]),
                            Section::make('Entrepreneurial Skills')
                                ->schema([
                                    Placeholder::make('review_creative_solution')->label('Provide an example of a creative solution you developed to address a challenge. What inspired your approach, and what was the outcome? / قدم مثالًا على حل إبداعي قمت بتطويره لمعالجة تحدي. ما هو مصدر إلهامك وماذا كانت النتيجة؟')
                                        ->content(fn(Application $record
                                        ): HtmlString => new HtmlString($record->data['creative_solution'] ?? '')),
                                    Placeholder::make('review_random_objects_usage')->label('You have a box of random objects (rubber bands, Pencils, Tape, Plastic spoons, Bottle caps). How many different uses can you come up with for these items? / لديك صندوق من الأغراض العشوائية (أشرطة مطاطية، وأقلام رصاص، وأشرطة لاصقة، وملاعق بلاستيكية، وأغطية زجاجات). كم عدد الاستخدامات المختلفة التي يمكنك ابتكارها لهذه العناصر؟')
                                        ->content(fn(Application $record
                                        ): HtmlString => new HtmlString($record->data['random_objects_usage'] ?? '')),
                                    Placeholder::make('review_problem_solving_scenario')->label('Share a scenario where you faced a significant obstacle while working on a project. How did you identify the problem, and what steps did you take to overcome it? / شاركنا بسيناريو حيث واجهت عقبة كبيرة أثناء العمل على مشروع. كيف حددت المشكلة، وما هي الخطوات التي اتخذتها للتغلب عليها؟')
                                        ->content(fn(Application $record
                                        ): HtmlString => new HtmlString($record->data['problem_solving_scenario'] ?? '')),
                                    Placeholder::make('review_motivation_participation')->label('What motivates you to participate in these ideation workshops, and how do you envision applying your passion or interests to generating new ideas or solutions? / ما الذي يحفزك على المشاركة في ورش العمل الخاصة بالتفكير الإبداعي، وكيف تتصور تطبيق شغفك أو اهتماماتك لتوليد أفكار أو حلول جديدة؟')
                                        ->content(fn(Application $record
                                        ): HtmlString => new HtmlString($record->data['motivation_participation'] ?? '')),
                                    Placeholder::make('review_collaboration_experience')->label('Can you share your experience with collaborating on creative projects or brainstorming sessions? Describe your role and contributions to the team\'s success. / هل يمكنك مشاركة تجربتك في التعاون على المشاريع الإبداعية أو جلسات التفكير الجماعي؟ صف دورك ومساهماتك في نجاح الفريق.')
                                        ->content(fn(Application $record
                                        ): HtmlString => new HtmlString($record->data['collaboration_experience'] ?? '')),
                                    Placeholder::make('review_participation_goals')->label('What do you hope to achieve by participating in the ideation workshop? Are there specific skills or insights you\'re looking to gain from the experience? / ما الذي تأمل في تحقيقه من خلال المشاركة في ورشة عمل الأفكار؟ هل هناك مهارات أو رؤى محددة تتطلع إلى اكتسابها من هذه التجربة؟')
                                        ->content(fn(Application $record
                                        ): HtmlString => new HtmlString($record->data['participation_goals'] ?? '')),
                                ]),
                            Section::make('Generic Questions')
                                ->schema([
                                    Placeholder::make('review_skills_expertise')->label('Please tell us about your skills and areas of expertise. This could include technical skills such as programming languages, or data analysis techniques, as well as non-technical skills such as communication, problem-solving, project management, or leadership abilities. Feel free to highlight any relevant experiences or accomplishments. / يرجى إخبارنا عن مهاراتك ومجالات خبرتك. يمكن أن يشمل ذلك المهارات التقنية مثل لغات البرمجة أو تقنيات تحليل البيانات، بالإضافة إلى المهارات غير التقنية مثل التواصل، أو حل المشكلات أو إدارة المشاريع أو القدرات القيادية. لا تتردد في إبراز أي خبرات أو إنجازات ذات صلة.')
                                        ->content(fn(Application $record
                                        ): HtmlString => new HtmlString($record->data['skills_expertise'] ?? '')),
                                    Placeholder::make('review_application_type')->label('Are you applying as an individual or as part of a team? / هل تتقدم بطلبك كفرد أو كجزء من فريق؟')
                                        ->content(fn(Application $record
                                        ): string => $record->data['application_type'] ?? ''),
                                    Placeholder::make('review_application_type_other')->label('Please Specify / يرجى التحديد')
                                        ->content(fn(Application $record
                                        ): string => $record->data['application_type_other'] ?? ''),
                                    Placeholder::make('review_startup_experience')->label('Do you have any knowledge or experience in entrepreneurship/startups? / هل لديك أي معرفة أو خبرة في ريادة الأعمال / الشركات الناشئة؟')
                                        ->content(fn(Application $record
                                        ): string => $record->data['startup_experience'] ?? ''),
                                    Placeholder::make('review_experience_specification')->label('Please specify your experience: / يرجى تحديد خبرتك:')
                                        ->content(fn(Application $record
                                        ): string => $record->data['experience_specification'] ?? ''),
                                    Placeholder::make('review_new_skill')->label('If you are looking to acquire one new skill, what would it be? / إذا كنت تتطلع إلى اكتساب مهارة جديدة واحدة، فما هي؟')
                                        ->content(fn(Application $record): string => $record->data['new_skill'] ?? ''),
                                    Placeholder::make('review_program_discovery')->label('How did you hear about the PIEC Programme? / كيف سمعت البرنامج؟')
                                        ->content(fn(Application $record
                                        ): string => $record->data['program_discovery'] ?? ''),
                                    Placeholder::make('review_program_discovery_other')->label('Please Specify / يرجى التحديد')
                                        ->content(fn(Application $record
                                        ): string => $record->data['program_discovery_other'] ?? ''),
                                    Placeholder::make('review_commitment')->label('Are you able to commit to attending all scheduled related workshops and sessions throughout the innovation challenge over two days? / هل أنت قادر على الالتزام بحضور جميع ورش العمل والجلسات ذات الصلة خلال تحدي الابتكار على مدار يومين؟')
                                        ->content(fn(Application $record): string => $record->data['commitment'] ?? ''),
                                    Placeholder::make('review_commitment_other')->label('Please Specify / يرجى التحديد')
                                        ->content(fn(Application $record
                                        ): string => $record->data['commitment_other'] ?? ''),
                                    Placeholder::make('review_continuation_plan')->label('Do you plan to continue working on the idea you develop, by participating in incubation and acceleration programs after the innovation challenge concludes? / هل تخطط لمتابعة العمل على الفكرة التي تطورها، من خلال المشاركة في برامج الحاضنة والتسريع بعد انتهاء تحدي الابتكار؟')
                                        ->content(fn(Application $record
                                        ): string => $record->data['continuation_plan'] ?? ''),
                                    Placeholder::make('review_additional_info')->label('Anything you’d like to share with us? Please share links to any online portfolios, websites, or repositories showcasing your creative work. Briefly describe your role and contributions to each project. / هل هناك أي شيء تود مشاركته معنا؟ يرجى مشاركة الروابط إلى أي محافظ على الإنترنت، أو مواقع إلكترونية، أو مستودعات تعرض أعمالك الإبداعية. اصف بإيجاز دورك ومساهماتك في كل مشروع.')
                                        ->content(fn(Application $record
                                        ): HtmlString => new HtmlString($record->data['additional_info'] ?? ''))
                                ])
                        ])
                ])->columnSpan(2)->statePath('data'),
            ]);
    }

    public static function table(Table $table): Table
    {

        $columns = [
            TextColumn::make('program.name')->label('Program'),
            TextColumn::make('program.level')->label('Level')
                ->formatStateUsing(fn(string $state): string => ucwords($state, '- '))
        ];

        if (auth()->id() <= 5) {
            $columns = array_merge($columns, [
                TextColumn::make('data.first_name')->label('First Name'),
                TextColumn::make('data.last_name')->label('Last Name'),
                TextColumn::make('data.email')->label('Email'),
            ]);
        }

        $columns = array_merge($columns, [
            TextColumn::make('status')->label('Status'),
            TextColumn::make('created_at')->label('Created at'),
        ]);

        $header_actions = auth()->id() <= 5 ? [
            Tables\Actions\Action::make('Export')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(route('applications.export'))
        ] : [];

        return $table
            ->columns($columns)
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
                \Filament\Infolists\Components\Section::make('Personal Questions')
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
                \Filament\Infolists\Components\Section::make('Educational Background')
                    ->schema([
                        RepeatableEntry::make('data.education / التعليم')->label('Education')
                            ->schema([
                                TextEntry::make('degree / الدرجة')->label('Degree'),
                                TextEntry::make('school')->label('School/University'),
                                TextEntry::make('major')->label('Major/Field of study'),
                                TextEntry::make('start_date')->label('Start Date'),
                                TextEntry::make('current')->label('Currently Studying There'),
                                TextEntry::make('end_date')->label('End Date'),
                            ])->columns(3),
                    ])->columns(1),
                \Filament\Infolists\Components\Section::make('Professional Experience')
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


                        TextEntry::make('data.soft_skills')->label('Soft Skills'),
                        TextEntry::make('data.technical_skills')->label('Technical Skills'),
                    ])->columns(1),

                \Filament\Infolists\Components\Section::make('Idea & Challenges')
                    ->schema([
                        TextEntry::make('data.has_idea')->label('Do you currently have a business idea or project?')->html(),
                        TextEntry::make('data.idea_stage')->label('In which stage is your idea?')->html(),
                        TextEntry::make('data.idea_description')->label('Please provide a brief description of your idea and what problem it aims to solve.')->html(),
                        TextEntry::make('data.uses_ai')->label('Does your business idea or project utilize Artificial Intelligence (AI)?')->html(),
                        TextEntry::make('data.ai_role')->label('How do you envision Artificial Intelligence playing a role in your solution?')->html(),
                        TextEntry::make('data.ai_future_plan')->label('How do you plan to incorporate AI or technological innovation into your project in the future?')->html(),
                        TextEntry::make('data.has_challenge')->label('Do you have a specific challenge you would solve with Artificial Intelligence (AI)?')->html(),
                        TextEntry::make('data.challenge_description')->label('What specific challenge would you like to solve, and how would you use Artificial Intelligence (AI) to address it')->html(),
                    ])->columns(1),
                \Filament\Infolists\Components\Section::make('Entrepreneurial Skills')
                    ->schema([
                        TextEntry::make('data.creative_solution')->label('Provide an example of a creative solution you developed to address a challenge. What inspired your approach, and what was the outcome?')->html(),
                        TextEntry::make('data.random_objects_usage')->label('You have a box of random objects (rubber bands, Pencils, Tape, Plastic spoons, Bottle caps). How many different uses can you come up with for these items?')->html(),
                        TextEntry::make('data.problem_solving_scenario')->label('Share a scenario where you faced a significant obstacle while working on a project. How did you identify the problem, and what steps did you take to overcome it?')->html(),
                        TextEntry::make('data.motivation_participation')->label('What motivates you to participate in these ideation workshops, and how do you envision applying your passion or interests to generating new ideas or solutions?')->html(),
                        TextEntry::make('data.collaboration_experience')->label('Can you share your experience with collaborating on creative projects or brainstorming sessions? Describe your role and contributions to the team\'s success.')->html(),
                        TextEntry::make('data.participation_goals')->label('What do you hope to achieve by participating in the ideation workshop? Are there specific skills or insights you\'re looking to gain from the experience?')->html(),
                    ])->columns(1),
                \Filament\Infolists\Components\Section::make('Generic Questions')
                    ->schema([
                        TextEntry::make('data.skills_expertise')->label('Please tell us about your skills and areas of expertise. This could include technical skills such as programming languages, or data analysis techniques, as well as non-technical skills such as communication, problem-solving, project management, or leadership abilities. Feel free to highlight any relevant experiences or accomplishments.')->html(),
                        TextEntry::make('data.application_type')->label('Are you applying as an individual or as part of a team?')->html(),
                        TextEntry::make('data.application_type_other')->label('Please Specify')->html(),
                        RepeatableEntry::make('data.team_members')->label('Team Members')
                            ->schema([
                                TextEntry::make('name')->label('Name'),
                                TextEntry::make('role')->label('Role'),
                                TextEntry::make('phone')->label('Phone'),
                                TextEntry::make('email')->label('Email'),
                            ])->columns(4),
                        TextEntry::make('data.startup_experience')->label('Do you have any knowledge or experience in entrepreneurship/startups?')->html(),
                        TextEntry::make('data.experience_specification')->label('Please specify your experience:')->html(),
                        TextEntry::make('data.new_skill')->label('If you are looking to acquire one new skill, what would it be?')->html(),
                        TextEntry::make('data.program_discovery')->label('How did you hear about the PIEC Programme?')->html(),
                        TextEntry::make('data.program_discovery_other')->label('Please Specify')->html(),
                        TextEntry::make('data.commitment')->label('Are you able to commit to attending all scheduled related workshops and sessions throughout the innovation challenge over two days?')->html(),
                        TextEntry::make('data.commitment_other')->label('Please Specify')->html(),
                        TextEntry::make('data.continuation_plan')->label('Do you plan to continue working on the idea you develop, by participating in incubation and acceleration programs after the innovation challenge concludes?')->html(),
                        TextEntry::make('data.additional_info')->label('Anything you’d like to share with us? Please share links to any online portfolios, websites, or repositories showcasing your creative work. Briefly describe your role and contributions to each project.')->html(),
                    ])->columns(1),

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
        return $record->status === 'Draft' && ($record->user_id === auth()->id() || auth()->id() <= 5);
    }

    public static function canView(Model $record): bool
    {
        return $record->user_id === auth()->id() || auth()->id() <= 5;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return auth()->id() <= 5
            ? parent::getEloquentQuery()
            : parent::getEloquentQuery()->where('user_id', auth()->id());
    }

}
