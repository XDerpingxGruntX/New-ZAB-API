<?php

namespace Database\Factories;

use App\Enums\Airport;
use App\Enums\ControllerPosition;
use App\Models\Event;
use App\Models\EventPosition;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventPosition>
 */
class EventPositionFactory extends Factory
{
    protected $model = EventPosition::class;

    public function definition(): array
    {
        /** @var Airport $airport */
        $airport = $this->faker->randomElement(Airport::cases());

        /** @var ControllerPosition $position */
        $position = $this->faker->randomElement(ControllerPosition::cases());

        return [
            'event_id' => Event::factory(),
            'callsign' => $airport->value . '_' . $position->value,
            'airport' => $airport,
            'position' => $position,
            'assigned' => $this->faker->boolean(20), // 20% chance of being assigned
            'user_id' => fn (array $attributes) => $attributes['assigned'] ? User::factory()->member() : null,
        ];
    }

    /**
     * Indicate that the position is assigned.
     */
    public function assigned(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'assigned' => true,
                'user_id' => User::factory()->member(),
            ];
        });
    }

    /**
     * Indicate that the position is unassigned.
     */
    public function unassigned(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'assigned' => false,
                'user_id' => null,
            ];
        });
    }
}
