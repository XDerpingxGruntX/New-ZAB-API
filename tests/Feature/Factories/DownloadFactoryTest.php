<?php

use App\Enums\DownloadCategory;
use App\Models\Download;
use App\Models\User;

it('creates a download with basic attributes', function () {
    $download = Download::factory()->create();

    expect($download)
        ->user_id->toBeInt()
        ->name->toBeString()->not->toBeEmpty()
        ->category->toBeInstanceOf(DownloadCategory::class)
        ->description->when(
            $download->description !== null,
            fn ($description) => $description->toBeString()
        )
        ->file_path->toBeString()
        ->file_path->toStartWith('downloads/')
        ->file_path->toMatch('/\.(zip|exe|msi|7z|pdf)$/');
});

it('creates a download with valid relationships', function () {
    $download = Download::factory()->create();

    expect($download)
        ->user->toBeInstanceOf(User::class)
        ->user->isStaff()->toBeTrue();
});

it('creates a miscellaneous download', function () {
    $download = Download::factory()->miscellaneous()->create();

    expect($download)
        ->category->toBe(DownloadCategory::MISCELLANEOUS)
        ->file_path->toEndWith('.zip');
});

it('creates a vATIS download', function () {
    $download = Download::factory()->vatis()->create();

    expect($download)
        ->category->toBe(DownloadCategory::VATIS)
        ->file_path->toEndWith('.json');
});

it('creates a vSTARS download', function () {
    $download = Download::factory()->vstars()->create();

    expect($download)
        ->category->toBe(DownloadCategory::VSTARS)
        ->file_path->toEndWith('.gz');
});

it('creates a vERAM download', function () {
    $download = Download::factory()->veram()->create();

    expect($download)
        ->category->toBe(DownloadCategory::VERAM)
        ->file_path->toEndWith('.gz');
});
