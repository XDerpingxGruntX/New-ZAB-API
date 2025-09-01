<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAbsenceRequest;
use App\Mail\AbsenceConfirmation;
use App\Models\Absence;
use App\Models\User;
use App\Notifications\AbsenceConfirmationNotification;
use App\Services\DossierService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class AbsenceController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Absences/Index', [
            'absences' => fn () => Absence::with('user')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Absences/Create', [
            'users' => fn () => User::all(),
        ]);
    }

    public function store(StoreAbsenceRequest $request, DossierService $dossierService): RedirectResponse
    {
        $absence = Absence::create(['starts_at' => now(), ...$request->validated()]);

        // Send confirmation email and notification to the user
        Mail::to($absence->user->email)->send(new AbsenceConfirmation($absence));
        $absence->user->notify(new AbsenceConfirmationNotification($absence));

        $dossierService->create(
            auth()->user(),
            "added a leave of absence for %a: {$absence->reason}",
            $absence->user
        );

        return redirect()->route('admin.absences.index');
    }

    public function destroy(Absence $absence, DossierService $dossierService): RedirectResponse
    {
        $dossierService->create(
            auth()->user(),
            'deleted the leave of absence for %a.',
            $absence->user
        );

        $absence->delete();

        return redirect()->route('admin.absences.index');
    }
}
