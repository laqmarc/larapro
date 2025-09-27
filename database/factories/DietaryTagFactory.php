<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\DietaryTag>
 */
class DietaryTagFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Vegan',
            'Vegetarian',
            'Gluten Free',
            'Dairy Free',
            'Keto',
            'Paleo',
            'Low Carb',
            'Halal',
            'Kosher',
            'Nut Free',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'color' => fake()->hexColor(),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
