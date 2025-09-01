<?php

use App\Enums\Role;
use App\Models\User;

it('creates a user with basic attributes', function () {
    $user = User::factory()->create();

    expect($user)
        ->cid->toBeInt()->toBeGreaterThan(800000)->toBeLessThan(2000000)
        ->first_name->toBeString()->not->toBeEmpty()
        ->last_name->toBeString()->not->toBeEmpty()
        ->email->toBeString()->toContain('@')
        ->operating_initials->toBeString()
        ->roles->when($user->roles !== null, function ($roles) {
            expect($roles)->toHaveCount(1)
                ->and($roles->first())->toBeInstanceOf(Role::class)
                ->and(collect([
                    Role::ATM->value,
                    Role::DATM->value,
                    Role::TA->value,
                    Role::EC->value,
                    Role::WM->value,
                    Role::FE->value,
                ]))->toContain($roles->first()->value);
        })
        ->and($user->broadcast_opt_in)->toBeBool()
        ->and($user->member)->toBeBool()
        ->and($user->visitor)->toBeBool();
});

it('creates a user with unique operating initials', function () {
    $users = User::factory(5)->create();
    $initials = $users->pluck('operating_initials');

    expect($initials->unique())->toHaveCount(5);
});

it('creates a manager with appropriate roles', function () {
    $user = User::factory()->manager()->create();
    $role = collect($user->roles)->first();

    expect($role)
        ->toBeInstanceOf(Role::class)
        ->and(collect([Role::ATM->value, Role::DATM->value]))
        ->toContain($role->value);
});

it('creates a staff member with appropriate roles', function () {
    $user = User::factory()->staff()->create();
    $role = collect($user->roles)->first();

    expect($role)
        ->toBeInstanceOf(Role::class)
        ->and(collect([
            Role::ATM->value,
            Role::DATM->value,
            Role::TA->value,
            Role::EC->value,
            Role::WM->value,
            Role::FE->value,
        ]))->toContain($role->value);
});

it('creates a senior staff member with appropriate roles', function () {
    $user = User::factory()->senior()->create();
    $role = collect($user->roles)->first();

    expect($role)
        ->toBeInstanceOf(Role::class)
        ->and(collect([Role::ATM->value, Role::DATM->value, Role::TA->value]))
        ->toContain($role->value);
});

it('creates an instructor with appropriate roles', function () {
    $user = User::factory()->instructor()->create();
    $role = collect($user->roles)->first();

    expect($role)
        ->toBeInstanceOf(Role::class)
        ->and(collect([
            Role::ATM->value,
            Role::DATM->value,
            Role::TA->value,
            Role::INS->value,
            Role::MTR->value,
        ]))->toContain($role->value);
});

it('creates a member', function () {
    $user = User::factory()->member()->create();

    expect($user)
        ->member->toBeTrue()
        ->visitor->toBeFalse();
});

it('creates a visitor', function () {
    $user = User::factory()->visitor()->create();

    expect($user)
        ->member->toBeFalse()
        ->visitor->toBeTrue();
});
