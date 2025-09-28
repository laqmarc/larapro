<?php

namespace Database\Factories;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Recipe>
 */
class RecipeFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);
        $isPublic = fake()->boolean(80);

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(100, 999),
            'summary' => fake()->optional()->paragraph(),
            'description' => fake()->optional()->paragraphs(3, true),
            'instructions' => collect(range(1, fake()->numberBetween(3, 6)))
                ->map(fn () => fake()->paragraph())
                ->implode("\n\n"),
            'prep_minutes' => fake()->numberBetween(5, 60),
            'cook_minutes' => fake()->optional()->numberBetween(10, 120),
            'servings' => fake()->numberBetween(1, 8),
            'difficulty' => fake()->randomElement(Recipe::DIFFICULTIES),
            'dish_type' => fake()->randomElement(Recipe::DISH_TYPES),
            'is_public' => $isPublic,
            'published_at' => $isPublic ? fake()->dateTimeBetween('-1 year', 'now') : null,
            'nutrition' => fake()->optional()->passthrough([
                'calories' => fake()->numberBetween(150, 900),
                'protein' => fake()->numberBetween(5, 50),
                'carbs' => fake()->numberBetween(10, 120),
                'fat' => fake()->numberBetween(5, 60),
            ]),
        ];
    }
}
