<?php

use App\Enums\DocumentCategory;
use App\Models\Document;
use App\Models\User;

it('creates a document with basic attributes', function () {
    $document = Document::factory()->create();

    expect($document)
        ->user_id->toBeInt()
        ->name->toBeString()->not->toBeEmpty()
        ->slug->toBeString()->not->toBeEmpty()
        ->category->toBeInstanceOf(DocumentCategory::class)
        ->description->when(
            $document->description !== null,
            fn ($description) => $description->toBeString()
        )
        ->content->when(
            $document->content !== null,
            fn ($content) => $content->toBeString()
        )
        ->file_path->when(
            $document->file_path !== null,
            fn ($file_path) => $file_path->toBeString()
        );
});

it('creates a document with valid relationships', function () {
    $document = Document::factory()->create();

    expect($document)
        ->user->toBeInstanceOf(User::class)
        ->user->isStaff()->toBeTrue();
});

it('creates a document with content', function () {
    $document = Document::factory()->withContent()->create();

    expect($document)
        ->content->toBeString()->not->toBeEmpty()
        ->file_path->toBeNull();
});

it('creates a document with file', function () {
    $document = Document::factory()->withFile()->create();

    expect($document)
        ->content->toBeNull()
        ->file_path->toBeString()
        ->file_path->toStartWith('documents/')
        ->file_path->toMatch('/\.(pdf|doc|docx|txt)$/');
});
