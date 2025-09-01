<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\DossierService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserDashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        // Get recent controller sessions (last 30 days)
        $recentSessions = $user->controllerSessions()
            ->whereNotNull('disconnected_at')
            ->where('connected_at', '>=', now()->subDays(30))
            ->orderBy('connected_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($session) {
                return [
                    'position' => $session->position?->getDisplayName() ?? 'Unknown',
                    'timeStart' => $session->connected_at->toISOString(),
                    'timeEnd' => $session->disconnected_at->toISOString(),
                ];
            });

        // Calculate quarterly hours
        $quarterStart = Carbon::now()->startOfQuarter();
        $quarterEnd = Carbon::now()->endOfQuarter();

        $quarterlyMinutes = $user->controllerSessions()
            ->whereNotNull('disconnected_at')
            ->whereBetween('connected_at', [$quarterStart, $quarterEnd])
            ->get()
            ->sum(function ($session) {
                return $session->connected_at->diffInMinutes($session->disconnected_at);
            });

        return Inertia::render('Dashboard/Index', [
            'controllingSessions' => $recentSessions,
            'quarterlyMinutes' => $quarterlyMinutes,
            'quarterEnd' => $quarterEnd->toDateString(),
            'hasMetRequirement' => $quarterlyMinutes >= 180,
        ]);
    }

    public function edit(): Response
    {
        return Inertia::render('Dashboard/Edit', [
            'user' => auth()->user(),
        ]);
    }

    public function update(Request $request, DossierService $dossierService): RedirectResponse
    {
        $request->validate([
            'bio' => 'nullable|string|max:2000',
        ]);

        auth()->user()->update([
            'bio' => $request->input('bio'),
        ]);

        $dossierService->create(
            auth()->user(),
            'updated their profile.'
        );

        return redirect()->route('dashboard.edit')
            ->with('success', 'Profile updated successfully.');
    }

    public function feedback(): Response
    {
        $user = auth()->user();

        $feedback = $user->feedbackAsController()
            ->whereNotNull('approved_at')
            ->orderBy('created_at', 'desc')
            ->with('critic')
            ->paginate(10);

        return Inertia::render('Dashboard/Feedback', [
            'feedback' => $feedback,
        ]);
    }
}
