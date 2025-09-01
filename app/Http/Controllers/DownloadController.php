<?php

namespace App\Http\Controllers;

use App\Enums\DownloadCategory;
use App\Models\Download;
use Inertia\Inertia;
use Inertia\Response;

class DownloadController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Downloads/Index', [
            'veramDownloads' => fn () => Download::where('category', DownloadCategory::VERAM)->latest()->get(),
            'vstarsDownloads' => fn () => Download::where('category', DownloadCategory::VSTARS)->latest()->get(),
            'vatisDownloads' => fn () => Download::where('category', DownloadCategory::VATIS)->latest()->get(),
            'miscellaneousDownloads' => fn () => Download::where('category', DownloadCategory::MISCELLANEOUS)->latest()->get(),
        ]);
    }
}
