<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\ApplicationReminder;
use Illuminate\Console\Command;

class SendApplicationReminderEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'application:reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Application Reminders to Applicants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::whereHas('applications', function ($query) {
            $query->where('program_id', 9)->where('status', '!=', 'Submitted');
        })->get();

        foreach ($users as $user) {
            $user->notify(new ApplicationReminder([
                'program'    => 'GreenTech',
                'first_name' => $user->first_name,
                'close_date' => 'January 3',
                'url'        => 'https://dashboard.flow.ps/programs/9'
            ]));
        }
    }
}
