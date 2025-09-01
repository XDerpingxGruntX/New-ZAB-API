<?php

namespace App\Jobs;

use App\Data\ControllerData;
use App\Models\User;
use App\Services\VATUSA;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SyncRoster implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct() {}

    /**
     * Execute the job.
     *
     * @throws ConnectionException
     */
    public function handle(VATUSA $client): void
    {
        $roster = $client->getFacilityRoster('ZAB', 'both');
        $changes = $this->calculateRosterChanges($roster);

        $this->processChanges($changes, $roster);
    }

    /**
     * Calculate the changes needed to sync the roster.
     *
     * @param  Collection<int, ControllerData>  $roster
     * @return array<string, Collection<int, int>>
     */
    protected function calculateRosterChanges(Collection $roster): array
    {
        // Get current user collections
        $controllers = User::all()->pluck('cid');
        $members = User::whereMember(true)->pluck('cid');
        $nonMembers = User::whereMember(false)->pluck('cid');
        $homeControllers = User::whereVisitor(false)->pluck('cid');
        $visitingControllers = User::whereVisitor(true)->pluck('cid');

        // Get VATUSA collections
        $fetchedControllers = $roster->pluck('cid');
        $fetchedHomeControllers = $roster->where('membership', 'home')->pluck('cid');
        $fetchedVisitingControllers = $roster->where('membership', '!=', 'home')->pluck('cid');

        return [
            'toBeAdded' => $fetchedControllers->diff($controllers),
            'makeNonMember' => $members->diff($fetchedControllers),
            'makeMember' => $nonMembers->intersect($fetchedControllers),
            'makeVisitor' => $homeControllers->intersect($fetchedVisitingControllers),
            'makeHome' => $visitingControllers->intersect($fetchedHomeControllers),
        ];
    }

    /**
     * Process all the calculated changes.
     *
     * @param  array<string, Collection<int, int>>  $changes
     * @param  Collection<int, ControllerData>  $roster
     */
    protected function processChanges(array $changes, Collection $roster): void
    {
        $this->processNewControllers($changes['toBeAdded'], $roster);
        $this->processMembershipChanges($changes['makeMember'], $changes['makeNonMember']);
        $this->processVisitorStatusChanges($changes['makeVisitor'], $changes['makeHome']);
    }

    /**
     * Process new controllers that need to be added.
     *
     * @param  Collection<int, int>  $toBeAdded
     * @param  Collection<int, ControllerData>  $roster
     */
    protected function processNewControllers(Collection $toBeAdded, Collection $roster): void
    {
        $toBeAdded->each(function (int $cid) use ($roster) {
            $controllerData = $roster->firstWhere('cid', $cid);

            if (! $controllerData) {
                return;
            }

            $user = User::create([
                'cid' => $controllerData->cid,
                'first_name' => $controllerData->first_name,
                'last_name' => $controllerData->last_name,
                'email' => $controllerData->email,
                'rating' => $controllerData->rating,
                'home_facility' => $controllerData->home_facility,
                'roles' => $controllerData->membership === 'home' ? $controllerData->mappedRoles->map->value->toArray() : null,
                'broadcast_opt_in' => $controllerData->broadcast_opt_in,
                'member' => true,
                'visitor' => $controllerData->membership !== 'home',
                'operating_initials' => $this->generateOperatingInitials($controllerData->first_name,
                    $controllerData->last_name),
                'next_activity_check_at' => now()->addDays(90),
            ]);
        });
    }

    /**
     * Process membership status changes.
     *
     * @param  Collection<int, int>  $makeMember
     * @param  Collection<int, int>  $makeNonMember
     */
    protected function processMembershipChanges(Collection $makeMember, Collection $makeNonMember): void
    {
        $makeMember->each(function (int $cid) {
            User::whereCid($cid)->update(['member' => true]);
        });

        $makeNonMember->each(function (int $cid) {
            User::whereCid($cid)->update(['member' => false]);
        });
    }

    /**
     * Process visitor status changes.
     *
     * @param  Collection<int, int>  $makeVisitor
     * @param  Collection<int, int>  $makeHome
     */
    protected function processVisitorStatusChanges(Collection $makeVisitor, Collection $makeHome): void
    {
        $makeVisitor->each(function (int $cid) {
            $user = User::whereCid($cid)->first();

            if ($user) {
                $user->update([
                    'visitor' => true,
                    'roles' => null,
                ]);
            }
        });

        $makeHome->each(function (int $cid) {
            User::whereCid($cid)->update(['visitor' => false]);
        });
    }

    /**
     * Generate operating initials for a new controller.
     */
    protected function generateOperatingInitials(
        string $first_name,
        string $last_name,
    ): string|false {
        $MAX_TRIES = 10;
        $used_initials = User::all()->pluck('operating_initials')->toArray();
        $attempts = [];

        // Try initials based on first and last name
        $attempts[] = Str::upper(Str::substr(trim($first_name), 0, 1) . Str::substr(trim($last_name), 0, 1));
        $attempts[] = Str::upper(Str::substr(trim($last_name), 0, 1) . Str::substr(trim($first_name), 0, 1));

        // Try random selections from combined names
        $chars = Str::upper(trim($last_name) . trim($first_name));
        for ($i = 0; $i < $MAX_TRIES; $i++) {
            $attempts[] = Str::substr(str_shuffle($chars), 0, 2);
        }

        // Try completely random initials
        for ($i = 0; $i < $MAX_TRIES; $i++) {
            $attempts[] = Str::random(2);
        }

        // Return the first unique set of initials
        foreach ($attempts as $initials) {
            if (! in_array($initials, $used_initials)) {
                return $initials;
            }
        }

        return false;
    }
}
