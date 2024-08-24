<?php

namespace App\Filament\Resources\JobApplicationResource\Pages;

use App\Filament\Resources\JobApplicationResource;
use App\Filament\Resources\JobResource;
use App\Models\Application;
use App\Models\Job;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Session;

class CreateJobApplication extends CreateRecord
{
    protected static string $resource = JobApplicationResource::class;

    protected function authorizeAccess(): void
    {

        $job_id = request('job') ?? Session::get('job_id', request('job'));

        if ( ! $job_id) {
            $this->redirect(JobResource::getUrl('index'));
        }

        Session::put('job_id', $job_id);

        $job = Job::find(request('job'));

        if ( ! $job) {
            $this->redirect(JobResource::getUrl('index'));
        }
        $application = Application::where('user_id', auth()->id())->where('career_id', $job->id)->first();

        if ( ! $application) {
            $application = Application::create([
                'user_id'   => auth()->id(),
                'career_id' => $job->id,
                'status'    => 'Draft',
                'data'      => [
                    'first_name'        => auth()->user()->first_name,
                    'last_name'         => auth()->user()->last_name,
                    'email'             => auth()->user()->email,
                    'dob'               => auth()->user()->dob,
                    'phone'             => auth()->user()->phone,
                    'whatsapp'          => auth()->user()->whatsapp,
                    'gender'            => auth()->user()->gender,
                    'residence'         => auth()->user()->residence,
                    'residence_other'   => auth()->user()->residence_other,
                    'description'       => auth()->user()->description,
                    'description_other' => auth()->user()->description_other,
                    'occupation'        => auth()->user()->occupation,
                ]
            ]);
        }

        $this->redirect(JobApplicationResource::getUrl('edit', [$application]));
    }

}
