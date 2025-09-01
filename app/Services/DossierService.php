<?php

namespace App\Services;

use App\Models\Dossier;
use App\Models\User;

final readonly class DossierService
{
    /**
     * Create a new dossier entry.
     */
    public function create(User|int $user, string $action, User|int|null $affectedUser = null): Dossier
    {
        $userId = $user instanceof User ? $user->id : $user;
        $affectedUserId = null;

        if ($affectedUser !== null) {
            $affectedUserId = $affectedUser instanceof User ? $affectedUser->id : $affectedUser;
        }

        return Dossier::create([
            'user_id' => $userId,
            'affected_user_id' => $affectedUserId,
            'action' => $action,
        ]);
    }
}
