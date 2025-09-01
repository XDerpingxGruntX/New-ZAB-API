<?php

namespace App\Notifications;

use App\Mail\ActivityWarning;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ActivityWarningNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private User $user,
        private float $requiredHours
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
    public function toMail(object $notifiable): ActivityWarning
    {
        return new ActivityWarning($this->user, $this->requiredHours);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Controller Inactivity Notice',
            'content' => "You have not controlled the required {$this->requiredHours} hours this quarter to maintain your roster status. Please contact staff if you believe this is an error.",
            'link' => '/dashboard',
            'required_hours' => $this->requiredHours,
        ];
    }
}
