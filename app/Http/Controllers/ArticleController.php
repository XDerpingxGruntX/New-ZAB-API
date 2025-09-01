<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Article;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Articles/Index', [
            'articles' => Article::with('user')->latest()->get(),
        ]);
    }

    public function show(Article $article): Response
    {
        return Inertia::render('Articles/Show', [
            'article' => $article->load('user'),
        ]);
    }
}
