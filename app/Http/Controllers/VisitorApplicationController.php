<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreVisitorApplicationRequest;
use Inertia\Inertia;
use Inertia\Response;

class VisitorApplicationController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('VisitorApplications/Create', [
            'pendingApplications' => auth()->user()->visitorApplications()->whereNull('accepted_at')->get(),
        ]);
    }

    public function store(StoreVisitorApplicationRequest $request)
    {
        $validated = $request->validated();

        auth()->user()->visitorApplications()->create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'rating' => auth()->user()->rating,
            'home_facility' => $validated['home_facility'],
            'justification' => $validated['justification'],
        ]);

        return redirect()->route('visitor-applications.create')->with('success',
            __('Your application has been submitted.'));
    }
}
