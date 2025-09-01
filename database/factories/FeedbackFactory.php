<?php

namespace Database\Factories;

use App\Enums\ControllerPosition;
use App\Enums\FeedbackRating;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Feedback>
 */
class FeedbackFactory extends Factory
{
    protected $model = Feedback::class;

    public function definition(): array
    {
        return [
            'critic_id' => User::factory(),
            'controller_id' => User::factory()->member(),
            'position' => $this->faker->randomElement(ControllerPosition::cases()),
            'rating' => $this->faker->randomElement(FeedbackRating::cases()),
            'comment' => $this->faker->sentence(),
            'ip_address' => $this->faker->ipv4(),
            'anonymous' => $this->faker->boolean(30), // 30% chance of being anonymous
            'approved_at' => $this->faker->optional(0.7)->dateTimeBetween('-1 month', 'now'),
            // 70% chance of being approved
        ];
    }

    /**
     * Indicate that the feedback is approved.
     */
    public function approved(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'approved_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            ];
        });
    }

    /**
     * Indicate that the feedback is pending approval.
     */
    public function pending(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'approved_at' => null,
            ];
        });
    }

    /**
     * Indicate that the feedback is anonymous.
     */
    public function anonymous(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'anonymous' => true,
            ];
        });
    }
}
