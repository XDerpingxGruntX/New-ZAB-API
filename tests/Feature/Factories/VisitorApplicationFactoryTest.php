<?php

use App\Enums\ControllerRating;
use App\Models\User;
use App\Models\VisitorApplication;

it('creates a visitor application with basic attributes', function () {
    $application = VisitorApplication::factory()->create();

    expect($application)
        ->user_id->toBeInt()
        ->first_name->toBeString()->not->toBeEmpty()
        ->last_name->toBeString()->not->toBeEmpty()
        ->email->toBeString()->toContain('@')
        ->rating->toBeInstanceOf(ControllerRating::class)
        ->home_facility->toBeString()->toMatch('/^K[A-Z]{3}$/')
        ->justification->toBeString()->not->toBeEmpty()
        ->accepted_at->when(
            $application->accepted_at !== null,
            fn ($accepted_at) => $accepted_at->toBeInstanceOf(DateTime::class)
        );
});

it('creates a visitor application with valid relationships', function () {
    $application = VisitorApplication::factory()->create();

    expect($application)
        ->user->toBeInstanceOf(User::class)
        ->user->visitor->toBeTrue()
        ->user->member->toBeFalse();
});

it('creates an accepted visitor application', function () {
    $application = VisitorApplication::factory()->accepted()->create();

    expect($application)
        ->accepted_at->toBeInstanceOf(DateTime::class)
        ->accepted_at->toBeLessThanOrEqual(now());
});

it('creates a pending visitor application', function () {
    $application = VisitorApplication::factory()->pending()->create();

    expect($application)
        ->accepted_at->toBeNull();
});

it('creates a visitor application with matching user details', function () {
    $application = VisitorApplication::factory()->create();
    $user = $application->user;

    expect($application)
        ->first_name->toBe($user->first_name)
        ->last_name->toBe($user->last_name)
        ->email->toBe($user->email)
        ->rating->toBe($user->rating)
        ->home_facility->toBe($user->home_facility);
});
