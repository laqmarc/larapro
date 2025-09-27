<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeMedia extends Model
{
    /** @use HasFactory<\Database\Factories\RecipeMediaFactory> */
    use HasFactory;

    protected $fillable = [
        'recipe_id',
        'disk',
        'path',
        'caption',
        'is_primary',
        'position',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }
}
