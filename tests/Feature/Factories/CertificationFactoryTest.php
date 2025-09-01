<?php

use App\Enums\CertificationClass;
use App\Models\Certification;

it('creates a certification with basic attributes', function () {
    $certification = Certification::factory()->create();

    expect($certification)
        ->code->toBeString()->not->toBeEmpty()
        ->name->toBeString()->not->toBeEmpty()
        ->class->toBeInstanceOf(CertificationClass::class)
        ->facility->when(
            $certification->facility !== null,
            fn ($facility) => $facility->toBeString()
        );
});

it('creates a certification with valid code and name pairs', function () {
    $certification = Certification::factory()->create();
    $code = $certification->code;
    $name = $certification->name;

    match ($code) {
        'GND' => expect($name)->toBe('Ground'),
        'TWR' => expect($name)->toBe('Tower'),
        'APP' => expect($name)->toBe('Approach'),
        'CTR' => expect($name)->toBe('Center'),
        'SAT' => expect($name)->toBe('Satellite'),
        'TMU' => expect($name)->toBe('Traffic Management Unit'),
    };
});

it('creates a certification with valid class and facility combinations', function () {
    $certification = Certification::factory()->create();

    if ($certification->code === 'SAT' || $certification->code === 'TMU') {
        expect($certification->facility)->toBeNull();
    } else {
        expect($certification->facility)->toBe('KZAB');
    }

    match ($certification->code) {
        'GND', 'TWR', 'SAT' => expect($certification->class)->toBe(CertificationClass::TIER_ONE),
        'APP', 'CTR', 'TMU' => expect($certification->class)->toBe(CertificationClass::TIER_TWO),
    };
});
