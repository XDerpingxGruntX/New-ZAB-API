<?php

use App\Enums\Airport;
use App\Models\PilotReport;

it('creates a pilot report with basic attributes', function () {
    $report = PilotReport::factory()->create();

    expect($report)
        ->external_id->toBeInt()->toBeGreaterThan(0)->toBeLessThan(1000000)
        ->location->toBeString()->not->toBeEmpty()
        ->aircraft->toBeString()->not->toBeEmpty()
        ->altitude->toBeString()->toMatch('/^\d+ft$/')
        ->temperature->toBeInt()->toBeGreaterThan(-31)->toBeLessThan(41)
        ->urgent->toBeBool()
        ->manual->toBeBool()
        ->raw->toBeString()->not->toBeEmpty()
        ->reported_at->toBeInstanceOf(DateTime::class);
});

it('creates a pilot report with valid location', function () {
    $report = PilotReport::factory()->create();
    $airports = collect(Airport::cases())->map->value;

    // Location can be either an airport code or a relative position to an airport
    if (! str_contains($report->location, ' ')) {
        expect($report->location)->toBeIn($airports);
    } else {
        $parts = explode(' ', $report->location);
        expect($parts)
            ->toHaveCount(2)
            ->and($parts[0])->toBeString()
            ->and($parts[1])->toBeIn($airports);
    }
});

it('creates a pilot report with valid aircraft type', function () {
    $report = PilotReport::factory()->create();
    $validAircraft = [
        'A320', 'B738', 'B739', 'CRJ7', 'CRJ9', 'E170', 'E175',
        'C172', 'PA28', 'BE58', 'SR22', 'C152', 'DA40', 'DA42',
    ];

    expect($report->aircraft)->toBeIn($validAircraft);
});

it('creates an urgent pilot report', function () {
    $report = PilotReport::factory()->urgent()->create();

    expect($report)
        ->urgent->toBeTrue()
        ->raw->toContain('UA');
});

it('creates a manual pilot report', function () {
    $report = PilotReport::factory()->manual()->create();

    expect($report)
        ->manual->toBeTrue();
});

it('creates a pilot report with valid weather conditions', function () {
    $report = PilotReport::factory()->create();

    if ($report->sky) {
        expect($report->sky)->toMatch('/^(SKC|CLR|FEW\d{3}|SCT\d{3}|BKN\d{3}|OVC\d{3}|((FEW|SCT|BKN|OVC)\d{3}\s?)+)$/');
    }

    if ($report->turbulence) {
        expect($report->turbulence)->toBeIn([
            'NONE', 'LIGHT', 'MODERATE', 'SEVERE',
            'LIGHT CHOP', 'MODERATE CHOP',
        ]);
    }

    if ($report->icing) {
        expect($report->icing)->toBeIn([
            'NONE', 'TRACE', 'LIGHT', 'MODERATE', 'SEVERE',
            'LIGHT RIME', 'MODERATE CLEAR',
        ]);
    }

    if ($report->visibility) {
        expect($report->visibility)->toBeString();
    }

    if ($report->wind) {
        expect($report->wind)->toMatch('/^\d{1,3}@\d{1,2}$/');
    }
});
