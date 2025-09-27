<?php

namespace Tests\Feature;

use App\Models\DietaryTag;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RecipeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_recipe_with_media_and_ingredients(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $tag = DietaryTag::factory()->create();
        $ingredient = Ingredient::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('recipes.store'), [
                'title' => 'Test Recipe',
                'summary' => 'Short summary',
                'description' => 'Detailed description',
                'instructions' => "Step 1\n\nStep 2",
                'prep_minutes' => 10,
                'cook_minutes' => 20,
                'servings' => 4,
                'difficulty' => 'easy',
                'is_public' => true,
                'dietary_tags' => [$tag->id],
                'ingredients' => [
                    [
                        'id' => $ingredient->id,
                        'quantity' => 2,
                        'unit' => 'cups',
                        'preparation' => 'chopped',
                        'position' => 0,
                    ],
                ],
                'media' => [
                    'primary' => UploadedFile::fake()->create('primary.jpg', 100, 'image/jpeg'),
                    'gallery' => [UploadedFile::fake()->create('gallery.jpg', 100, 'image/jpeg')],
                ],
            ]);

        $recipe = Recipe::first();

        $response->assertRedirect(route('recipes.edit', $recipe));
        $this->assertNotNull($recipe);
        $this->assertSame('Test Recipe', $recipe->title);
        $this->assertEquals($user->id, $recipe->user_id);
        $this->assertTrue($recipe->dietaryTags->contains($tag));
        $this->assertCount(2, $recipe->media);
        Storage::disk('public')->assertExists($recipe->media->first()->path);
    }

    public function test_non_owner_cannot_edit_recipe(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $recipe = Recipe::factory()->for($owner, 'author')->create();

        $this
            ->actingAs($other)
            ->get(route('recipes.edit', $recipe))
            ->assertForbidden();
    }

    public function test_owner_can_update_recipe(): void
    {
        $owner = User::factory()->create();
        $recipe = Recipe::factory()->for($owner, 'author')->create([
            'title' => 'Original Title',
            'is_public' => false,
        ]);
        $ingredient = Ingredient::factory()->create();

        $response = $this
            ->actingAs($owner)
            ->patch(route('recipes.update', $recipe), [
                'title' => 'Updated Title',
                'summary' => null,
                'description' => 'Updated description',
                'instructions' => 'Updated instructions',
                'difficulty' => 'medium',
                'prep_minutes' => 5,
                'cook_minutes' => 15,
                'servings' => 2,
                'dietary_tags' => [],
                'ingredients' => [
                    [
                        'id' => $ingredient->id,
                        'quantity' => 1,
                        'unit' => 'g',
                        'preparation' => null,
                        'position' => 0,
                    ],
                ],
            ]);

        $response->assertRedirect(route('recipes.edit', $recipe));
        $recipe->refresh();

        $this->assertSame('Updated Title', $recipe->title);
        $this->assertEquals(1, $recipe->ingredients()->count());
    }

    public function test_private_recipe_is_not_visible_to_guests_or_other_users(): void
    {
        $owner = User::factory()->create();
        $recipe = Recipe::factory()->for($owner, 'author')->create([
            'is_public' => false,
        ]);

        $this->get(route('recipes.show', $recipe))->assertForbidden();

        $otherUser = User::factory()->create();
        $this
            ->actingAs($otherUser)
            ->get(route('recipes.show', $recipe))
            ->assertForbidden();

        $this
            ->actingAs($owner)
            ->get(route('recipes.show', $recipe))
            ->assertOk();
    }

    public function test_user_can_save_and_unsave_public_recipe(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['is_public' => true]);

        $this
            ->actingAs($user)
            ->post(route('recipes.save', $recipe))
            ->assertRedirect();

        $this->assertDatabaseHas('saved_recipes', [
            'user_id' => $user->id,
            'recipe_id' => $recipe->id,
        ]);

        $this
            ->actingAs($user)
            ->delete(route('recipes.unsave', $recipe))
            ->assertRedirect();

        $this->assertDatabaseMissing('saved_recipes', [
            'user_id' => $user->id,
            'recipe_id' => $recipe->id,
        ]);
    }

    public function test_user_cannot_save_private_recipe_they_cannot_view(): void
    {
        $owner = User::factory()->create();
        $privateRecipe = Recipe::factory()->for($owner, 'author')->create(['is_public' => false]);
        $otherUser = User::factory()->create();

        $this
            ->actingAs($otherUser)
            ->post(route('recipes.save', $privateRecipe))
            ->assertForbidden();

        $this->assertDatabaseMissing('saved_recipes', [
            'user_id' => $otherUser->id,
            'recipe_id' => $privateRecipe->id,
        ]);
    }
}

