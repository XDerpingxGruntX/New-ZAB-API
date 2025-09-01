<?php

namespace Database\Seeders;

use App\Models\Certification;
use Illuminate\Database\Seeder;

class CertificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $certifications = [
            ['code' => 'kphxground', 'name' => 'KPHX GND', 'class' => 1, 'facility' => 'KPHX'],
            ['code' => 'kphxtower', 'name' => 'KPHX TWR', 'class' => 1, 'facility' => 'KPHX'],
            ['code' => 'p50', 'name' => 'P50', 'class' => 1, 'facility' => 'P50'],
            ['code' => 'enroute', 'name' => 'Enroute', 'class' => 1, 'facility' => 'Enroute'],
            ['code' => 'kabq', 'name' => 'KABQ', 'class' => 2, 'facility' => 'KABQ'],
            ['code' => 'kflg', 'name' => 'KFLG', 'class' => 2, 'facility' => 'KFLG'],
            ['code' => 'kluf', 'name' => 'KLUF', 'class' => 2, 'facility' => 'KLUF'],
            ['code' => 'ksaf', 'name' => 'KSAF', 'class' => 2, 'facility' => 'KSAF'],
        ];

        foreach ($certifications as $certification) {
            Certification::create($certification);
        }
    }
}
