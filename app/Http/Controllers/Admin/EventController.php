<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Airport;
use App\Enums\ControllerPosition;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Services\DossierService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Events/Index', [
            'events' => fn () => Event::query()->whereFuture('ends_at')->latest()->get(),
            'pastEvents' => fn () => Event::query()->wherePast('ends_at')->latest()->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Events/Create');
    }

    public function store(StoreEventRequest $request, DossierService $dossierService): RedirectResponse
    {
        $validated = $request->validated();
        $validated['slug'] ??= Str::slug($validated['name']);

        if ($request->hasFile('banner')) {
            $validated['banner_path'] = $request->file('banner')->storePubliclyAs('events',
                $validated['slug'] . '.' . $request->file('banner')->extension(), 'public');
        }

        $event = Event::create($validated);

        $dossierService->create(
            auth()->user(),
            "created the event *{$event->name}*."
        );

        return redirect()->route('admin.events.edit', $event);
    }

    public function edit(Event $event): Response
    {
        return Inertia::render('Admin/Events/Edit', [
            'event' => $event->load('eventPositions'),
        ]);
    }

    public function update(UpdateEventRequest $request, Event $event, DossierService $dossierService): RedirectResponse
    {
        $validated = $request->validated();
        $validated['slug'] ??= Str::slug($validated['name'] ?? $event->name);

        if ($request->hasFile('banner')) {
            $validated['banner_path'] = $request->file('banner')->storePubliclyAs('events',
                $validated['slug'] . '.' . $request->file('banner')->extension(), 'public');
        }

        if (isset($validated['positions'])) {
            $event->eventPositions()->whereNotIn('callsign', $validated['positions'])->delete();

            $existingPositions = $event->eventPositions()->pluck('callsign')->toArray();
            $newPositions = array_diff($validated['positions'], $existingPositions);

            foreach ($newPositions as $callsign) {
                $parts = explode('_', $callsign);

                $airport = Airport::tryFrom($parts[0]);
                $positionType = ControllerPosition::tryFrom($parts[count($parts) - 1]);

                if ($airport && $positionType) {
                    $event->eventPositions()->create([
                        'callsign' => $callsign,
                        'airport' => $airport->value,
                        'position' => $positionType->value,
                    ]);
                }
            }
            unset($validated['positions']);
        }

        $event->update($validated);

        $dossierService->create(
            auth()->user(),
            "updated the event *{$event->name}*."
        );

        return redirect()->route('admin.events.index');
    }

    public function destroy(Event $event, DossierService $dossierService): RedirectResponse
    {
        $dossierService->create(
            auth()->user(),
            "deleted the event *{$event->name}*."
        );

        $event->eventPositions()->delete();
        $event->eventRegistrations()->delete();
        $event->delete();

        return redirect()->route('admin.events.index');
    }
}
