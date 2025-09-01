<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventPosition;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventRegistration>
 */
class EventRegistrationFactory extends Factory
{
    protected $model = EventRegistration::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'user_id' => User::factory()->member(),
            'requested_positions' => function (array $attributes) {
                $event = Event::find($attributes['event_id']);
                if (! $event) {
                    $event = Event::factory()->create();
                }

                // Get or create event positions
                $positions = $event->eventPositions;
                if ($positions->isEmpty()) {
                    $positions = EventPosition::factory()
                        ->count($this->faker->numberBetween(2, 4))
                        ->create(['event_id' => $event->id]);
                }

                // Select random positions to request
                return $positions->random($this->faker->numberBetween(1, min(3, $positions->count())))
                    ->pluck('id')
                    ->toArray();
            },
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (EventRegistration $registration) {
            // Ensure the user is a member
            if (! $registration->user->member) {
                $registration->user->update(['member' => true, 'visitor' => false]);
            }
        });
    }
}
