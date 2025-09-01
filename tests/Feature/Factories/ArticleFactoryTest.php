<?php

use App\Models\Article;
use App\Models\User;

it('creates an article with basic attributes', function () {
    $article = Article::factory()->create();

    expect($article)
        ->user_id->toBeInt()
        ->title->toBeString()->not->toBeEmpty()
        ->slug->toBeString()->not->toBeEmpty()
        ->content->toBeString()->not->toBeEmpty();
});

it('creates an article with valid relationships', function () {
    $article = Article::factory()->create();

    expect($article)
        ->user->toBeInstanceOf(User::class)
        ->user->isStaff()->toBeTrue();
});

it('creates an article with auto-generated slug', function () {
    $article = Article::factory()->state([
        'title' => 'Test Article Title',
        'slug' => null,
    ])->create();

    expect($article->slug)->toBe('test-article-title');
});
