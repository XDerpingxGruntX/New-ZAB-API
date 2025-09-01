<?php

namespace Database\Factories;

use App\Enums\Airport;
use App\Models\PilotReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PilotReport>
 */
class PilotReportFactory extends Factory
{
    protected $model = PilotReport::class;

    public function definition(): array
    {
        $aircraft = [
            'A320', 'B738', 'B739', 'CRJ7', 'CRJ9', 'E170', 'E175',
            'C172', 'PA28', 'BE58', 'SR22', 'C152', 'DA40', 'DA42',
        ];

        $locations = collect(Airport::cases())->map->value->toArray();
        $locations = array_merge(
            $locations,
            array_map(fn ($airport) => $this->faker->numberBetween(5, 30) . $this->faker->randomElement([
                'N', 'S', 'E', 'W',
            ]) . ' ' . $airport, $locations)
        );

        $skyConditions = [
            'SKC', 'CLR', 'FEW020', 'SCT030', 'BKN040', 'OVC050',
            'FEW020 BKN040', 'SCT025 OVC040',
        ];

        $turbulence = [
            'NONE', 'LIGHT', 'MODERATE', 'SEVERE',
            'LIGHT CHOP', 'MODERATE CHOP',
        ];

        $icing = [
            'NONE', 'TRACE', 'LIGHT', 'MODERATE', 'SEVERE',
            'LIGHT RIME', 'MODERATE CLEAR',
        ];

        return [
            'external_id' => $this->faker->unique()->numberBetween(1, 999999),
            'location' => $this->faker->randomElement($locations),
            'aircraft' => $this->faker->randomElement($aircraft),
            'altitude' => $this->faker->numberBetween(0, 45000) . 'ft',
            'sky' => $this->faker->optional(0.8)->randomElement($skyConditions),
            'turbulence' => $this->faker->optional(0.6)->randomElement($turbulence),
            'icing' => $this->faker->optional(0.4)->randomElement($icing),
            'visibility' => $this->faker->optional(0.7)->numberBetween(1, 50) . 'SM',
            'temperature' => $this->faker->numberBetween(-30, 40),
            'wind' => $this->faker->optional(0.9)->numberBetween(0, 359) . '@' . $this->faker->numberBetween(0, 50),
            'urgent' => $this->faker->boolean(10), // 10% chance of being urgent
            'manual' => $this->faker->boolean(20), // 20% chance of being manual
            'raw' => function (array $attributes) {
                return implode(' ', array_filter([
                    "UA {$attributes['external_id']}",
                    $attributes['location'],
                    "/{$attributes['aircraft']}",
                    $attributes['altitude'],
                    $attributes['sky'] ?? '',
                    $attributes['turbulence'] ? "TB {$attributes['turbulence']}" : '',
                    $attributes['icing'] ? "IC {$attributes['icing']}" : '',
                    $attributes['visibility'] ? "VS {$attributes['visibility']}" : '',
                    $attributes['temperature'] ? "TP {$attributes['temperature']}" : '',
                    $attributes['wind'] ? "WND {$attributes['wind']}" : '',
                ]));
            },
            'reported_at' => $this->faker->dateTimeBetween('-1 day', 'now'),
        ];
    }

    /**
     * Indicate that the report is urgent.
     */
    public function urgent(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'urgent' => true,
            ];
        });
    }

    /**
     * Indicate that the report is manual.
     */
    public function manual(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'manual' => true,
            ];
        });
    }
}
