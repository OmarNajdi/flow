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
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Application::where('program_id',2)->get();
    }

    public function map($application): array
    {
        $team_members = array_values($application->data['team_members'] ?? []);

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
    }

    public function headings(): array
    {
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
