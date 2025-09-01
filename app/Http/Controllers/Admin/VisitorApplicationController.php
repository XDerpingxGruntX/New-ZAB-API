<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\VisitorApplicationApproved;
use App\Mail\VisitorApplicationRejected;
use App\Models\VisitorApplication;
use App\Notifications\VisitorApplicationStatusNotification;
use App\Services\DossierService;
use App\Services\VATUSA;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class VisitorApplicationController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/VisitorApplications/Index', [
            'visitorApplications' => fn () => VisitorApplication::with('user')->get(),
        ]);
    }

    public function accept(
        VisitorApplication $visitorApplication,
        VATUSA $vatusa,
        DossierService $dossierService
    ): RedirectResponse {
        $visitorApplication->update([
            'accepted_at' => now(),
        ]);

        $visitorApplication->user()->update([
            'member' => true,
            'visitor' => true,
        ]);

        // Send approval email and notification to the user
        Mail::to($visitorApplication->user->email)->send(new VisitorApplicationApproved($visitorApplication));
        $visitorApplication->user->notify(new VisitorApplicationStatusNotification($visitorApplication, 'approved'));

        $dossierService->create(
            auth()->user(),
            'approved the visiting application for %a.',
            $visitorApplication->user
        );

        try {
            $vatusa->addVisitor('ZAB', (string) $visitorApplication->user->cid);
            Log::info('Successfully added visitor to VATUSA', [
                'cid' => $visitorApplication->user->cid,
                'name' => $visitorApplication->user->full_name,
            ]);
        } catch (ConnectionException $e) {
            Log::error('Failed to add visitor to VATUSA', [
                'cid' => $visitorApplication->user->cid,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('admin.visitor-applications.index', $visitorApplication)
                ->with('warning',
                    'Visitor application accepted locally, but failed to sync with VATUSA. Please try again or contact the web team.');
        }

        return redirect()->route('admin.visitor-applications.index', $visitorApplication)
            ->with('success', 'Visitor application accepted successfully.');
    }

    public function reject(VisitorApplication $visitorApplication, DossierService $dossierService): RedirectResponse
    {
        // Send rejection email and notification to the user before deleting
        Mail::to($visitorApplication->user->email)->send(new VisitorApplicationRejected($visitorApplication));
        $visitorApplication->user->notify(new VisitorApplicationStatusNotification($visitorApplication, 'rejected'));

        $dossierService->create(
            auth()->user(),
            'rejected the visiting application for %a.',
            $visitorApplication->user
        );

        $visitorApplication->delete();

        return redirect()->route('admin.visitor-applications.index', $visitorApplication)
            ->with('success', 'Visitor application rejected successfully.');
    }
}
