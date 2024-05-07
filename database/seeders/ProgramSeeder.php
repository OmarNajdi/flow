<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Program::factory()->create([
            'name'        => 'PIEC',
            'level'       => 'pre-incubation',
            'open_date'   => '2024-05-01',
            'close_date'  => '2024-05-31',
            'description' =>
                "<p>Hello There,</p><p>If you're here, we believe you're ready to embark on your journey into entrepreneurship, and of course, we are more than eager to walk alongside you through our best practices.</p><p>Before you dive in, let us provide you with a brief overview of our program, PIEC, and where you currently stand:</p><p><br></p><p><strong>About PIEC:</strong></p><p>The PIEC program stands as a transformative endeavor, aimed at nurturing startups through crucial growth phases. By providing essential resources, mentorship, and networking opportunities, our program empowers startups to overcome obstacles and scale operations, contributing to economic progress and job creation in Palestine. Ultimately, we aspire to establish a sustainable and transformative support system for entrepreneurs, positioning Palestine as a leading startup hub regionally and globally.</p><p>To realize this vision, our program strategically focuses on several key objectives: fostering innovation and creativity, establishing a nurturing ecosystem, enhancing access to funding and mentorship, and facilitating investor readiness and global outreach.</p><p><strong>The Program Components:</strong></p><p><strong>#1: Ideation and Innovation&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; #2: Formation and Development&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;#3: Validation and Growth</strong></p><p><br></p><p><strong>Where You Stand Now? </strong>In the #1: Ideation and Innovation phase.</p><p>In the Ideation and Innovation phase, you will participate in a two-day hackathon (Hackathon and Pitching Day) where we provide the ideal environment to foster creativity and generate novel ideas to address specific challenges from various industries, utilizing AI. <strong>So, what defines you:</strong></p><p>-&nbsp; &nbsp; &nbsp; &nbsp; You're a motivated individual from diverse backgrounds: young professionals, university students, researchers, and tech enthusiasts.</p><p>-&nbsp; &nbsp; &nbsp; &nbsp; You possess a creative and problem-solving mindset.</p><p>-&nbsp; &nbsp; &nbsp; &nbsp; You can apply individually or as part of a team.</p><p>-&nbsp; &nbsp; &nbsp; &nbsp; You either have an idea in the AI sector or a specific challenge you want to address through AI.</p><p>-&nbsp; &nbsp; &nbsp; &nbsp; Even if you don't have a specific idea, you have skills in problem-solving and tackling challenges through AI.</p><p>-&nbsp; &nbsp; &nbsp; &nbsp; You're committed to taking steps in the startup world and progressing through our program components.</p>"
        ]);
    }
}
