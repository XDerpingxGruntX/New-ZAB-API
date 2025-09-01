<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventPosition;
use App\Models\EventRegistration;
use App\Services\DossierService;
use Illuminate\Http\Request;

class EventRegistrationController extends Controller
{
    public function store(Request $request, Event $event, DossierService $dossierService)
    {
        $user = auth()->user();

        if (! $user) {
            return back()->withErrors(['error' => 'You must be logged in to register for events.']);
        }

        if (! $user->member) {
            return back()->withErrors(['error' => 'You must be a member of ZAB to register for events.']);
        }

        // Check if registration is open
        $now = now();
        if (! $event->registration_opens_at || ! $event->registration_closes_at ||
            $now < $event->registration_opens_at || $now > $event->registration_closes_at) {
            return back()->withErrors(['error' => 'Registration is not open for this event.']);
        }

        // Check if user is already registered
        $existingRegistration = EventRegistration::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingRegistration) {
            return back()->withErrors(['error' => 'You are already registered for this event.']);
        }

        // Check if user already has an assigned position
        $assignedPosition = EventPosition::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->where('assigned', true)
            ->first();

        if ($assignedPosition) {
            return back()->withErrors(['error' => 'You have already been assigned a position for this event.']);
        }

        $request->validate([
            'position_requests' => 'array|max:3',
            'position_requests.*' => 'string|nullable',
        ]);

        // Prepare requested positions
        $positionIds = [];
        if ($request->position_requests) {
            foreach ($request->position_requests as $callsign) {
                if (! empty($callsign)) {
                    $position = EventPosition::where('event_id', $event->id)
                        ->where('callsign', $callsign)
                        ->first();
                    if ($position) {
                        $positionIds[] = $position->id;
                    }
                }
            }
        }

        // Create the registration with requested positions
        $registration = EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'requested_positions' => $positionIds, // This will be cast to JSON
        ]);

        $dossierService->create(
            $user,
            "signed up for the event *{$event->name}*."
        );

        return back()->with('success', 'Registration submitted successfully!');
    }

    public function destroy(Event $event, EventRegistration $registration, DossierService $dossierService)
    {
        $user = auth()->user();

        if (! $user) {
            return back()->withErrors(['error' => 'You must be logged in.']);
        }

        // Check if this registration belongs to the current user
        if ($registration->user_id !== $user->id) {
            return back()->withErrors(['error' => 'You can only delete your own registrations.']);
        }

        // Check if user has been assigned a position
        $assignedPosition = EventPosition::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->where('assigned', true)
            ->first();

        if ($assignedPosition) {
            return back()->withErrors(['error' => 'You cannot delete your registration as you have been assigned a position. Contact the Events Team.']);
        }

        $dossierService->create(
            $user,
            "deleted their signup for the event *{$event->name}*."
        );

        $registration->delete();

        return back()->with('success', 'Registration deleted successfully!');
    }
}
