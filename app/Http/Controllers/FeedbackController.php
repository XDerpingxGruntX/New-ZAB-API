<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ControllerPosition;
use App\Enums\FeedbackRating;
use App\Http\Requests\StoreFeedbackRequest;
use App\Models\Feedback;
use App\Models\User;
use App\Services\DossierService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FeedbackController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Feedback/Create', [
            'controllers' => User::query()
                ->where('member', true)
                ->where('visitor', false)
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->select(['id', 'first_name', 'last_name', 'cid'])
                ->get(),
            'positions' => ControllerPosition::cases(),
            'ratings' => FeedbackRating::cases(),
        ]);
    }

    public function store(StoreFeedbackRequest $request, DossierService $dossierService): RedirectResponse
    {
        $feedback = Feedback::create([
            'critic_id' => auth()->id(),
            'controller_id' => $request->validated('controller_id'),
            'position' => $request->validated('position'),
            'rating' => $request->validated('rating'),
            'comment' => $request->validated('comment'),
            'ip_address' => $request->ip(),
            'anonymous' => $request->boolean('anonymous'),
        ]);

        $dossierService->create(
            auth()->user(),
            'submitted feedback about %a.',
            $feedback->controller_id
        );

        return redirect()->route('feedback.create')->with('success', 'Feedback submitted successfully!');
    }
}
