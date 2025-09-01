<?php

namespace App\Notifications;

use App\Mail\ActivityReminder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ActivityReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private User $user,
        private array $data
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): ActivityReminder
    {
        return new ActivityReminder($this->user, $this->data);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $hours = number_format($this->data['hours'], 1);
        $required = number_format($this->data['requiredHours'], 1);
        $remaining = $this->data['gracePeriod'];

        return [
            'title' => 'Activity Reminder',
            'content' => "You have controlled {$hours} hours this quarter but need {$required} hours. You have {$remaining} days remaining to meet the requirement.",
            'link' => '/dashboard',
            'hours_controlled' => $this->data['hours'],
            'required_hours' => $this->data['requiredHours'],
            'grace_period_days' => $this->data['gracePeriod'],
        ];
    }
}
