<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Events/Index', [
            'events' => fn () => Event::query()->whereFuture('ends_at')->latest()->get(),
            'pastEvents' => fn () => Event::query()->wherePast('ends_at')->latest()->get(),
        ]);
    }

    public function show(Event $event): Response
    {
        $event->load(['eventPositions.user', 'eventRegistrations.user', 'eventRegistrations.requestedPositions']);

        return Inertia::render('Events/Show', [
            'event' => $event,
        ]);
    }
}
