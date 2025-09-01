<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Inertia\Inertia;
use Inertia\Response;

class IndexStaffUserController extends Controller
{
    public function __invoke(): Response
    {
        $users = User::query()->whereNotNull('roles')->orderBy('last_name')->orderBy('first_name')->get();

        return Inertia::render('Staff/Index', [
            'staff' => $this->getStaffByRole($users),
        ]);
    }

    /**
     * Group users by their roles and return the staff structure
     *
     * @param  Collection<User>  $users
     */
    protected function getStaffByRole(Collection $users): array
    {
        $staff = collect([
            Role::ATM->value => [
                'title' => Role::ATM->getDisplayName(),
                'code' => 'zab-' . Role::ATM->value,
            ],
            Role::DATM->value => [
                'title' => Role::DATM->getDisplayName(),
                'code' => 'zab-' . Role::DATM->value,
            ],
            Role::TA->value => [
                'title' => Role::TA->getDisplayName(),
                'code' => 'zab-' . Role::TA->value,
            ],
            Role::EC->value => [
                'title' => Role::EC->getDisplayName(),
                'code' => 'zab-' . Role::EC->value,
            ],
            Role::WM->value => [
                'title' => Role::WM->getDisplayName(),
                'code' => 'john.morgan',
            ],
            Role::FE->value => [
                'title' => Role::FE->getDisplayName(),
                'code' => 'edward.sterling',
            ],
            Role::INS->value => [
                'title' => Role::INS->getDisplayName(),
                'code' => 'instructors',
            ],
            Role::MTR->value => [
                'title' => Role::MTR->getDisplayName(),
                'code' => 'instructors',
            ],
            Role::DTA->value => [
                'title' => Role::DTA->getDisplayName(),
                'code' => 'connor.gibson',
            ],
        ])->map(fn ($role) => array_merge($role, ['users' => []]));

        if ($users->isEmpty()) {
            return $staff->toArray();
        }

        foreach ($users as $user) {
            foreach ($user->roles as $role) {
                if ($staff->has($role->value)) {
                    $staffItem = $staff->get($role->value);
                    $staffItem['users'][] = $user;
                    $staff->put($role->value, $staffItem);
                }
            }
        }

        return $staff->toArray();
    }
}
