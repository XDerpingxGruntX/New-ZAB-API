<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Users/Index', [
            'homeControllers' => User::whereVisitor(false)->orderBy('last_name')->with([
                'absences', 'certifications',
            ])->get(),
            'visitingControllers' => User::whereVisitor(true)->orderBy('last_name')->with([
                'absences', 'certifications',
            ])->get(),
        ]);
    }

    public function show(User $user): Response
    {
        return Inertia::render('Users/Show', [
            'user' => $user->load(['absences', 'certifications']),
            'metrics' => $user->getControllerMetrics(),
        ]);
    }
}
