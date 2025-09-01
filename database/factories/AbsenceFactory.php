<?php

namespace Database\Factories;

use App\Models\Absence;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Absence>
 */
class AbsenceFactory extends Factory
{
    protected $model = Absence::class;

    public function definition(): array
    {
        $startsAt = $this->faker->dateTimeBetween('now', '+6 months');
        $expiresAt = $this->faker->optional(0.8)->dateTimeBetween($startsAt, '+1 year');

        return [
            'user_id' => User::factory()->member(),
            'justification' => $this->faker->optional(0.9)->sentence(),
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
        ];
    }

    /**
     * Indicate that the absence is active.
     */
    public function active(): static
    {
        return $this->state(function (array $attributes) {
            $startsAt = $this->faker->dateTimeBetween('-1 month', 'now');

            return [
                'starts_at' => $startsAt,
                'expires_at' => $this->faker->dateTimeBetween('+1 day', '+6 months'),
            ];
        });
    }

    /**
     * Indicate that the absence is expired.
     */
    public function expired(): static
    {
        return $this->state(function (array $attributes) {
            $startsAt = $this->faker->dateTimeBetween('-1 year', '-6 months');

            return [
                'starts_at' => $startsAt,
                'expires_at' => $this->faker->dateTimeBetween('-5 months', '-1 day'),
            ];
        });
    }

    /**
     * Indicate that the absence is permanent.
     */
    public function permanent(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'expires_at' => null,
            ];
        });
    }
}
