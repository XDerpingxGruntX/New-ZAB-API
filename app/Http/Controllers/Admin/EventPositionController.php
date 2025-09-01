<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignEventPositionRequest;
use App\Mail\EventAssignment;
use App\Models\Event;
use App\Models\EventPosition;
use App\Models\User;
use App\Notifications\EventAssignmentNotification;
use App\Services\DossierService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class EventPositionController extends Controller
{
    public function show(Event $event): Response
    {
        $event->load([
            'eventRegistrations.user',
            'eventPositions.user',
        ]);

        // Load the requested positions for each registration
        foreach ($event->eventRegistrations as $registration) {
            if ($registration->requested_positions) {
                $registration->requested_position_objects = EventPosition::whereIn('id',
                    $registration->requested_positions)
                    ->get(['id', 'callsign']);
            } else {
                $registration->requested_position_objects = collect();
            }
        }

        return Inertia::render('Admin/Events/Assign', [
            'event' => $event,
        ]);
    }

    public function assign(
        AssignEventPositionRequest $request,
        Event $event,
        DossierService $dossierService
    ): RedirectResponse {
        $validated = $request->validated();
        $position = EventPosition::findOrFail($validated['position_id']);

        // Verify the position belongs to this event
        if ($position->event_id !== $event->id) {
            return back()->with('error', 'Position does not belong to this event.');
        }

        // If user_id is null, we're unassigning the position
        if ($validated['user_id'] === null) {
            $position->update([
                'user_id' => null,
                'assigned' => false,
            ]);

            $dossierService->create(
                auth()->user(),
                "unassigned *{$position->callsign}* for *{$event->name}*."
            );

            return back()->with('success', "Position {$position->callsign} unassigned.");
        }

        // Check if user is registered for this event
        $user = User::findOrFail($validated['user_id']);
        if (! $event->eventRegistrations()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'User must be registered for this event to be assigned a position.');
        }

        $position->update([
            'user_id' => $user->id,
            'assigned' => true,
        ]);

        $dossierService->create(
            auth()->user(),
            "assigned %a to *{$position->callsign}* for *{$event->name}*.",
            $user
        );

        return back()->with('success', "Position {$position->callsign} assigned to {$user->full_name}.");
    }

    public function notify(Event $event, DossierService $dossierService): RedirectResponse
    {
        // Check if emails have already been sent
        if ($event->emails_sent_at) {
            return back()->with('error', 'Assignment emails have already been sent for this event.');
        }

        // Get all assigned positions with users
        $assignedPositions = $event->eventPositions()
            ->where('assigned', true)
            ->with('user')
            ->get();

        if ($assignedPositions->isEmpty()) {
            return back()->with('error', 'No positions are currently assigned.');
        }

        // Group positions by user
        $userAssignments = $assignedPositions->groupBy('user_id');

        // Send email and notification to each assigned user
        foreach ($userAssignments as $userId => $positions) {
            $user = $positions->first()->user;

            // Send email
            Mail::to($user->email)->send(new EventAssignment($event, $user, $positions));

            // Send notification
            $user->notify(new EventAssignmentNotification($event, $user, $positions));
        }

        $event->update([
            'emails_sent_at' => now(),
        ]);

        $dossierService->create(
            auth()->user(),
            "notified controllers of positions for the event *{$event->name}*."
        );

        return back()->with('success', 'Assignment notifications sent to all assigned controllers.');
    }
}
