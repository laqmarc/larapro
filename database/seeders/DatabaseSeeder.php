<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\DietaryTag;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeMedia;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tagNames = collect([
            'Vegan',
            'Vegetarian',
            'Gluten Free',
            'Dairy Free',
            'Keto',
            'Paleo',
            'Low Carb',
            'Kosher',
            'Halal',
            'Nut Free',
        ]);

        $tags = $tagNames->map(function (string $name) {
            return DietaryTag::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'color' => fake()->hexColor(),
                    'description' => fake()->optional()->sentence(),
                ]
            );
        });

        $ingredients = Ingredient::all();
        if ($ingredients->isEmpty()) {
            $ingredients = Ingredient::factory(40)->create();
        }

        if (! Recipe::exists()) {
            $users = User::factory(10)->create();

            Recipe::factory(25)
                ->recycle($users)
                ->create()
                ->each(function (Recipe $recipe) use ($ingredients, $tags, $users) {
                    $selectedIngredients = $ingredients->random(fake()->numberBetween(5, 10));

                    foreach ($selectedIngredients->values() as $index => $ingredient) {
                        $recipe->ingredients()->attach($ingredient->id, [
                            'quantity' => fake()->randomFloat(2, 0.25, 5),
                            'unit' => fake()->randomElement(['g', 'kg', 'ml', 'cups', 'tbsp', 'tsp', 'pieces']),
                            'preparation' => fake()->optional(0.4)->sentence(3),
                            'position' => $index,
                        ]);
                    }

                    $tagSelection = $tags->random(fake()->numberBetween(1, 3));
                    $tagIds = $tagSelection instanceof Collection
                        ? $tagSelection->pluck('id')->all()
                        : [$tagSelection->id];
                    $recipe->dietaryTags()->sync($tagIds);

                    RecipeMedia::factory()
                        ->primary()
                        ->for($recipe)
                        ->create();

                    RecipeMedia::factory()
                        ->count(fake()->numberBetween(0, 3))
                        ->for($recipe)
                        ->sequence(fn ($sequence) => ['position' => $sequence->index + 1])
                        ->create();

                    Comment::factory()
                        ->count(fake()->numberBetween(2, 6))
                        ->for($recipe)
                        ->state(fn () => [
                            'user_id' => $users->random()->id,
                        ])
                        ->create();

                    $saversCount = fake()->numberBetween(0, 4);
                    if ($saversCount > 0) {
                        $users->random($saversCount)->each(function (User $user) use ($recipe) {
                            $recipe->savedByUsers()->syncWithoutDetaching([
                                $user->id => ['created_at' => now()->subDays(fake()->numberBetween(0, 30))],
                            ]);
                        });
                    }
                });
        }

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
            ]
        );
    }
}
