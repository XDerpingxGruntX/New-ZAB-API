<?php

namespace Database\Factories;

use App\Enums\DocumentCategory;
use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->sentence(3);

        return [
            'user_id' => User::factory()->staff(),
            'name' => $name,
            'slug' => Str::slug($name),
            'category' => $this->faker->randomElement(DocumentCategory::cases()),
            'description' => $this->faker->optional(0.8)->paragraph(),
            'content' => $this->faker->optional(0.7)->paragraphs(3, true),
            'file_path' => function (array $attributes) {
                // Only generate file path if content is null
                if (! isset($attributes['content'])) {
                    $extension = $this->faker->randomElement(['pdf', 'doc', 'docx', 'txt']);

                    return 'documents/' . Str::slug($attributes['name']) . '.' . $extension;
                }

                return null;
            },
        ];
    }

    /**
     * Indicate that the document has content.
     */
    public function withContent(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'content' => $this->faker->paragraphs(3, true),
                'file_path' => null,
            ];
        });
    }

    /**
     * Indicate that the document has a file.
     */
    public function withFile(): static
    {
        return $this->state(function (array $attributes) {
            $extension = $this->faker->randomElement(['pdf', 'doc', 'docx', 'txt']);

            return [
                'content' => null,
                'file_path' => 'documents/' . Str::slug($attributes['name']) . '.' . $extension,
            ];
        });
    }
}
