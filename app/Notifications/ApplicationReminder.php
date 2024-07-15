<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationReminder extends Notification implements ShouldQueue
{
    use Queueable;

    protected $program, $first_name, $close_date, $url;

    /**
     * Create a new notification instance.
     */
    public function __construct($application)
    {
        $this->program    = $application['program'];
        $this->first_name = $application['first_name'];
        $this->close_date = $application['close_date'];
        $this->url        = $application['url'];
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
            ->subject('Complete Your Application for '.$this->program.' by '.$this->close_date)
            ->greeting('Dear '.$this->first_name.',')
            ->line('Our records indicate that you have started your application to join '.$this->program.', but it has not yet been submitted. We wanted to remind you that the deadline to finalize and submit your application is fast approaching.')
            ->line('Closing Date: '.$this->close_date)
            ->line('To complete your application, please log in to your account and ensure all required information is filled out and submitted. If you encounter any issues or have any questions, please do not hesitate to reach out to our Program team by replying directly to this email.')
            ->line('This program offers a unique opportunity, and we don’t want you to miss out. We are excited about the potential of having you join us and look forward to reviewing your completed application.')
            ->line('Thank you for your interest in '.$this->program.'. We wish you the best of luck in completing your application.')
            ->action('Complete Application', $this->url)
            ->line('Best regards,')
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
