<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'recipe_id' => Recipe::factory(),
            'user_id' => User::factory(),
            'parent_id' => null,
            'body' => fake()->paragraph(),
            'rating' => fake()->optional(0.6)->numberBetween(1, 5),
            'is_published' => fake()->boolean(90),
        ];
    }

    public function replyTo(Comment $parent): self
    {
        return $this->state(fn () => [
            'recipe_id' => $parent->recipe_id,
            'user_id' => $parent->user_id,
            'parent_id' => $parent->id,
        ]);
    }
}
