<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\JobReminder;
use Illuminate\Console\Command;

class SendJobReminderEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Job Reminders to Applicants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::whereHas('applications', function ($query) {
            $query->where('career_id', 1)->where('status', '!=', 'Submitted');
        })->get();

        foreach ($users as $user) {
            $user->notify(new JobReminder([
                'job'        => 'Research Analyst',
                'first_name' => $user->first_name,
                'close_date' => 'August 31',
                'url'        => 'https://dashboard.flow.ps/jobs/1'
            ]));
        }
    }
}
