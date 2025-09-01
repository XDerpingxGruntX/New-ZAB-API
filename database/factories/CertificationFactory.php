<?php

namespace Database\Factories;

use App\Enums\CertificationClass;
use App\Models\Certification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Certification>
 */
class CertificationFactory extends Factory
{
    protected $model = Certification::class;

    public function definition(): array
    {
        $certifications = [
            ['GND', 'Ground', CertificationClass::TIER_ONE, 'KZAB'],
            ['TWR', 'Tower', CertificationClass::TIER_ONE, 'KZAB'],
            ['APP', 'Approach', CertificationClass::TIER_TWO, 'KZAB'],
            ['CTR', 'Center', CertificationClass::TIER_TWO, 'KZAB'],
            ['SAT', 'Satellite', CertificationClass::TIER_ONE, null],
            ['TMU', 'Traffic Management Unit', CertificationClass::TIER_TWO, null],
        ];

        $cert = $this->faker->randomElement($certifications);

        return [
            'code' => $cert[0],
            'name' => $cert[1],
            'class' => $cert[2],
            'facility' => $cert[3],
        ];
    }
}
