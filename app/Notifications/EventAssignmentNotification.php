<?php

namespace App\Notifications;

use App\Mail\EventAssignment;
use App\Models\Event;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Notification;

class EventAssignmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private Event $event,
        private User $user,
        private Collection $positions
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
    public function toMail(object $notifiable): EventAssignment
    {
        return new EventAssignment($this->event, $this->user, $this->positions);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $positionNames = $this->positions->pluck('callsign')->join(', ');

        return [
            'title' => 'Event Position Assignment',
            'content' => "You have been assigned to position(s) {$positionNames} for the event '{$this->event->name}' on {$this->event->starts_at->format('M j, Y')}.",
            'link' => "/events/{$this->event->slug}",
            'event_id' => $this->event->id,
            'positions' => $this->positions->pluck('callsign')->toArray(),
        ];
    }
}
