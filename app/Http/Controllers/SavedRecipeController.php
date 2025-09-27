<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SavedRecipeController extends Controller
{
    public function index(Request $request): View
    {
        $recipes = $request->user()
            ->savedRecipes()
            ->with(['author', 'media', 'dietaryTags'])
            ->withCount(['comments as comments_count' => fn ($q) => $q->where('is_published', true)])
            ->orderByDesc('saved_recipes.created_at')
            ->paginate(12);

        return view('recipes.saved', compact('recipes'));
    }

    public function store(Request $request, Recipe $recipe): RedirectResponse
    {
        $this->authorize('view', $recipe);

        $request->user()->savedRecipes()->syncWithoutDetaching([
            $recipe->id => ['created_at' => now(), 'updated_at' => now()],
        ]);

        return back()->with('status', 'Recipe saved to your collection.');
    }

    public function destroy(Request $request, Recipe $recipe): RedirectResponse
    {
        $this->authorize('view', $recipe);

        $request->user()->savedRecipes()->detach($recipe->id);

        return back()->with('status', 'Recipe removed from your collection.');
    }
}
