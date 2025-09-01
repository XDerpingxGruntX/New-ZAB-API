<?php

namespace Database\Factories;

use App\Enums\Airport;
use App\Models\FlightSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FlightSession>
 */
class FlightSessionFactory extends Factory
{
    protected $model = FlightSession::class;

    public function definition(): array
    {
        $user = User::factory();
        $aircraft = [
            'A320', 'B738', 'B739', 'CRJ7', 'CRJ9', 'E170', 'E175',
            'C172', 'PA28', 'BE58', 'SR22', 'C152', 'DA40', 'DA42',
        ];

        return [
            'user_id' => $user,
            'cid' => fn (array $attributes
            ) => User::find($attributes['user_id'])?->cid ?? $this->faker->unique()->numberBetween(800000, 2000000),
            'callsign' => function (array $attributes) {
                $airlines = ['AAL', 'DAL', 'UAL', 'SWA', 'N'];
                $prefix = $this->faker->randomElement($airlines);
                $suffix = $prefix === 'N' ?
                    $this->faker->regexify('[1-9][0-9]{2}[A-Z]{2}') :
                    $this->faker->numberBetween(1, 9999);

                return $prefix . $suffix;
            },
            'aircraft' => $this->faker->randomElement($aircraft),
            'departure_airport' => $this->faker->randomElement(Airport::cases())->value,
            'arrival_airport' => function (array $attributes) {
                $airports = collect(Airport::cases())->map->value;

                return $this->faker->randomElement($airports->diff([$attributes['departure_airport']])->toArray());
            },
            'latitude' => $this->faker->latitude(41.0, 45.0),
            'longitude' => $this->faker->longitude(-93.0, -87.0),
            'heading' => $this->faker->numberBetween(0, 359),
            'altitude' => $this->faker->numberBetween(0, 45000),
            'planned_altitude' => $this->faker->optional(0.9)->numberBetween(3000, 45000),
            'speed' => $this->faker->numberBetween(0, 500),
            'route' => $this->faker->optional(0.8)->sentence(6),
            'remarks' => $this->faker->optional(0.4)->sentence(),
        ];
    }

    /**
     * Indicate that the flight is a commercial flight.
     */
    public function commercial(): static
    {
        return $this->state(function (array $attributes) {
            $airlines = ['AAL', 'DAL', 'UAL', 'SWA'];
            $prefix = $this->faker->randomElement($airlines);
            $aircraft = ['A320', 'B738', 'B739', 'CRJ7', 'CRJ9', 'E170', 'E175'];

            return [
                'callsign' => $prefix . $this->faker->numberBetween(1, 9999),
                'aircraft' => $this->faker->randomElement($aircraft),
                'planned_altitude' => $this->faker->numberBetween(28000, 45000),
            ];
        });
    }

    /**
     * Indicate that the flight is a general aviation flight.
     */
    public function generalAviation(): static
    {
        return $this->state(function (array $attributes) {
            $aircraft = ['C172', 'PA28', 'BE58', 'SR22', 'C152', 'DA40', 'DA42'];

            return [
                'callsign' => 'N' . $this->faker->regexify('[1-9][0-9]{2}[A-Z]{2}'),
                'aircraft' => $this->faker->randomElement($aircraft),
                'planned_altitude' => $this->faker->numberBetween(3000, 12000),
            ];
        });
    }
}
