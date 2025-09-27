<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\SavedRecipeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('recipes', RecipeController::class)->except(['index', 'show']);

    Route::post('recipes/{recipe}/save', [SavedRecipeController::class, 'store'])
        ->name('recipes.save');
    Route::delete('recipes/{recipe}/save', [SavedRecipeController::class, 'destroy'])
        ->name('recipes.unsave');
    Route::get('saved-recipes', [SavedRecipeController::class, 'index'])
        ->name('recipes.saved');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::resource('recipes', RecipeController::class)->only(['index', 'show']);
Route::get('/', [RecipeController::class, 'index']);

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/auth.php';
