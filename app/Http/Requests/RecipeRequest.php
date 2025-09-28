<?php

namespace App\Http\Requests;

use App\Models\Recipe;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:300'],
            'description' => ['nullable', 'string'],
            'instructions' => ['required', 'string'],
            'prep_minutes' => ['nullable', 'integer', 'min:0'],
            'cook_minutes' => ['nullable', 'integer', 'min:0'],
            'servings' => ['nullable', 'integer', 'min:1', 'max:24'],
            'difficulty' => ['nullable', Rule::in(Recipe::DIFFICULTIES)],
            'dish_type' => ['nullable', Rule::in(Recipe::DISH_TYPES)],
            'is_public' => ['sometimes', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'nutrition' => ['nullable', 'array'],
            'nutrition.calories' => ['nullable', 'numeric', 'min:0'],
            'nutrition.protein' => ['nullable', 'numeric', 'min:0'],
            'nutrition.carbs' => ['nullable', 'numeric', 'min:0'],
            'nutrition.fat' => ['nullable', 'numeric', 'min:0'],

            'dietary_tags' => ['nullable', 'array'],
            'dietary_tags.*' => ['integer', 'exists:dietary_tags,id'],

            'ingredients' => ['required', 'array', 'min:1'],
            'ingredients.*.id' => ['nullable', 'integer', 'exists:ingredients,id'],
            'ingredients.*.name' => ['required_without:ingredients.*.id', 'string', 'max:255'],
            'ingredients.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'ingredients.*.unit' => ['nullable', 'string', 'max:50'],
            'ingredients.*.preparation' => ['nullable', 'string', 'max:255'],
            'ingredients.*.position' => ['nullable', 'integer', 'min:0'],

            'media.primary' => ['nullable', 'image', 'max:5120'],
            'media.gallery' => ['nullable', 'array'],
            'media.gallery.*' => ['image', 'max:5120'],
        ];
    }
}
