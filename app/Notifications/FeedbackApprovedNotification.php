<?php

namespace App\Notifications;

use App\Mail\FeedbackApproved;
use App\Models\Feedback;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class FeedbackApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private Feedback $feedback)
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
    public function toMail(object $notifiable): FeedbackApproved
    {
        return new FeedbackApproved($this->feedback);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Feedback Approved',
            'content' => "Your feedback from {$this->feedback->created_at->format('M j, Y')} has been approved and is now visible to the controller.",
            'link' => '/dashboard/feedback',
            'feedback_id' => $this->feedback->id,
        ];
    }
}
