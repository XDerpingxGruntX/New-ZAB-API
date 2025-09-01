<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ControllerSession;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class ActivityController extends Controller
{
    protected const int REQUIRED_HOURS = 3; // 3 hours minimum activity requirement
    protected const int ACTIVITY_PERIOD_DAYS = 91; // 91 days activity period

    public function index(): Response
    {
        // Calculate activity for the last 91 days
        $checkDate = now()->subDays(self::ACTIVITY_PERIOD_DAYS);

        // Get all members with their certifications and absences
        $users = User::query()
            ->whereMember(true)
            ->with(['certifications', 'absences' => function ($query) {
                $query->where('starts_at', '<=', now())
                    ->where('expires_at', '>=', now());
            }])
            ->orderBy('last_name')
            ->get();

        // Calculate controller hours for each user
        $userActivity = [];

        foreach ($users as $user) {
            $totalTime = ControllerSession::query()
                ->whereBelongsTo($user)
                ->where('connected_at', '>', $checkDate)
                ->whereNotNull('disconnected_at')
                ->get()
                ->sum(fn (ControllerSession $session) => $session->duration->totalSeconds);

            $userActivity[] = [
                'id' => $user->id,
                'cid' => $user->cid,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'full_name' => $user->full_name,
                'operating_initials' => $user->operating_initials,
                'rating' => $user->rating->value,
                'rating_short' => $user->rating->getAbbreviation(),
                'rating_long' => $user->rating->getDisplayName(),
                'visitor' => $user->visitor,
                'member' => $user->member,
                'total_time' => $totalTime,
                'join_date' => $user->created_at,
                'certifications' => $user->certifications->map(function ($cert) {
                    return [
                        'id' => $cert->id,
                        'code' => $cert->code,
                        'name' => $cert->name,
                        'class' => $cert->class->value,
                    ];
                }),
                'has_absence' => $user->absences->isNotEmpty(),
                'too_low' => $totalTime < (self::REQUIRED_HOURS * 3600) && $user->created_at < $checkDate,
                'protected' => $user->isStaff(),
            ];
        }

        return Inertia::render('Admin/Activity/Index', [
            'users' => $userActivity,
            'checkDate' => $checkDate->toISOString(),
            'requiredHours' => self::REQUIRED_HOURS,
        ]);
    }
}
