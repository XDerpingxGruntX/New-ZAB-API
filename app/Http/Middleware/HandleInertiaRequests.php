<?php

namespace App\Http\Middleware;

use App\Models\ControllerSession;
use App\Models\FlightSession;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'onlineFlightSessions' => FlightSession::query()
                ->where('updated_at', '>=', now()->subMinutes(15))
                ->with('user')
                ->get(),
            'onlineControllerSessions' => ControllerSession::query()
                ->whereNull('disconnected_at')
                ->with('user')
                ->get(),
            ...$this->aggregateTopFive(),
        ];
    }

    protected function aggregateTopFive(): array
    {
        $thisMonth = now()->startOfMonth();
        $nextMonth = now()->addMonth()->startOfMonth();

        $controllerSessions = ControllerSession::query()
            ->whereBetween('connected_at', [$thisMonth, $nextMonth])
            ->where('callsign', 'NOT LIKE', '%\_M\_%')
            ->with('user')
            ->get();

        $controllerTimes = [];
        $positionTimes = [];

        foreach ($controllerSessions as $controllerSession) {
            $duration = $controllerSession->connected_at->diffInSeconds($controllerSession->disconnected_at);

            if (! isset($controllerTimes[$controllerSession->cid])) {
                $controllerTimes[$controllerSession->cid] = [
                    'name' => $controllerSession->user->full_name,
                    'cid' => $controllerSession->cid,
                    'duration' => 0,
                ];
            }
            $controllerTimes[$controllerSession->cid]['duration'] += $duration;

            $positionKey = "{$controllerSession->airport->value}_{$controllerSession->position->value}";
            if (! isset($positionTimes[$positionKey])) {
                $positionTimes[$positionKey] = [
                    'name' => "{$controllerSession->airport->getDisplayName()} {$controllerSession->position->getDisplayName()}",
                    'duration' => 0,
                ];
            }
            $positionTimes[$positionKey]['duration'] += $duration;
        }

        return [
            'topControllers' => collect($controllerTimes)
                ->sortByDesc('duration')
                ->values()
                ->take(5),
            'topPositions' => collect($positionTimes)
                ->sortByDesc('duration')
                ->values()
                ->take(5),
        ];
    }
}
