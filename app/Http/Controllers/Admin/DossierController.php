<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dossier;
use Inertia\Inertia;
use Inertia\Response;

class DossierController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Dossiers/Index', [
            'dossiers' => fn () => Dossier::with(['user', 'affectedUser'])
                ->latest()
                ->get(),
        ]);
    }
}
