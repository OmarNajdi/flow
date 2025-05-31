<?php

namespace App\Exports;

use App\Models\Application;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ApplicationsExport implements FromCollection, WithMapping, WithHeadings, WithColumnFormatting, WithStyles
{
    public function __construct(private readonly int $id, private readonly string $level)
    {
    }


    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Application::where('program_id', $this->id)->get();
    }

    public function map($application): array
    {
        $team_members = array_values($application->data['team_members'] ?? []);

        $ideation = [
            $application->data['has_idea'] ?? '',
            $application->data['circular_economy'] ?? '',
            $application->data['idea_stage'] ?? '',
            $application->data['idea_sector'] ?? '',
            strip_tags($application->data['idea_description'] ?? ''),
            $application->data['has_challenge'] ?? '',
            $application->data['challenge_description'] ?? '',
            strip_tags($application->data['creative_solution'] ?? ''),
            strip_tags($application->data['problem_solving_scenario'] ?? ''),
            strip_tags($application->data['participation_goals'] ?? ''),
            strip_tags($application->data['skills_expertise'] ?? ''),
            $application->data['individual_or_team'] ?? '',
            $application->data['team_count'] ?? '',
            $team_members[0]['name'] ?? '',
            $team_members[0]['role'] ?? '',
            $team_members[0]['phone'] ?? '',
            $team_members[0]['email'] ?? '',
            $application->data['startup_experience'] ?? '',
            $application->data['experience_specification'] ?? '',
            $application->data['new_skill'] ?? '',
            $application->data['program_discovery'] ?? '',
            $application->data['program_discovery_other'] ?? '',
            $application->data['commitment'] ?? '',
            $application->data['commitment_other'] ?? '',
            $application->data['continuation_plan'] ?? '',
            strip_tags($application->data['additional_info'] ?? ''),
        ];

        $pre_incubation = [
            strip_tags($application->data['problem'] ?? ''),
            strip_tags($application->data['target'] ?? ''),
            strip_tags($application->data['identify'] ?? ''),
            strip_tags($application->data['solution'] ?? ''),
            strip_tags($application->data['unique'] ?? ''),
            strip_tags($application->data['alternatives'] ?? ''),
            $application->data['sector'] ?? '',
            $application->data['sector_other'] ?? '',
            $application->data['stage'] ?? '',
            $application->data['have_prototype'] ?? '',
            strip_tags($application->data['prototype_details'] ?? ''),
            $application->data['duration'] ?? '',
            $application->data['customers'] ?? '',
            $application->data['customers_count'] ?? '',
            $application->data['individual_or_team'] ?? '',
            $team_members[0]['name'] ?? '',
            $team_members[0]['role'] ?? '',
            $team_members[0]['phone'] ?? '',
            $team_members[0]['email'] ?? '',
            strip_tags($application->data['milestones'] ?? ''),
            strip_tags($application->data['resources'] ?? ''),
            $application->data['why'] ?? '',
            $application->data['achieve'] ?? '',
            $application->data['program_discovery'] ?? '',
            $application->data['program_discovery_other'] ?? '',
        ];

        $incubation = [
            $application->data['idea'] ?? '',
            $application->data['problem'] ?? '',
            $application->data['fit'] ?? '',
            $application->data['solution'] ?? '',
            $application->data['sector'] ?? '',
            $application->data['sector_other'] ?? '',
            $application->data['stage'] ?? '',
            $application->data['features'] ?? '',
            $application->data['milestones'] ?? '',
            $application->data['target'] ?? '',
            $application->data['how_target'] ?? '',
            $application->data['advantage'] ?? '',
            $application->data['team_members'][0]['name'] ?? '',
            $application->data['team_members'][0]['role'] ?? '',
            $application->data['team_members'][0]['phone'] ?? '',
            $application->data['team_members'][0]['email'] ?? '',
            $application->data['vision'] ?? '',
            $application->data['achieve'] ?? '',
            $application->data['support'] ?? '',
            $application->data['other'] ?? '',
            $application->data['committed'] ?? '',
            $application->data['issues'] ?? '',
        ];

        $pre_acceleration = [
            $application->data['sector'] ?? '',
            $application->data['sector_other'] ?? '',
            $application->data['stage'] ?? '',
            $application->data['solution_name'] ?? '',
            strip_tags($application->data['solution'] ?? ''),
            $application->data['solution_type'] ?? '',
            $application->data['solution_type_other'] ?? '',
            strip_tags($application->data['problem'] ?? ''),
            strip_tags($application->data['target'] ?? ''),
            strip_tags($application->data['value_proposition'] ?? ''),
            strip_tags($application->data['competitive_advantage'] ?? ''),
            $application->data['impact'] ?? '',
            $application->data['impact_other'] ?? '',
            $application->data['validation_duration'] ?? '',
            strip_tags($application->data['validation_process'] ?? ''),
            $application->data['startup_registered'] ?? '',
            $application->data['solution_launch'] ?? '',
            $application->data['go_to_market_strategy'] ?? '',
            $application->data['go_to_market_strategy_other'] ?? '',
            $application->data['business_model'] ?? '',
            $application->data['business_model_other'] ?? '',
            $application->data['revenue_model'] ?? '',
            $application->data['revenue_model_other'] ?? '',
            strip_tags($application->data['competitors'] ?? ''),
            $application->data['funding'] ?? '',
            $application->data['funding_other'] ?? '',
            $application->data['challenges'] ?? '',
            $application->data['challenges_other'] ?? '',
            strip_tags($application->data['traction'] ?? ''),
            strip_tags($application->data['market_validation'] ?? ''),
            $application->data['generating_revenue'] ?? '',
            strip_tags($application->data['needs'] ?? ''),
            $application->data['sdg'] ?? '',
            $application->data['team_members'][0]['name'] ?? '',
            $application->data['team_members'][0]['role'] ?? '',
            $application->data['team_members'][0]['phone'] ?? '',
            $application->data['team_members'][0]['email'] ?? '',
            $application->data['strategy'] ?? '',
            $application->data['business_skills'] ?? '',
            $application->data['startup_experience'] ?? '',
            $application->data['experience_specification'] ?? '',
            $application->data['new_skill'] ?? '',
            $application->data['program_discovery'] ?? '',
            $application->data['participation'] ?? '',
            $application->data['prototype_link'] ?? '',
        ];

        $acceleration = [
            strip_tags($application->data['value_proposition'] ?? ''),
            strip_tags($application->data['problem'] ?? ''),
            strip_tags($application->data['solution'] ?? ''),
            $application->data['sector'] ?? '',
            $application->data['sector_other'] ?? '',
            $application->data['stage'] ?? '',
            $application->data['product_launched'] ?? '',
            strip_tags($application->data['launch_details'] ?? ''),
            strip_tags($application->data['current_traction'] ?? ''),
            $application->data['funding_status'] ?? '',
            $application->data['funding_status_other'] ?? '',
            strip_tags($application->data['achieved_milestones'] ?? ''),
            strip_tags($application->data['target_customers'] ?? ''),
            strip_tags($application->data['market_size'] ?? ''),
            strip_tags($application->data['market_identification'] ?? ''),
            strip_tags($application->data['competitive_advantage'] ?? ''),
            $application->data['revenue_model'] ?? '',
            $application->data['revenue_model_other'] ?? '',
            strip_tags($application->data['go_to_market_strategy'] ?? ''),
            strip_tags($application->data['customer_acquisition'] ?? ''),
            $application->data['team_count'] ?? '',
            $application->data['team_members'][0]['name'] ?? '',
            $application->data['team_members'][0]['role'] ?? '',
            $application->data['team_members'][0]['phone'] ?? '',
            $application->data['team_members'][0]['email'] ?? '',
            $application->data['full_time_team_members'] ?? '',
            $application->data['full_time_team_members_other'] ?? '',
            strip_tags($application->data['investment_to_date'] ?? ''),
            $application->data['funding_raised'] ?? '',
            strip_tags($application->data['additional_funding'] ?? ''),
            $application->data['registered_legal_entity'] ?? '',
            strip_tags($application->data['registration_details'] ?? ''),
            strip_tags($application->data['legal_issues'] ?? ''),
            $application->data['in_process_registration'] ?? '',
            strip_tags($application->data['registration_timeline'] ?? ''),
            strip_tags($application->data['current_registration_step'] ?? ''),
            strip_tags($application->data['registration_steps_challenges'] ?? ''),
            strip_tags($application->data['vision'] ?? ''),
            strip_tags($application->data['aimed_milestones'] ?? ''),
            strip_tags($application->data['support_from_accelerator'] ?? ''),
            $application->data['participated_in_programs'] ?? '',
            strip_tags($application->data['program_details'] ?? ''),
        ];


        $formation = [
            // Startup Overview
            $application->data['startup_name'] ?? '',
            $application->data['solution_stage'] ?? '',
            $application->data['problem'] ?? '',
            $application->data['target_segment'] ?? '',
            $application->data['problem_identification'] ?? '',
            $application->data['sector'] ?? '',
            $application->data['sector_other'] ?? '',

            // Solution & Uniqueness
            $application->data['solution_description'] ?? '',
            $application->data['solution_uniqueness'] ?? '',
            $application->data['solution_better_alternatives'] ?? '',
            $application->data['problem_solution_validation'] ?? '',
            $application->data['validation_details'] ?? '',
            $application->data['validation_next_steps'] ?? '',

            // Market Analysis
            $application->data['target_market_identified'] ?? '',
            $application->data['target_market_how'] ?? '',
            $application->data['target_market_opportunity'] ?? '',
            $application->data['target_market_research'] ?? '',
            $application->data['target_market_challenges'] ?? '',
            $application->data['competitor_analysis'] ?? '',
            $application->data['competitor_details'] ?? '',
            $application->data['competitive_advantage'] ?? '',

            // MVP
            $application->data['has_mvp'] ?? '',
            $application->data['mvp_roadmap'] ?? '',
            $application->data['mvp_features'] ?? '',
            $application->data['mvp_support'] ?? '',
            $application->data['flow_assistance'] ?? '',

            // Commitment & Expectations
            $application->data['program_reason'] ?? '',
            $application->data['program_goals'] ?? '',
            $application->data['commitment'] ?? '',
            $application->data['legal_issues'] ?? '',
            $application->data['legal_issues_details'] ?? '',

            // Progress & Team Info
            $application->data['idea_duration'] ?? '',
            $application->data['individual_or_team'] ?? '',

            $team_members[0]['name'] ?? '',
            $team_members[0]['role'] ?? '',
            $team_members[0]['phone'] ?? '',
            $team_members[0]['email'] ?? '',

            $application->data['program_discovery'] ?? '',
            $application->data['program_discovery_other'] ?? '',
            $application->data['additional_notes'] ?? '',
        ];

        $program = match ($this->level) {
            'ideation and innovation' => $ideation,
            'pre-incubation' => $pre_incubation,
            'incubation' => $incubation,
            'pre-acceleration' => $pre_acceleration,
            'acceleration' => $acceleration,
            'formation' => $formation,
            default => $ideation
        };

        return [
            $application->created_at ?? '',
            $application->status ?? '',
            $application->data['first_name'] ?? '',
            $application->data['last_name'] ?? '',
            $application->data['email'] ?? '',
            $application->data['dob'] ?? '',
            $application->data['phone'] ?? '',
            $application->data['whatsapp'] ?? '',
            $application->data['gender'] ?? '',
            $application->data['residence'] ?? '',
            $application->data['residence_other'] ?? '',
            $application->data['description'] ?? '',
            $application->data['description_other'] ?? '',
            $application->data['occupation'] ?? '',
            $application->data['education'][0]['degree'] ?? '',
            $application->data['education'][0]['school'] ?? '',
            $application->data['education'][0]['major'] ?? '',
            $application->data['education'][0]['start_date'] ?? '',
            $application->data['education'][0]['current'] ?? '',
            $application->data['education'][0]['end_date'] ?? '',
            $application->data['experience'][0]['type'] ?? '',
            $application->data['experience'][0]['company'] ?? '',
            $application->data['experience'][0]['title'] ?? '',
            $application->data['experience'][0]['start_date'] ?? '',
            $application->data['experience'][0]['current'] ?? '',
            $application->data['experience'][0]['end_date'] ?? '',
            implode(', ', $application->data['soft_skills'] ?? []),
            implode(', ', $application->data['technical_skills'] ?? []),
            ...$program
        ];
    }

    public function headings(): array
    {

        $ideation = [
            'Do you currently have a business idea or project?',
            'Is your business idea or project focused on a specific sector?',
            'In which stage is your idea?',
            'Which sector is it, and what specific problem or challenge does your idea aim to address?',
            'Please provide a brief description of your idea and what problem it aims to solve.',
            'Do you have a specific challenge you aim to solve?',
            'Which sector is it, and what specific challenge would you like to solve?',
            'Provide an example of a creative solution you developed to address a challenge. What inspired your approach, and what was the outcome?',
            'Share a scenario where you faced a significant obstacle while working on a project. How did you identify the problem, and what steps did you take to overcome it?',
            'What do you hope to achieve by participating in the ideation workshop? Are there specific skills or insights you\'re looking to gain from the experience?',
            'Please tell us about your skills and areas of expertise. This could include technical skills such as programming languages, or data analysis techniques, as well as non-technical skills such as communication, problem-solving, project management, or leadership abilities. Feel free to highlight any relevant experiences or accomplishments.',
            'Are you applying as an individual or as part of a team?',
            'How many team members will participate in the problem-solving workshop?',
            'Team Member Name',
            'Team Member Role',
            'Team Member Phone',
            'Team Member Email',
            'Do you have any knowledge or experience in entrepreneurship/startups?',
            'Please specify your experience:',
            'If you are looking to acquire one new skill, what would it be?',
            'How did you hear about the PIEC Programme?',
            'How did you hear (Other)',
            'Are you able to commit to attending all scheduled related workshops and sessions?',
            'Commitment (Other)',
            'Do you plan to continue working on the idea you develop, by participating in incubation and acceleration programs after the innovation challenge concludes?',
            'Anything you’d like to share with us? Please share links to any online portfolios, websites, or repositories showcasing your creative work. Briefly describe your role and contributions to each project.'
        ];

        $pre_incubation = [
            'What specific problem or need does your startup address?',
            'Who is affected by this problem? And who’s your target segment?',
            'How did you identify this problem?',
            'Describe your proposed solution to the problem',
            'What makes your solution unique or innovative?',
            'How does your solution address the problem better than existing alternatives?',
            'What industry sector does your product/service target?',
            'What industry (Other)',
            'What stage is your solution currently in?',
            'Have you developed a prototype or proof-of-concept?',
            'Please provide us with details',
            'How long have you been working on this solution?',
            'Do you have any customers or users currently?',
            'How many customers and what is their feedback?',
            'Are you applying as an individual or as part of a team?',
            'Team Member Name',
            'Team Member Role',
            'Team Member Phone',
            'Team Member Email',
            'What are the next key milestones you aim to achieve in the next 3-6 months?',
            'What resources or support do you need to achieve these milestones?',
            'Why do you want to join our pre-incubation program?',
            'What do you hope to achieve by the end of the program?',
            'How did you hear about the PIEC Programme?',
            'Please Specify',
        ];

        $incubation = [
            'Please provide a brief description of your business or idea.',
            'What problem are you solving?',
            'How did you validate the problem-solution fit?',
            'Describe your solution and how it addresses the problem. Please make sure to clarify your value proposition',
            'What industry sector does your product/service target?',
            'Please Specify',
            'What stage is your solution currently in?',
            'Have you identified the must-have features that your MVP should include?',
            'What key milestones have you achieved so far?',
            'Who are your target customers?',
            'How did you identify your target market? Why do you believe this target market presents a good opportunity to start your business? Describe any research methods used (surveys, interviews, focus groups, etc.)',
            'What is your competitive advantage? How do you plan to differentiate your startup in the market?',
            'Name',
            'Role',
            'Phone',
            'Email',
            'What is your vision for the startup in the next 1-3 years?',
            'What are the key milestones you aim to achieve during the incubation program?',
            'How can Flow Accelerator support you in developing your MVP?',
            'Have you participated in any other incubation or acceleration programs? If yes, provide details.',
            'Are you committed to attending the training sessions, mentoring sessions, and completing deliverables?',
            'Are there any legal issues or intellectual property concerns related to your startup?',
        ];

        $pre_acceleration = [
            'In which industry does your solution fit?',
            'Please Specify',
            'What stage is your solution currently in?',
            'What is the name of your solution?',
            'In a short paragraph, describe your solution briefly?',
            'Is your solution a software, hardware or System?',
            'Please Specify',
            'What Problem / Challenge does your solution tackle?',
            'Who are your target market and potential customers?',
            'How does your solution solve the problem and what\'s your value proposition?',
            'What is your competitive advantage?',
            'What types of impact does your solution make?',
            'Please Specify',
            'When did you start with the idea validation?',
            'Tell us briefly about your proof of concept/idea validation process?',
            'Is your Startup registered?',
            'When did you launch your solution in the market?',
            'What was your go-to-market Strategy?',
            'Please Specify',
            'What is your business model?',
            'Please Specify',
            'What is your revenue model? How does your business generate revenue?',
            'Please Specify',
            'Who are your current competitors? Locally and regionally?',
            'Did you get any funding so far?',
            'Please Specify',
            'What are the greatest challenges facing the implementation of your idea? If any please choose.',
            'Please Specify',
            'What traction and leads were you able to gain? Please clarify.',
            'What is your market validation strategy?',
            'Is the solution generating revenue already?',
            'What are the three top needs for your solution that the funding would fulfill?',
            'Does your solution meet one or more of the SDGs (Sustainable Development Goals)?',
            'Team Member Name',
            'Team Member Role',
            'Team Member Phone',
            'Team Member Email',
            'What are the upcoming milestones you aim to achieve throughout the programme. Please provide specific measurable milestones and the expected completion dates.',
            'Which of the following business skills do you have?',
            'Do you have any knowledge or experience in entrepreneurship/startups?',
            'Please specify your experience',
            'If you are looking to acquire one new skill, what would it be?',
            'How did you hear about the Orange Corners Incubation Programme?',
            'If you were selected, can you participate in a 3-day bootcamp?',
            'Please share a link to the prototype of your product so that we can get a better understanding of its features and functionalities.',
        ];

        $acceleration = [
            'Briefly describe your startup’s value proposition.',
            'What problem are you solving?',
            'Describe your solution and how it addresses the problem.',
            'What industry sector does your product/service target?',
            'Please Specify',
            'What is your current stage of development?',
            'Have you launched any version of your product?',
            'Can you provide details?',
            'What is your current traction (users, customers, revenue, partnerships, etc.)?',
            'What is your current funding status?',
            'Please Specify',
            'What key milestones have you achieved so far?',
            'Who are your target customers?',
            'What is the size of your target market? Provide any relevant data or estimates.',
            'How did you identify your target market? Describe any research methods used (surveys, interviews, focus groups, etc.).',
            'What is your competitive advantage? How do you differentiate your startup in the market?',
            'Please describe your revenue model:',
            'Please Specify',
            'What is your go-to-market strategy?',
            'What channels and tactics are you using for customer acquisition and growth?',
            'How many members does your team consist of?',
            'Name',
            'Role',
            'Phone',
            'Email',
            'Do you have at least two full-time team members dedicated to participating fully in all activities of the program?',
            'Please Specify',
            'How much have you invested in the company to date?',
            'Have you raised any funding for your company (incl. award/grant money)?',
            'How much additional funding do you seek to raise during or after the acceleration program?',
            'Have you registered your startup as a legal entity?',
            'Please provide details about the registration process (e.g., type of entity, country of registration, date of registration).',
            'Are there any legal issues or intellectual property concerns related to your startup?',
            'Are you currently in the process of registering your startup?',
            'Please provide an estimated timeline for completion.',
            'Where are you now in this timeline (which step have you reached so far)?',
            'What steps are required from this point forward to complete the registration process? Are there any legal issues or intellectual property concerns related to your startup?',
            'What is your vision for the startup in the next 1-3 years?',
            'What are the key milestones you aim to achieve during the acceleration program?',
            'How can Flow Accelerator support you in scaling your business?',
            'Have you participated in any other incubation or acceleration programs?',
            'If yes, please provide details.',
        ];

        $formation_labels = [
            // Startup Overview
            'Startup / Project Name',
            'What stage is your solution currently in?',
            'What specific problem or need does your startup address?',
            'Who is affected by this problem, and who is your target segment?',
            'How did you identify this problem? Please explain the process.',
            'What industry sector does your product/service target?',
            'Please Specify',

            // Solution & Uniqueness
            'Describe your solution. What product or service are you offering?',
            'What makes your solution unique, creative, or better than current alternatives?',
            'How does your solution address the problem better than existing alternatives?',
            'Have you validated the problem-solution fit?',
            'How did you validate it? (e.g., surveys, interviews, pilots). What feedback did you receive?',
            'Why not? What are your next steps for validation?',

            // Market Analysis
            'Have you identified your target market?',
            'How did you identify your target market?',
            'What makes this market a good opportunity?',
            'What research methods have you used? (e.g., surveys, interviews, focus groups)',
            'What challenges are you facing in identifying your market?',
            'Have you conducted a competitor analysis?',
            'Who are your competitors?',
            'What is your competitive advantage, and how do you plan to differentiate in the market?',

            // MVP
            'Do you currently have an MVP?',
            'Have you developed a roadmap for your MVP?',
            'What are the core features included in your MVP? What problem do they solve?',
            'What kind of support do you need to define or build your MVP plan?',
            'How can Flow Accelerator assist you in developing your MVP? (e.g., technical help, prototyping tools, mentorship)',

            // Commitment & Expectations
            'Why do you want to join the PIEC Formation and Development Program?',
            'What do you hope to achieve by the end of the program?',
            'Are you committed to attending extensive in-person and virtual training sessions, mentoring, and completing deliverables?',
            'Are there any legal or intellectual property issues related to your startup?',
            'Please explain.',

            // Progress & Team Info
            'How long have you been working on your idea?',
            'Are you applying as an individual or as part of a team?',

            'Team Member Name',
            'Team Member Role',
            'Team Member Phone',
            'Team Member Email',

            'How did you hear about the PIEC Program?',
            'Please Specify',
            'Anything else you\'d like to share with us?',
        ];

        $program = match ($this->level) {
            'ideation and innovation' => $ideation,
            'pre-incubation' => $pre_incubation,
            'incubation' => $incubation,
            'pre-acceleration' => $pre_acceleration,
            'acceleration' => $acceleration,
            'formation' => $formation_labels,
            default => $ideation
        };

        return [
            'Created At',
            'Status',
            'First Name',
            'Last Name',
            'Email',
            'Date of Birth',
            'Phone',
            'Whatsapp',
            'Gender',
            'Residence',
            'Other Governorate',
            'Describe Yourself',
            'Describe Yourself (Other)',
            'Occupation',
            'Degree',
            'School/University',
            'Major/Field of study',
            'Start Date',
            'Currently Studying There',
            'End Date',
            'Experience Type',
            'Company Name',
            'Title',
            'Start Date',
            'Currently Working There',
            'End Date',
            'Soft Skills',
            'Technical Skills',
            ...$program
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E'  => "0",
            'F'  => "0",
            'AT' => "0",
        ];

    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'alignment' => ['horizontal' => 'center', 'vertical' => 'center']],
        ];
    }
}
