<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationResource\Pages;
use App\Filament\Resources\ApplicationResource\RelationManagers;
use App\Models\Application;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
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
                            TextInput::make('first_name')->label('First Name / الاسم الأول')->required()->reactive()->default(auth()->user()->first_name)
                                ->afterStateUpdated(fn(Set $set, ?string $state) => $set('first_name',
                                    ucwords($state))),
                            TextInput::make('last_name')->label('Last Name / اسم العائلة')->required()->reactive()->default(auth()->user()->last_name)
                                ->afterStateUpdated(fn(Set $set, ?string $state) => $set('last_name',
                                    ucwords($state))),
                            TextInput::make('email')->email()->label('Email / البريد الإلكتروني')->required()->default(auth()->user()->email),
                            DatePicker::make('dob')->label('Date of Birth / تاريخ الميلاد')->native(false)->required()->default(auth()->user()->dob),
                            TextInput::make('phone')->label('Phone / رقم الهاتف')->required()->default(auth()->user()->phone),
                            TextInput::make('whatsapp')->label('Whatsapp Number / رقم الواتساب')->required()->default(auth()->user()->whatsapp),
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
                            ])->required()->default(auth()->user()->residence),
                            TextInput::make('residence_other')->label('Other Governorate / محافظة أخرى')
                                ->hidden(fn(callable $get
                                ) => $get('residence') !== 'Other')->default(auth()->user()->residence_other),
                            Select::make('educational_level')->label('Educational Level / المستوى التعليمي')->options([
                                'High School'                 => 'High School / المدرسة الثانوية',
                                'Vocational/Technical School' => 'Vocational/Technical School / المدرسة المهنية / التقنية',
                                'Bachelor'                    => 'Bachelor\'s Degree / درجة البكالوريوس',
                                'Master'                      => 'Master\'s Degree / درجة الماجستير',
                                'PhD'                         => 'Doctorate/Ph.D. / دكتوراه / دكتوراه',
                                'Other'                       => 'Other / أخرى (يرجى التحديد)',
                            ])->required()->reactive()->default(auth()->user()->educational_level),
                            TextInput::make('educational_level_other')->label('Other Educational Level / مستوى تعليمي آخر')
                                ->hidden(fn(callable $get
                                ) => $get('educational_level') !== 'Other')->default(auth()->user()->educational_level_other),
                            Select::make('description')->label('Describe Yourself / صِف نفسك')->options([
                                'Student'      => 'Student / طالب',
                                'Professional' => 'Professional / محترف',
                                'Entrepreneur' => 'Entrepreneur / رائد أعمال',
                                'Other'        => 'Other / أخرى',
                            ])->required()->reactive()->default(auth()->user()->description),
                            TextInput::make('description_other')->label('Describe Yourself / صِف نفسك')
                                ->hidden(fn(callable $get
                                ) => $get('description') !== 'Other')->default(auth()->user()->description_other),
                            TextInput::make('occupation')->label('Occupation / المهنة')->required()
                                ->hidden(fn(callable $get) => in_array($get('description'), ['Student', 'Other']))
                                ->default(auth()->user()->occupation),
                        ])->columns(2),
                    Wizard\Step::make('Educational Background')->icon('heroicon-o-academic-cap')
                        ->schema([
                            Section::make('Education / التعليم')
                                ->schema([
                                    Repeater::make('education / التعليم')
                                        ->schema([
                                            Select::make('degree / الدرجة')
                                                ->options([
                                                    'High School'                 => 'High School / الثانوية العامة',
                                                    'Vocational/Technical School' => 'Vocational/Technical School / المدرسة المهنية/التقنية',
                                                    'Bachelor'                    => 'Bachelor\'s Degree / درجة البكالوريوس',
                                                    'Master'                      => 'Master\'s Degree / درجة الماجستير',
                                                    'PhD'                         => 'Doctorate/Ph.D. / الدكتوراه/الفلسفة',
                                                    'Certification'               => 'Certification / الشهادة',
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
                                        ])->columns(3)->reorderableWithButtons()->inlineLabel(false)->hiddenLabel()->defaultItems(0)
                                ])
                        ]),
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
                                            TextInput::make('title')->label('Title / العنوان')->required(),
                                            DatePicker::make('start_date')->label('Start Date / تاريخ البدء')->required()->extraInputAttributes(['type' => 'month']),
                                            Group::make([
                                                Toggle::make('current')->label('Currently Working There / ما زلت أعمل هناك')->reactive(),
                                            ])->extraAttributes(['class' => 'h-full content-center']),
                                            DatePicker::make('end_date')->label('End Date / تاريخ الانتهاء')->extraInputAttributes(['type' => 'month'])
                                                ->hidden(fn(callable $get) => $get('current')),
                                        ])->columns(3)->reorderableWithButtons()->inlineLabel(false)->hiddenLabel()->defaultItems(0)
                                ]),
                            Section::make('Skills')
                                ->schema([
                                    TagsInput::make('soft_skills')->label('Soft Skills / المهارات الشخصية')
                                        ->placeholder('Type and press Enter / اكتب واضغط على Enter')->splitKeys(['Tab', ',']),
                                    TagsInput::make('technical_skills')->label('Technical Skills / المهارات التقنية')
                                        ->placeholder('Type and press Enter / اكتب واضغط على Enter')->splitKeys(['Tab', ','])
                                ])
                        ]),
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
                        ]),
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
                        ]),
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
                                    TextInput::make('phone')->label('Phone / الهاتف')->required(),
                                    TextInput::make('email')->label('Email / البريد الإلكتروني')->required(),
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
        return $record->user_id === auth()->id();
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
