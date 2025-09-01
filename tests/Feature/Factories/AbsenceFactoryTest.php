<?php

use App\Models\Absence;
use App\Models\User;

it('creates an absence with basic attributes', function () {
    $absence = Absence::factory()->create();

    expect($absence)
        ->user_id->toBeInt()
        ->justification->when(
            $absence->justification !== null,
            fn ($justification) => $justification->toBeString()
        )
        ->starts_at->toBeInstanceOf(DateTime::class)
        ->expires_at->when(
            $absence->expires_at !== null,
            fn ($expires_at) => $expires_at->toBeInstanceOf(DateTime::class)
        );
});

it('creates an absence with valid relationships', function () {
    $absence = Absence::factory()->create();

    expect($absence)
        ->user->toBeInstanceOf(User::class)
        ->user->member->toBeTrue();
});

it('creates an active absence', function () {
    $absence = Absence::factory()->active()->create();
    $now = now();

    expect($absence)
        ->starts_at->toBeLessThanOrEqual($now)
        ->expires_at->toBeGreaterThan($now);
});

it('creates an expired absence', function () {
    $absence = Absence::factory()->expired()->create();
    $now = now();

    expect($absence)
        ->starts_at->toBeLessThan($now)
        ->expires_at->toBeLessThan($now);
});

it('creates a permanent absence', function () {
    $absence = Absence::factory()->permanent()->create();

    expect($absence)
        ->expires_at->toBeNull();
});
