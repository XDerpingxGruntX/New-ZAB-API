<?php

namespace App\Notifications;

use App\Mail\VisitorApplicationApproved;
use App\Mail\VisitorApplicationRejected;
use App\Models\VisitorApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class VisitorApplicationStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private VisitorApplication $application,
        private string $status,
        private ?string $reason = null
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
    public function toMail(object $notifiable): VisitorApplicationApproved|VisitorApplicationRejected
    {
        return $this->status === 'approved'
            ? new VisitorApplicationApproved($this->application)
            : new VisitorApplicationRejected($this->application);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $title = $this->status === 'approved'
            ? 'Visitor Application Approved'
            : 'Visitor Application Rejected';

        $content = $this->status === 'approved'
            ? 'Congratulations! Your visitor application has been approved. You now have access to Albuquerque ARTCC facilities.'
            : 'Your visitor application has been rejected.' . ($this->reason ? " Reason: {$this->reason}" : ' Please contact staff for more information.');

        return [
            'title' => $title,
            'content' => $content,
            'link' => $this->status === 'approved' ? '/dashboard' : '/visitor-applications/create',
            'application_id' => $this->application->id,
            'status' => $this->status,
            'reason' => $this->reason,
        ];
    }
}
