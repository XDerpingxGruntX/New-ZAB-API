<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRegistrationRequest;
use App\Models\Event;
use App\Models\User;
use App\Services\DossierService;
use Illuminate\Http\RedirectResponse;

class EventRegistrationController extends Controller
{
    public function store(
        StoreEventRegistrationRequest $request,
        Event $event,
        DossierService $dossierService
    ): RedirectResponse {
        $user = User::where('cid', $request->validated()['cid'])->first();

        if (! $user) {
            return back()->with('error', 'Controller not found.');
        }

        // Check if user is already registered for this event
        if ($event->eventRegistrations()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Controller is already registered for this event.');
        }

        $event->eventRegistrations()->create([
            'user_id' => $user->id,
            'requested_positions' => [], // Empty array for manual signup
        ]);

        $dossierService->create(
            auth()->user(),
            "manually signed up %a for the event *{$event->name}*.",
            $user
        );

        return back()->with('success', 'Sign-up manually added.');
    }

    public function destroy(Event $event, User $user, DossierService $dossierService): RedirectResponse
    {
        $registration = $event->eventRegistrations()->where('user_id', $user->id)->first();

        if (! $registration) {
            return back()->with('error', 'Registration not found.');
        }

        $dossierService->create(
            auth()->user(),
            "manually deleted the event signup for %a for the event *{$event->name}*.",
            $user
        );

        $registration->delete();

        return back()->with('success', 'Sign-up manually deleted.');
    }
}
