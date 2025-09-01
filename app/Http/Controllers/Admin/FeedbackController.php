<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\FeedbackApproved;
use App\Models\Feedback;
use App\Notifications\FeedbackApprovedNotification;
use App\Services\DossierService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class FeedbackController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Feedbacks/Index', [
            'unapprovedFeedbacks' => fn () => Feedback::query()->whereNull('approved_at')->orderBy('created_at',
                'desc')->get(),
            'allFeedbacks' => fn (
            ) => Feedback::withTrashed()->whereNotNull('approved_at')->orWhereNotNull('deleted_at')->orderBy('created_at',
                'desc')->get(),
        ]);
    }

    public function accept(Feedback $feedback, DossierService $dossierService): RedirectResponse
    {
        $feedback->update([
            'approved_at' => now(),
        ]);

        // Send approval email and notification to the controller who received the feedback
        Mail::to($feedback->controller->email)->send(new FeedbackApproved($feedback));
        $feedback->controller->notify(new FeedbackApprovedNotification($feedback));

        $dossierService->create(
            auth()->user(),
            'approved feedback for %a.',
            $feedback->controller_id
        );

        return redirect()->route('admin.feedbacks.index', $feedback);
    }

    public function reject(Feedback $feedback, DossierService $dossierService): RedirectResponse
    {
        $dossierService->create(
            auth()->user(),
            'rejected feedback for %a.',
            $feedback->controller_id
        );

        $feedback->delete();

        return redirect()->route('admin.feedbacks.index', $feedback);
    }
}
