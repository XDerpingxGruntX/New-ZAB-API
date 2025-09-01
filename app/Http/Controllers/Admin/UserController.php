<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Certification;
use App\Models\User;
use App\Services\DossierService;
use App\Services\VATUSA;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Users/Index', [
            'users' => fn () => User::query()->orderBy('first_name')->get(),
        ]);
    }

    public function edit(User $user): Response
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'availableCertifications' => Certification::query()->orderBy('code')->get(),
            'usedOperatingInitials' => User::pluck('operating_initials')->unique()->toArray(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user, DossierService $dossierService): RedirectResponse
    {
        $data = $request->validated();

        $user->update($data);

        if (isset($data['certifications'])) {
            $certifications = Certification::query()->whereIn('code', $data['certifications'])->get();
            $user->certifications()->sync($certifications);
        }

        $dossierService->create(
            auth()->user(),
            'updated %a.',
            $user
        );

        return redirect()->route('admin.users.edit', $user)
            ->with('success', 'User updated successfully.');
    }

    public function destroy(
        User $user,
        Request $request,
        VATUSA $vatusa,
        DossierService $dossierService
    ): RedirectResponse {
        // Validate request to make sure reason is provided
        $validator = Validator::make($request->only('reason'), [
            'reason' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.users.index')->withErrors($validator);
        }

        if (auth()->id() === $user->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $reason = $request->input('reason');

        try {
            if ($user->visitor) {
                $vatusa->removeVisitor('ZAB', (string) $user->cid, $reason);
                Log::info('Successfully removed visitor from VATUSA', [
                    'cid' => $user->cid,
                    'name' => $user->full_name,
                    'reason' => $reason,
                ]);
            } else {
                $vatusa->removeController('ZAB', (string) $user->cid, $reason);
                Log::info('Successfully removed controller from VATUSA', [
                    'cid' => $user->cid,
                    'name' => $user->full_name,
                    'reason' => $reason,
                ]);
            }
        } catch (ConnectionException $e) {
            Log::error('Failed to remove user from VATUSA', [
                'cid' => $user->cid,
                'visitor' => $user->visitor,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('admin.users.index')
                ->with('error', 'Failed to remove user from VATUSA. Please try again or contact the web team.');
        }

        $dossierService->create(
            auth()->user(),
            "removed %a from the roster: {$reason}",
            $user
        );

        // Update user locally
        $user->update([
            'member' => false,
            'roles' => null,
        ]);

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User removed successfully.');
    }
}
