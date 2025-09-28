<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipe extends Model
{
    /** @use HasFactory<\Database\Factories\RecipeFactory> */
    use HasFactory;
    use SoftDeletes;

    public const DIFFICULTIES = ['facil', 'mitg', 'dificil'];

    public const DISH_TYPES = ['primer plat', 'segon plat', 'postres', 'esmorzar', 'berenar'];

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'summary',
        'description',
        'instructions',
        'prep_minutes',
        'cook_minutes',
        'servings',
        'difficulty',
        'dish_type',
        'is_public',
        'published_at',
        'nutrition',
    ];
*ryabTg6ysx95W?Z
    protected $casts = [
        'is_public' => 'boolean',
        'published_at' => 'datetime',
        'nutrition' => 'array',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'recipe_ingredient')
            ->using(RecipeIngredient::class)
            ->withPivot(['id', 'quantity', 'unit', 'preparation', 'position'])
            ->withTimestamps()
            ->orderByPivot('position');
    }

    public function dietaryTags(): BelongsToMany
    {
        return $this->belongsToMany(DietaryTag::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(RecipeMedia::class)->orderBy('position');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)
            ->whereNull('parent_id')
            ->orderByDesc('created_at');
    }

    public function savedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'saved_recipes')
            ->using(SavedRecipe::class)
            ->withPivot('created_at');
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }
}
