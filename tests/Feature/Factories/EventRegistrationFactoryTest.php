<?php

use App\Models\Event;
use App\Models\EventPosition;
use App\Models\EventRegistration;
use App\Models\User;

it('creates an event registration with basic attributes', function () {
    $registration = EventRegistration::factory()->create();

    expect($registration)
        ->event_id->toBeInt()
        ->user_id->toBeInt()
        ->requested_positions->toBeArray()
        ->and(count($registration->requested_positions))->toBeBetween(1, 3);
});

it('creates an event registration with valid relationships', function () {
    $registration = EventRegistration::factory()->create();

    expect($registration)
        ->event->toBeInstanceOf(Event::class)
        ->user->toBeInstanceOf(User::class)
        ->user->member->toBeTrue()
        ->requestedPositions->toBeCollection()
        ->requestedPositions->each->toBeInstanceOf(EventPosition::class);
});

it('creates an event registration with valid requested positions', function () {
    $registration = EventRegistration::factory()->create();
    $event = $registration->event;

    // All requested positions should belong to the event
    $eventPositionIds = $event->eventPositions->pluck('id');
    $requestedPositionIds = collect($registration->requested_positions);

    expect($requestedPositionIds->diff($eventPositionIds))->toBeEmpty();
});

it('creates an event registration with unique requested positions', function () {
    $registration = EventRegistration::factory()->create();
    $requestedPositions = collect($registration->requested_positions);

    expect($requestedPositions->unique())->toHaveCount($requestedPositions->count());
});
