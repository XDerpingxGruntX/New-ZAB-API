<?php

use App\Enums\Airport;
use App\Models\FlightSession;
use App\Models\User;

it('creates a flight session with basic attributes', function () {
    $session = FlightSession::factory()->create();

    expect($session)
        ->user_id->toBeInt()
        ->cid->toBeInt()->toBeGreaterThan(800000)->toBeLessThan(2000000)
        ->callsign->toBeString()->not->toBeEmpty()
        ->aircraft->toBeString()->not->toBeEmpty()
        ->departure_airport->toBeString()->toBeIn(collect(Airport::cases())->map->value)
        ->arrival_airport->toBeString()->toBeIn(collect(Airport::cases())->map->value)
        ->latitude->toBeFloat()->toBeGreaterThan(41.0)->toBeLessThan(45.0)
        ->longitude->toBeFloat()->toBeGreaterThan(-93.0)->toBeLessThan(-87.0)
        ->heading->toBeInt()->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(359)
        ->altitude->toBeInt()->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(45000)
        ->speed->toBeInt()->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(500);
});

it('creates a flight session with valid relationships', function () {
    $session = FlightSession::factory()->create();

    expect($session)
        ->user->toBeInstanceOf(User::class);
});

it('creates a flight session with different departure and arrival airports', function () {
    $session = FlightSession::factory()->create();

    expect($session->departure_airport)
        ->not->toBe($session->arrival_airport);
});

it('creates a commercial flight session', function () {
    $session = FlightSession::factory()->commercial()->create();
    $commercialAirlines = ['AAL', 'DAL', 'UAL', 'SWA'];
    $commercialAircraft = ['A320', 'B738', 'B739', 'CRJ7', 'CRJ9', 'E170', 'E175'];

    expect($session)
        ->callsign->toMatch('/^(' . implode('|', $commercialAirlines) . ')\d{1,4}$/')
        ->aircraft->toBeIn($commercialAircraft)
        ->planned_altitude->toBeGreaterThanOrEqual(28000)->toBeLessThanOrEqual(45000);
});

it('creates a general aviation flight session', function () {
    $session = FlightSession::factory()->generalAviation()->create();
    $gaAircraft = ['C172', 'PA28', 'BE58', 'SR22', 'C152', 'DA40', 'DA42'];

    expect($session)
        ->callsign->toMatch('/^N[1-9][0-9]{2}[A-Z]{2}$/')
        ->aircraft->toBeIn($gaAircraft)
        ->planned_altitude->toBeGreaterThanOrEqual(3000)->toBeLessThanOrEqual(12000);
});
