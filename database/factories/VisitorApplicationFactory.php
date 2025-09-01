<?php

namespace Database\Factories;

use App\Enums\ControllerRating;
use App\Models\User;
use App\Models\VisitorApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VisitorApplication>
 */
class VisitorApplicationFactory extends Factory
{
    protected $model = VisitorApplication::class;

    public function definition(): array
    {
        $user = User::factory()->visitor();

        return [
            'user_id' => $user,
            'first_name' => fn (array $attributes
            ) => User::find($attributes['user_id'])?->first_name ?? $this->faker->firstName(),
            'last_name' => fn (array $attributes
            ) => User::find($attributes['user_id'])?->last_name ?? $this->faker->lastName(),
            'email' => fn (array $attributes
            ) => User::find($attributes['user_id'])?->email ?? $this->faker->unique()->safeEmail(),
            'rating' => fn (array $attributes
            ) => User::find($attributes['user_id'])?->rating ?? $this->faker->randomElement(ControllerRating::cases()),
            'home_facility' => fn (array $attributes
            ) => User::find($attributes['user_id'])?->home_facility ?? $this->faker->randomElement([
                'KZLA', 'KZNY', 'KZDC', 'KZMA',
            ]),
            'justification' => $this->faker->sentence(),
            'accepted_at' => $this->faker->optional(0.6)->dateTimeBetween('-1 month', 'now'),
            // 60% chance of being accepted
        ];
    }

    /**
     * Indicate that the application is accepted.
     */
    public function accepted(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'accepted_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            ];
        });
    }

    /**
     * Indicate that the application is pending.
     */
    public function pending(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'accepted_at' => null,
            ];
        });
    }
}
