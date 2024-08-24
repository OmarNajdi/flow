<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobApplicationSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $job, $first_name;

    /**
     * Create a new notification instance.
     */
    public function __construct($application)
    {
        $this->job        = $application['job'];
        $this->first_name = $application['first_name'];
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Confirmation of Application Receipt for '.$this->job)
            ->greeting('Hello '.$this->first_name.',')
            ->line('Thank you for applying for the '.$this->job.' at Flow Accelerator. We have successfully received your application, and we appreciate your interest in joining our team.')
            ->line('Our HR team is currently reviewing all applications to identify candidates whose qualifications best match the requirements of the position. We will be in touch shortly after completing this review and the shortlisting process.')
            ->line('Thank you for considering Flow Accelerator as your next career opportunity.')
            ->line('Best Regards,')
            ->line('Flow Accelerator')
            ->salutation(' ');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
