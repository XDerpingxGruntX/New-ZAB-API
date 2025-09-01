<?php

use App\Enums\ControllerPosition;
use App\Enums\FeedbackRating;
use App\Models\Feedback;
use App\Models\User;

it('creates feedback with basic attributes', function () {
    $feedback = Feedback::factory()->create();

    expect($feedback)
        ->critic_id->toBeInt()
        ->controller_id->toBeInt()
        ->position->toBeInstanceOf(ControllerPosition::class)
        ->rating->toBeInstanceOf(FeedbackRating::class)
        ->comment->toBeString()->not->toBeEmpty()
        ->ip_address->toBeString()->toMatch('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/')
        ->anonymous->toBeBool()
        ->approved_at->when(
            $feedback->approved_at !== null,
            fn ($approved_at) => $approved_at->toBeInstanceOf(DateTime::class)
        );
});

it('creates feedback with valid relationships', function () {
    $feedback = Feedback::factory()->create();

    expect($feedback)
        ->critic->toBeInstanceOf(User::class)
        ->controller->toBeInstanceOf(User::class)
        ->controller->member->toBeTrue();
});

it('creates approved feedback', function () {
    $feedback = Feedback::factory()->approved()->create();

    expect($feedback)
        ->approved_at->toBeInstanceOf(DateTime::class)
        ->approved_at->toBeLessThanOrEqual(now());
});

it('creates pending feedback', function () {
    $feedback = Feedback::factory()->pending()->create();

    expect($feedback)
        ->approved_at->toBeNull();
});

it('creates anonymous feedback', function () {
    $feedback = Feedback::factory()->anonymous()->create();

    expect($feedback)
        ->anonymous->toBeTrue();
});
