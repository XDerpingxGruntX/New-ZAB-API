<?php

use App\Enums\Airport;
use App\Enums\ControllerRating;
use App\Models\ControllerSession;
use App\Models\User;

it('creates a controller session with basic attributes', function () {
    $session = ControllerSession::factory()->create();

    expect($session)
        ->user_id->toBeInt()
        ->cid->toBeInt()->toBeGreaterThan(800000)->toBeLessThan(2000000)
        ->rating->toBeInstanceOf(ControllerRating::class)
        ->position->toBeString()
        ->frequency->toBeFloat()->toBeGreaterThan(118.0)->toBeLessThan(137.0)
        ->connected_at->toBeInstanceOf(DateTime::class)
        ->disconnected_at->when(
            $session->disconnected_at !== null,
            fn ($disconnected_at) => $disconnected_at->toBeInstanceOf(DateTime::class)
        );
});

it('creates a controller session with valid relationships', function () {
    $session = ControllerSession::factory()->create();

    expect($session)
        ->user->toBeInstanceOf(User::class)
        ->user->member->toBeTrue();
});

it('creates a controller session with valid position format', function () {
    $session = ControllerSession::factory()->create();
    $parts = explode('_', $session->position);

    expect($parts)
        ->toHaveCount(2)
        ->and($parts[0])->toBeIn(collect(Airport::cases())->map->value)
        ->and($parts[1])->toMatch('/^[A-Z]+[123]?$/');
});

it('creates an active controller session', function () {
    $session = ControllerSession::factory()->active()->create();
    $now = now();

    expect($session)
        ->connected_at->toBeLessThan($now)
        ->disconnected_at->toBeNull();
});

it('creates a completed controller session', function () {
    $session = ControllerSession::factory()->completed()->create();

    expect($session)
        ->connected_at->toBeInstanceOf(DateTime::class)
        ->disconnected_at->toBeInstanceOf(DateTime::class)
        ->disconnected_at->toBeGreaterThan($session->connected_at);
});
