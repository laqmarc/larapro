<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecipeRequest;
use App\Models\DietaryTag;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RecipeController extends Controller
{
    public function index(Request $request): View
    {
        $query = Recipe::query()
            ->with(['author', 'media', 'dietaryTags'])
            ->withCount(['comments as comments_count' => fn ($q) => $q->where('is_published', true)])
            ->withCount('savedByUsers')
            ->public();

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($difficulty = $request->string('difficulty')->toString()) {
            $query->where('difficulty', $difficulty);
        }

        if ($dietary = $request->input('dietary')) {
            $dietary = collect(is_array($dietary) ? $dietary : explode(',', $dietary))
                ->filter()
                ->all();

            if (! empty($dietary)) {
                $query->whereHas('dietaryTags', fn ($q) => $q->whereIn('slug', $dietary));
            }
        }

        if ($ingredient = $request->string('ingredient')->toString()) {
            $query->whereHas('ingredients', fn ($q) => $q->where('slug', $ingredient));
        }

        if ($time = $request->integer('max_time')) {
            $query->whereRaw('(COALESCE(prep_minutes, 0) + COALESCE(cook_minutes, 0)) <= ?', [$time]);
        }

        if ($request->boolean('saved') && $request->user()) {
            $query->whereHas('savedByUsers', fn ($q) => $q->where('user_id', $request->user()->id));
        }

        if ($request->boolean('mine') && $request->user()) {
            $query->where('user_id', $request->user()->id);
        }

        $recipes = $query
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $dietaryTags = DietaryTag::orderBy('name')->get();
        $difficulties = ['easy', 'medium', 'hard'];
        $popularIngredients = Ingredient::orderBy('name')->limit(20)->get();

        return view('recipes.index', compact('recipes', 'dietaryTags', 'difficulties', 'popularIngredients'));
    }

    public function create(): View
    {
        $this->authorize('create', Recipe::class);

        $dietaryTags = DietaryTag::orderBy('name')->get();
        $ingredients = Ingredient::orderBy('name')->get();

        return view('recipes.create', compact('dietaryTags', 'ingredients'));
    }

    public function store(RecipeRequest $request): RedirectResponse
    {
        $this->authorize('create', Recipe::class);

        $data = $request->validated();

        $recipe = DB::transaction(function () use ($data, $request) {
            $recipe = Recipe::create([
                'user_id' => Auth::id(),
                'title' => $data['title'],
                'slug' => $this->generateSlug($data['title']),
                'summary' => $data['summary'] ?? null,
                'description' => $data['description'] ?? null,
                'instructions' => $data['instructions'],
                'prep_minutes' => $data['prep_minutes'] ?? null,
                'cook_minutes' => $data['cook_minutes'] ?? null,
                'servings' => $data['servings'] ?? null,
                'difficulty' => $data['difficulty'] ?? null,
                'is_public' => (bool) ($data['is_public'] ?? false),
                'published_at' => $this->resolvePublishedAt($data),
                'nutrition' => $this->filterNutrition($data['nutrition'] ?? null),
            ]);

            $this->syncIngredients($recipe, $data['ingredients']);
            $recipe->dietaryTags()->sync($data['dietary_tags'] ?? []);
            $this->syncMedia($recipe, $request);

            return $recipe;
        });

        return redirect()
            ->route('recipes.edit', $recipe)
            ->with('status', 'Recipe created successfully.');
    }

    public function show(Recipe $recipe): View
    {
        $this->authorize('view', $recipe);

        $recipe->load([
            'author',
            'media',
            'dietaryTags',
            'ingredients' => fn ($q) => $q->orderBy('recipe_ingredient.position'),
            'comments' => fn ($q) => $q->with(['author'])->where('is_published', true),
            'comments.replies' => fn ($q) => $q->with('author')->where('is_published', true),
        ]);

        $related = Recipe::public()
            ->where('id', '!=', $recipe->id)
            ->whereHas('dietaryTags', fn ($q) => $q->whereIn('dietary_tags.id', $recipe->dietaryTags->pluck('id')))
            ->latest('published_at')
            ->limit(3)
            ->get();

        $isSaved = auth()->check()
            ? $recipe->savedByUsers()->where('user_id', auth()->id())->exists()
            : false;

        return view('recipes.show', compact('recipe', 'related', 'isSaved'));
    }

    public function edit(Recipe $recipe): View
    {
        $this->authorize('update', $recipe);

        $dietaryTags = DietaryTag::orderBy('name')->get();
        $ingredients = Ingredient::orderBy('name')->get();
        $recipe->load(['dietaryTags', 'ingredients', 'media']);

        return view('recipes.edit', compact('recipe', 'dietaryTags', 'ingredients'));
    }

    public function update(RecipeRequest $request, Recipe $recipe): RedirectResponse
    {
        $this->authorize('update', $recipe);
        $data = $request->validated();

        DB::transaction(function () use ($recipe, $data, $request) {
            $recipe->update([
                'title' => $data['title'],
                'slug' => $this->shouldRefreshSlug($recipe->title, $data['title'])
                    ? $this->generateSlug($data['title'])
                    : $recipe->slug,
                'summary' => $data['summary'] ?? null,
                'description' => $data['description'] ?? null,
                'instructions' => $data['instructions'],
                'prep_minutes' => $data['prep_minutes'] ?? null,
                'cook_minutes' => $data['cook_minutes'] ?? null,
                'servings' => $data['servings'] ?? null,
                'difficulty' => $data['difficulty'] ?? null,
                'is_public' => (bool) ($data['is_public'] ?? false),
                'published_at' => $this->resolvePublishedAt($data, $recipe),
                'nutrition' => $this->filterNutrition($data['nutrition'] ?? null),
            ]);

            $this->syncIngredients($recipe, $data['ingredients']);
            $recipe->dietaryTags()->sync($data['dietary_tags'] ?? []);
            $this->syncMedia($recipe, $request, true);
        });

        return redirect()
            ->route('recipes.edit', $recipe)
            ->with('status', 'Recipe updated successfully.');
    }

    public function destroy(Recipe $recipe): RedirectResponse
    {
        $this->authorize('delete', $recipe);

        DB::transaction(function () use ($recipe) {
            $recipe->dietaryTags()->detach();
            $recipe->ingredients()->detach();

            $recipe->media->each(function ($media) {
                Storage::disk($media->disk)->delete($media->path);
                $media->delete();
            });

            $recipe->savedByUsers()->detach();
            $recipe->delete();
        });

        return redirect()
            ->route('recipes.index')
            ->with('status', 'Recipe deleted successfully.');
    }

    protected function syncIngredients(Recipe $recipe, array $ingredients): void
    {
        $payload = [];

        foreach ($ingredients as $index => $ingredientData) {
            $ingredientId = $ingredientData['id'] ?? null;

            if (! $ingredientId) {
                $name = $ingredientData['name'];
                $slug = Str::slug($name);

                $ingredient = Ingredient::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => Str::title($name),
                        'plural_name' => null,
                    ]
                );

                $ingredientId = $ingredient->id;
            }

            $payload[$ingredientId] = [
                'quantity' => $ingredientData['quantity'] ?? null,
                'unit' => $ingredientData['unit'] ?? null,
                'preparation' => $ingredientData['preparation'] ?? null,
                'position' => $ingredientData['position'] ?? $index,
            ];
        }

        $recipe->ingredients()->sync($payload);
    }

    protected function syncMedia(Recipe $recipe, Request $request, bool $updating = false): void
    {
        $primaryFile = $request->file('media.primary');
        $galleryFiles = $request->file('media.gallery', []);

        if (! $primaryFile && empty($galleryFiles)) {
            return;
        }

        if ($updating) {
            if ($primaryFile) {
                $recipe->media()->where('is_primary', true)->get()->each(function ($media) {
                    Storage::disk($media->disk)->delete($media->path);
                    $media->delete();
                });
            }

            if (! empty($galleryFiles)) {
                $recipe->media()->where('is_primary', false)->get()->each(function ($media) {
                    Storage::disk($media->disk)->delete($media->path);
                    $media->delete();
                });
            }
        }

        if ($primaryFile) {
            $path = $primaryFile->store('recipes', 'public');
            $recipe->media()->create([
                'disk' => 'public',
                'path' => $path,
                'is_primary' => true,
                'position' => 0,
            ]);
        }

        foreach ($galleryFiles as $index => $file) {
            if (! $file) {
                continue;
            }

            $path = $file->store('recipes', 'public');
            $recipe->media()->create([
                'disk' => 'public',
                'path' => $path,
                'is_primary' => false,
                'position' => $index + 1,
            ]);
        }
    }

    protected function resolvePublishedAt(array $data, ?Recipe $recipe = null): ?string
    {
        $isPublic = (bool) ($data['is_public'] ?? false);

        if (! $isPublic) {
            return null;
        }

        if (! empty($data['published_at'])) {
            return $data['published_at'];
        }

        return $recipe?->published_at ?? now();
    }

    protected function filterNutrition(?array $nutrition): ?array
    {
        if (! $nutrition) {
            return null;
        }

        $filtered = array_filter($nutrition, fn ($value) => $value !== null && $value !== '');

        return empty($filtered) ? null : $filtered;
    }

    protected function shouldRefreshSlug(string $current, string $next): bool
    {
        return Str::slug($current) !== Str::slug($next);
    }

    protected function generateSlug(string $title): string
    {
        return Str::slug($title) . '-' . Str::random(6);
    }
}
