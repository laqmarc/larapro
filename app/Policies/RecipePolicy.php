<?php

namespace App\Policies;

use App\Models\Recipe;
use App\Models\User;

class RecipePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Recipe $recipe): bool
    {
        if ($recipe->is_public) {
            return true;
        }

        return $user?->id === $recipe->user_id;
    }

    public function create(User $user): bool
    {
        return $user->exists;
    }

    public function update(User $user, Recipe $recipe): bool
    {
        return $user->id === $recipe->user_id;
    }

    public function delete(User $user, Recipe $recipe): bool
    {
        return $user->id === $recipe->user_id;
    }

    public function restore(User $user, Recipe $recipe): bool
    {
        return $user->id === $recipe->user_id;
    }

    public function forceDelete(User $user, Recipe $recipe): bool
    {
        return false;
    }
}
