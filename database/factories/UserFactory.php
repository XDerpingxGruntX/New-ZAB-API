<?php

namespace Database\Factories;

use App\Enums\ControllerRating;
use App\Enums\Role;
use App\Models\Certification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();

        return [
            'cid' => $this->faker->unique()->numberBetween(800000, 2000000),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$4$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'rating' => $this->faker->randomElement(ControllerRating::cases()),
            'operating_initials' => $this->faker->unique()->words(asText: true),
            'home_facility' => $this->faker->randomElement(['KZAB', 'KZAU', 'KZID', 'KZMP']),
            'roles' => null,
            'broadcast_opt_in' => $this->faker->boolean(),
            'member' => $this->faker->boolean(80), // 80% chance of being a member
            'visitor' => fn (array $attributes) => ! $attributes['member'], // If not a member, then a visitor
            'next_activity_check_at' => $this->faker->optional(0.7)->dateTimeBetween('now', '+6 months'),
            'warning_delivered_at' => null,
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            // Randomly attach roles
            $random = $this->faker->numberBetween(1, 100);
            if ($random <= 10) { // 10% chance of being senior staff
                $user->roles = [$this->faker->randomElement([Role::ATM, Role::DATM, Role::TA])];
                $user->save();
            } elseif ($random <= 30) { // Additional 20% chance of being regular staff
                $user->roles = [$this->faker->randomElement([Role::EC, Role::WM, Role::FE])];
                $user->save();
            }

            // Randomly attach certifications
            if ($this->faker->boolean(60)) { // 60% chance of having certifications
                $user->certifications()->attach(
                    Certification::factory()->count($this->faker->numberBetween(1, 4))->create()
                );
            }
        });
    }

    /**
     * Indicate that the user is a manager.
     */
    public function manager(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->roles = [$this->faker->randomElement([Role::ATM, Role::DATM])];
            $user->save();
        });
    }

    /**
     * Indicate that the user is a staff member.
     */
    public function staff(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->roles = [
                $this->faker->randomElement([
                    Role::ATM, Role::DATM, Role::TA, Role::EC, Role::WM, Role::FE,
                ]),
            ];
            $user->save();
        });
    }

    /**
     * Indicate that the user is a senior staff member.
     */
    public function senior(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->roles = [$this->faker->randomElement([Role::ATM, Role::DATM, Role::TA])];
            $user->save();
        });
    }

    /**
     * Indicate that the user is an instructor.
     */
    public function instructor(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->roles = [$this->faker->randomElement([Role::ATM, Role::DATM, Role::TA, Role::INS, Role::MTR])];
            $user->save();
        });
    }

    /**
     * Indicate that the user is a member.
     */
    public function member(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'member' => true,
                'visitor' => false,
            ];
        });
    }

    /**
     * Indicate that the user is a visitor.
     */
    public function visitor(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'member' => false,
                'visitor' => true,
            ];
        });
    }
}
