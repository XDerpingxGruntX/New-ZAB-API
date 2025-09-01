<?php

namespace App\Data;

use App\Enums\ControllerRating;
use App\Enums\Role;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class ControllerData extends Data
{
    #[Computed]
    public Collection $mappedRoles;

    public function __construct(
        public int $cid,
        #[MapInputName('fname')]
        public string $first_name,
        #[MapInputName('lname')]
        public string $last_name,
        public ?string $email,
        public ControllerRating $rating,
        #[MapInputName('facility')]
        public string $home_facility,

        #[MapInputName('roles')]
        public array $controllerRoles,

        #[MapInputName('flag_broadcastOptedIn')]
        public ?bool $broadcast_opt_in,
        public string $membership,
        public Carbon $created_at,
        public Carbon $updated_at,
    ) {
        $this->mappedRoles = new Collection;

        foreach ($this->controllerRoles as $role) {
            if ($role['facility'] !== 'ZAB') {
                continue;
            }

            $roleCode = Str::upper($role['role']);
            if (Role::tryFrom($roleCode)) {
                $this->mappedRoles->push(Role::from($roleCode));
            }
        }
    }
}
