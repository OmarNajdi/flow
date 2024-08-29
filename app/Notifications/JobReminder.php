<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobReminder extends Notification implements ShouldQueue
{
    use Queueable;

    protected $job, $first_name, $close_date, $url;

    /**
     * Create a new notification instance.
     */
    public function __construct($application)
    {
        $this->job        = $application['job'];
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
            ->subject('Complete Your Application for '.$this->job.' by '.$this->close_date)
            ->greeting('Dear '.$this->first_name.',')
            ->line('We noticed that you started an application for the '.$this->job.' position at Flow Accelerator but haven\'t submitted it yet. we wanted to send a friendly reminder that the deadline for submitting your application is approaching on .'.$this->close_date)
            ->line('We would love to consider you for this opportunity, so please make sure to complete and submit your application before the deadline.')
            ->line('Looking forward to receiving your completed application!')
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
