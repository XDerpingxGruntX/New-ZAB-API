<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Article;
use App\Services\DossierService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Articles/Index', [
            'articles' => fn () => Article::with('user')->latest()->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Articles/Create');
    }

    public function store(StoreArticleRequest $request, DossierService $dossierService): RedirectResponse
    {
        $article = auth()->user()->articles()->create($request->validated());

        $dossierService->create(
            auth()->user(),
            "created the news item *{$article->title}*."
        );

        return redirect()->route('admin.articles.edit', $article)
            ->with('success', 'Article created successfully.');
    }

    public function edit(Article $article): Response
    {
        return Inertia::render('Admin/Articles/Edit', [
            'article' => $article,
        ]);
    }

    public function update(
        UpdateArticleRequest $request,
        Article $article,
        DossierService $dossierService
    ): RedirectResponse {
        $article->update($request->validated());

        $dossierService->create(
            auth()->user(),
            "updated the news item *{$article->title}*."
        );

        return redirect()->route('admin.articles.edit', $article)
            ->with('success', 'Article updated successfully.');
    }

    public function destroy(Article $article, DossierService $dossierService): RedirectResponse
    {
        $dossierService->create(
            auth()->user(),
            "deleted the news item *{$article->title}*."
        );

        $article->delete();

        return redirect()->route('admin.articles.index')
            ->with('success', 'Article deleted successfully.');
    }
}
