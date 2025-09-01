<?php

namespace App\Notifications;

use App\Mail\AbsenceConfirmation;
use App\Models\Absence;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AbsenceConfirmationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private Absence $absence)
    {
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
    public function toMail(object $notifiable): AbsenceConfirmation
    {
        return new AbsenceConfirmation($this->absence);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $startDate = $this->absence->starts_at->format('M j, Y');
        $endDate = $this->absence->expires_at ? $this->absence->expires_at->format('M j, Y') : 'indefinite';

        return [
            'title' => 'Leave of Absence Confirmed',
            'content' => "Your leave of absence from {$startDate} to {$endDate} has been approved and confirmed.",
            'link' => '/dashboard',
            'absence_id' => $this->absence->id,
            'starts_at' => $this->absence->starts_at->toISOString(),
            'expires_at' => $this->absence->expires_at?->toISOString(),
        ];
    }
}
