<?php

use App\Enums\ControllerPosition;
use App\Models\Event;
use App\Models\User;

it('creates an event with basic attributes', function () {
    $event = Event::factory()->create();

    expect($event)
        ->user_id->toBeInt()
        ->name->toBeString()->not->toBeEmpty()
        ->slug->toBeString()->not->toBeEmpty()
        ->description->toBeString()->not->toBeEmpty()
        ->starts_at->toBeInstanceOf(DateTime::class)
        ->ends_at->toBeInstanceOf(DateTime::class)
        ->registration_opens_at->toBeInstanceOf(DateTime::class)
        ->registration_closes_at->toBeInstanceOf(DateTime::class)
        ->emails_sent_at->when(
            $event->emails_sent_at !== null,
            fn ($emails_sent_at) => $emails_sent_at->toBeInstanceOf(DateTime::class)
        );
});

it('creates an event with valid relationships', function () {
    $event = Event::factory()->create();

    expect($event)
        ->user->toBeInstanceOf(User::class)
        ->user->isStaff()->toBeTrue()
        ->eventPositions->toBeCollection()
        ->eventPositions->toHaveCount(count(ControllerPosition::cases()));
});

it('creates a past event', function () {
    $event = Event::factory()->past()->create();
    $now = now();

    expect($event)
        ->starts_at->toBeLessThan($now)
        ->ends_at->toBeLessThan($now)
        ->registration_opens_at->toBeLessThan($now)
        ->registration_closes_at->toBeLessThan($now);
});

it('creates an event in progress', function () {
    $event = Event::factory()->inProgress()->create();
    $now = now();

    expect($event)
        ->starts_at->toBeLessThan($now)
        ->ends_at->toBeGreaterThan($now)
        ->registration_opens_at->toBeLessThan($now)
        ->registration_closes_at->toBeLessThan($now);
});

it('creates event positions for all controller positions', function () {
    $event = Event::factory()->create();
    $positions = $event->eventPositions;
    $positionCodes = $positions->pluck('code')->toArray();

    foreach (ControllerPosition::cases() as $position) {
        expect($positionCodes)->toContain($position->value);
    }
});
