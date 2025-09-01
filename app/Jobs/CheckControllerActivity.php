<?php

namespace App\Jobs;

use App\Enums\ControllerRating;
use App\Mail\ActivityReminder;
use App\Mail\ActivityWarning;
use App\Models\ControllerSession;
use App\Models\User;
use App\Notifications\ActivityReminderNotification;
use App\Notifications\ActivityWarningNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class CheckControllerActivity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected const int REQUIRED_HOURS = 3;
    protected const int REMINDER_DAYS_BEFORE = 30; // Send reminder 30 days before quarter end

    protected Carbon $quarterStart;

    protected Carbon $quarterEnd;

    protected int $daysUntilQuarterEnd;

    protected int $activityWindow;

    public function __construct(protected User $user)
    {
        $this->quarterStart = now()->startOfQuarter();
        $this->quarterEnd = now()->endOfQuarter();
        $this->daysUntilQuarterEnd = now()->diffInDays($this->quarterEnd);

        $this->activityWindow = now()->startOfQuarter()->diffInDays(now()->endOfQuarter()) + 1;
    }

    public function handle(): void
    {
        $hours = $this->getControllerHours($this->user, $this->quarterStart);

        if ($this->daysUntilQuarterEnd <= 0) {
            // Quarter has ended, send warning if reminder was previously sent
            if ($this->user->warning_delivered_at && $this->isControllerInactive($this->user, $hours)) {
                $this->user->update(['warning_delivered_at' => null]);

                // Send both email and notification
                Mail::to($this->user)
                    ->cc('zab-datm@vatusa.net')
                    ->send(new ActivityWarning($this->user, self::REQUIRED_HOURS));

                $this->user->notify(new ActivityWarningNotification($this->user, self::REQUIRED_HOURS));
            }
        } elseif ($this->daysUntilQuarterEnd <= self::REMINDER_DAYS_BEFORE) {
            // Within reminder period and insufficient hours
            if (! $this->user->warning_delivered_at
                && $this->isControllerInactive($this->user, $hours)) {
                $this->user->update([
                    'warning_delivered_at' => now(),
                    'next_activity_check_at' => $this->quarterEnd,
                ]);

                $reminderData = [
                    'hours' => $hours,
                    'requiredHours' => self::REQUIRED_HOURS,
                    'activityWindow' => $this->activityWindow,
                    'gracePeriod' => $this->daysUntilQuarterEnd,
                ];

                // Send both email and notification
                Mail::to($this->user)->send(new ActivityReminder($this->user, $reminderData));
                $this->user->notify(new ActivityReminderNotification($this->user, $reminderData));
            }
        }
    }

    protected function getControllerHours(User $user, Carbon $since): float
    {
        return ControllerSession::query()
            ->whereBelongsTo($user)
            ->where('connected_at', '>', $since)
            ->get()
            ->sum(fn (ControllerSession $session) => $session->duration->hours);
    }

    protected function isControllerInactive(User $user, float $hours): bool
    {
        return $hours < self::REQUIRED_HOURS && $user->rating !== ControllerRating::OBS;
    }
}
