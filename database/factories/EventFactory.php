<?php

namespace Database\Factories;

use App\Enums\ControllerPosition;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->sentence(3);
        $startsAt = $this->faker->dateTimeBetween('+1 week', '+2 months');
        $endsAt = (clone $startsAt)->modify('+' . $this->faker->numberBetween(2, 6) . ' hours');
        $registrationOpensAt = (clone $startsAt)->modify('-2 weeks');
        $registrationClosesAt = (clone $startsAt)->modify('-1 day');

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraphs(3, true),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'registration_opens_at' => $registrationOpensAt,
            'registration_closes_at' => $registrationClosesAt,
            'emails_sent_at' => $this->faker->optional(0.3)->dateTimeBetween($registrationOpensAt,
                $registrationClosesAt),
            'banner_path' => function (array $attributes) {
                $extension = $this->faker->randomElement(['jpg', 'png', 'gif']);

                return 'events/' . $attributes['slug'] . '.' . $extension;
            },
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Event $event) {
            foreach (ControllerPosition::cases() as $position) {
                $event->eventPositions()->create([
                    'code' => $position->value,
                    'type' => $position->getDisplayName(),
                    'assigned' => false,
                ]);
            }
        });
    }

    /**
     * Indicate that the event is in the past.
     */
    public function past(): static
    {
        return $this->state(function (array $attributes) {
            $startsAt = $this->faker->dateTimeBetween('-2 months', '-1 week');
            $endsAt = (clone $startsAt)->modify('+' . $this->faker->numberBetween(2, 6) . ' hours');
            $registrationOpensAt = (clone $startsAt)->modify('-2 weeks');
            $registrationClosesAt = (clone $startsAt)->modify('-1 day');

            return [
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'registration_opens_at' => $registrationOpensAt,
                'registration_closes_at' => $registrationClosesAt,
                'emails_sent_at' => $this->faker->optional(0.8)->dateTimeBetween($registrationOpensAt,
                    $registrationClosesAt),
            ];
        });
    }

    /**
     * Indicate that the event is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(function (array $attributes) {
            $startsAt = $this->faker->dateTimeBetween('-2 hours', '-1 hour');
            $endsAt = $this->faker->dateTimeBetween('+1 hour', '+2 hours');
            $registrationOpensAt = (clone $startsAt)->modify('-2 weeks');
            $registrationClosesAt = (clone $startsAt)->modify('-1 day');

            return [
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'registration_opens_at' => $registrationOpensAt,
                'registration_closes_at' => $registrationClosesAt,
                'emails_sent_at' => $this->faker->optional(0.9)->dateTimeBetween($registrationOpensAt,
                    $registrationClosesAt),
            ];
        });
    }
}
