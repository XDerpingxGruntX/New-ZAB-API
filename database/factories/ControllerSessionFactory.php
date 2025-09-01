<?php

namespace Database\Factories;

use App\Enums\Airport;
use App\Enums\ControllerPosition;
use App\Enums\ControllerRating;
use App\Models\ControllerSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ControllerSession>
 */
class ControllerSessionFactory extends Factory
{
    protected $model = ControllerSession::class;

    public function definition(): array
    {
        $connectedAt = $this->faker->dateTimeBetween('-1 month', 'now');
        $disconnectedAt = $this->faker->optional(0.9)->dateTimeBetween($connectedAt, '+4 hours');
        $user = User::factory()->member();

        return [
            'user_id' => $user,
            'cid' => fn (array $attributes
            ) => User::find($attributes['user_id'])?->cid ?? $this->faker->unique()->numberBetween(800000, 2000000),
            'rating' => fn (array $attributes
            ) => User::find($attributes['user_id'])?->rating ?? $this->faker->randomElement(ControllerRating::cases()),
            'position' => function (array $attributes) {
                /** @var Airport $airport */
                $airport = $this->faker->randomElement(Airport::cases());
                /** @var ControllerPosition $position */
                $position = $this->faker->randomElement(ControllerPosition::cases());
                $suffix = $this->faker->optional(0.3)->randomElement(['1', '2', '3']);

                return $airport->value . '_' . $position->value . ($suffix ?? '');
            },
            'frequency' => $this->faker->randomFloat(3, 118.0, 136.975),
            'atis' => $this->faker->optional(0.7)->randomLetter(),
            'connected_at' => $connectedAt,
            'disconnected_at' => $disconnectedAt,
        ];
    }

    /**
     * Indicate that the session is active.
     */
    public function active(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'connected_at' => $this->faker->dateTimeBetween('-4 hours', '-1 hour'),
                'disconnected_at' => null,
            ];
        });
    }

    /**
     * Indicate that the session is completed.
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $connectedAt = $this->faker->dateTimeBetween('-1 month', '-1 day');

            return [
                'connected_at' => $connectedAt,
                'disconnected_at' => $this->faker->dateTimeBetween($connectedAt, '+4 hours'),
            ];
        });
    }
}
