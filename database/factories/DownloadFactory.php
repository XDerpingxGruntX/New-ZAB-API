<?php

namespace Database\Factories;

use App\Enums\DownloadCategory;
use App\Models\Download;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Download>
 */
class DownloadFactory extends Factory
{
    protected $model = Download::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->sentence(3);

        return [
            'user_id' => User::factory()->staff(),
            'name' => $name,
            'category' => $this->faker->randomElement(DownloadCategory::cases()),
            'description' => $this->faker->optional(0.8)->paragraph(),
            'file_path' => function (array $attributes) {
                $extension = $this->faker->randomElement(['zip', 'exe', 'msi', '7z', 'pdf']);

                return 'downloads/' . Str::slug($attributes['name']) . '.' . $extension;
            },
        ];
    }

    /**
     * Indicate that the download is a miscellaneous package.
     */
    public function miscellaneous(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'category' => DownloadCategory::MISCELLANEOUS,
                'file_path' => 'downloads/' . Str::slug($attributes['name']) . '.zip',
            ];
        });
    }

    /**
     * Indicate that the download is a vATIS profile.
     */
    public function vatis(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'category' => DownloadCategory::VATIS,
                'file_path' => 'downloads/' . Str::slug($attributes['name']) . '.json',
            ];
        });
    }

    /**
     * Indicate that the download is a vSTARS profile.
     */
    public function vstars(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'category' => DownloadCategory::VSTARS,
                'file_path' => 'downloads/' . Str::slug($attributes['name']) . '.gz',
            ];
        });
    }

    /**
     * Indicate that the download is a vERAM profile.
     */
    public function veram(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'category' => DownloadCategory::VERAM,
                'file_path' => 'downloads/' . Str::slug($attributes['name']) . '.gz',
            ];
        });
    }
}
