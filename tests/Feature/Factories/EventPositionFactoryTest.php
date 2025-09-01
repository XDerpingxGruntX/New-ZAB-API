<?php

use App\Enums\ControllerPosition;
use App\Models\Event;
use App\Models\EventPosition;
use App\Models\User;

it('creates an event position with basic attributes', function () {
    $position = EventPosition::factory()->create();

    expect($position)
        ->event_id->toBeInt()
        ->code->toBeString()->not->toBeEmpty()
        ->type->toBeString()->not->toBeEmpty()
        ->assigned->toBeBool()
        ->user_id->when($position->assigned, fn ($id) => $id->toBeInt())
        ->user_id->when(! $position->assigned, fn ($id) => $id->toBeNull());
});

it('creates an event position with valid relationships', function () {
    $position = EventPosition::factory()->create();

    expect($position)
        ->event->toBeInstanceOf(Event::class)
        ->user->when($position->assigned, fn ($user) => $user->toBeInstanceOf(User::class))
        ->user->when(! $position->assigned, fn ($user) => $user->toBeNull());
});

it('creates an assigned event position', function () {
    $position = EventPosition::factory()->assigned()->create();

    expect($position)
        ->assigned->toBeTrue()
        ->user_id->toBeInt()
        ->user->toBeInstanceOf(User::class)
        ->user->member->toBeTrue();
});

it('creates an unassigned event position', function () {
    $position = EventPosition::factory()->unassigned()->create();

    expect($position)
        ->assigned->toBeFalse()
        ->user_id->toBeNull()
        ->user->toBeNull();
});

it('creates an event position with valid controller position', function () {
    $position = EventPosition::factory()->create();
    $validPositions = collect(ControllerPosition::cases())->map->value;

    expect($position->code)
        ->toBeIn($validPositions);
});
