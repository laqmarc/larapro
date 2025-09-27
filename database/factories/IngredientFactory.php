<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Ingredient>
 */
class IngredientFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(fake()->numberBetween(1, 2), true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'plural_name' => fake()->boolean(60) ? Str::title($name) . 's' : null,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
