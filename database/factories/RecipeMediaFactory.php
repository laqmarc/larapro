<?php

namespace Database\Factories;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\RecipeMedia>
 */
class RecipeMediaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'recipe_id' => Recipe::factory(),
            'disk' => 'public',
            'path' => 'recipes/' . fake()->unique()->lexify('????????') . '.jpg',
            'caption' => fake()->optional()->sentence(),
            'is_primary' => false,
            'position' => fake()->numberBetween(0, 5),
        ];
    }

    public function primary(): self
    {
        return $this->state(fn () => [
            'is_primary' => true,
            'position' => 0,
        ]);
    }
}
