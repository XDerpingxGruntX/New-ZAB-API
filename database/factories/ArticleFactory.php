<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->sentence();

        return [
            'user_id' => User::factory()->staff(),
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => $this->faker->paragraphs($this->faker->numberBetween(3, 8), true),
        ];
    }
}
