<?php

namespace App\Models;

use App\Enums\ControllerPosition;
use App\Enums\ControllerRating;
use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'cid',
        'first_name',
        'last_name',
        'email',
        'bio',
        'rating',
        'operating_initials',
        'home_facility',
        'roles',
        'broadcast_opt_in',
        'member',
        'visitor',
        'next_activity_check_at',
        'warning_delivered_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'full_name',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['certifications', 'notifications', 'readNotifications', 'unreadNotifications'];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'cid';
    }

    public function absences(): HasMany
    {
        return $this->hasMany(Absence::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function certifications(): BelongsToMany
    {
        return $this->belongsToMany(Certification::class)->withTimestamps();
    }

    public function controllerSessions(): HasMany
    {
        return $this->hasMany(ControllerSession::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(Download::class);
    }

    public function eventPositions(): HasMany
    {
        return $this->hasMany(EventPosition::class);
    }

    public function eventRegistrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function feedbackAsCritic(): HasMany
    {
        return $this->hasMany(Feedback::class, 'critic_id');
    }

    public function feedbackAsController(): HasMany
    {
        return $this->hasMany(Feedback::class, 'controller_id');
    }

    public function flightSessions(): HasMany
    {
        return $this->hasMany(FlightSession::class);
    }

    public function visitorApplications(): HasMany
    {
        return $this->hasMany(VisitorApplication::class);
    }

    public function dossiers(): HasMany
    {
        return $this->hasMany(Dossier::class);
    }

    public function dossiersAsAffected(): HasMany
    {
        return $this->hasMany(Dossier::class, 'affected_user_id');
    }

    /**
     * Check if the user is a manager.
     */
    public function isManager(): bool
    {
        return collect($this->roles)->contains(fn (Role $role) => in_array($role, [Role::ATM, Role::DATM]));
    }

    /**
     * Check if the user is a senior staff member.
     */
    public function isSenior(): bool
    {
        return collect($this->roles)->contains(fn (Role $role) => in_array($role, [Role::ATM, Role::DATM, Role::TA]));
    }

    /**
     * Check if the user is a staff member.
     */
    public function isStaff(): bool
    {
        return collect($this->roles)->contains(fn (Role $role) => in_array($role, [
            Role::ATM, Role::DATM, Role::TA, Role::EC, Role::WM, Role::FE,
        ]));
    }

    /**
     * Check if the user is an instructor.
     */
    public function isInstructor(): bool
    {
        return collect($this->roles)->contains(fn (Role $role) => in_array($role, [
            Role::ATM, Role::DATM, Role::TA, Role::INS, Role::MTR,
        ]));
    }

    /**
     * Scope a query to only include inactive members.
     */
    public function scopeInactive(Builder $query): void
    {
        $query
            ->whereMember(true)
            ->where(function (Builder $query) {
                $query->wherePast('next_activity_check_at')
                    ->orwhereNull('next_activity_check_at');
            });
    }

    /**
     * Scope a query to only include users to be removed.
     */
    public function scopeToBeRemoved(Builder $query): void
    {
        $query
            ->whereMember(true)
            ->whereNotNull('warning_delivered_at')
            ->wherePast('warning_delivered_at');
    }

    /**
     * Get monthly, ancient, and total controller session metrics for this user.
     */
    public function getControllerMetrics(): array
    {
        $now = now();
        $oneYearAgo = $now->copy()->subYear();

        // Get all sessions for this controller
        $allSessions = $this->controllerSessions()
            ->orderBy('connected_at', 'desc')
            ->get();

        // Initialize the base stats structure with enum values
        $emptyPositionStats = collect(ControllerPosition::cases())
            ->mapWithKeys(fn (ControllerPosition $position) => [$position->value => 0])
            ->all();

        $metrics = [
            'totalControllerSessions' => [...$emptyPositionStats, 'total' => 0],
            'ancientControllerSessions' => [...$emptyPositionStats, 'total' => 0],
            'monthlyControllerSessions' => [],
            'sessionCount' => $allSessions->count(),
            'sessionAverage' => 0,
        ];

        // Initialize the last 12 months
        for ($i = 0; $i < 12; $i++) {
            $monthDate = $now->copy()->subMonths($i);
            $monthKey = $monthDate->format('M Y');
            $metrics['monthlyControllerSessions'][$monthKey] = [...$emptyPositionStats, 'total' => 0];
        }

        // Process each session
        foreach ($allSessions as $session) {
            $duration = $session->connected_at->diffInSeconds($session->disconnected_at);

            if (! $session->position) {
                continue;
            }

            $positionValue = $session->position->value;

            // Add to total stats
            $metrics['totalControllerSessions'][$positionValue] += $duration;
            $metrics['totalControllerSessions']['total'] += $duration;

            // Determine which bucket this session belongs in
            if ($session->connected_at->lt($oneYearAgo)) {
                $metrics['ancientControllerSessions'][$positionValue] += $duration;
                $metrics['ancientControllerSessions']['total'] += $duration;
            } else {
                $monthKey = $session->connected_at->format('M Y');
                if (isset($metrics['monthlyControllerSessions'][$monthKey])) {
                    $metrics['monthlyControllerSessions'][$monthKey][$positionValue] += $duration;
                    $metrics['monthlyControllerSessions'][$monthKey]['total'] += $duration;
                }
            }
        }

        // Calculate session average
        if ($metrics['sessionCount'] > 0) {
            $metrics['sessionAverage'] = round($metrics['totalControllerSessions']['total'] / $metrics['sessionCount']);
        }

        return $metrics;
    }

    /**
     * Get the user's full name.
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn (
                mixed $value,
                array $attributes
            ) => trim($attributes['first_name']) . ' ' . trim($attributes['last_name']),
        );
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'rating' => ControllerRating::class,
            'roles' => AsEnumCollection::of(Role::class),
            'broadcast_opt_in' => 'boolean',
            'member' => 'boolean',
            'visitor' => 'boolean',
            'next_activity_at' => 'datetime',
            'warning_delivered_at' => 'datetime',
        ];
    }
}
