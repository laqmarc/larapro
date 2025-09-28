@php
    $isEdit = isset($recipe);
    $initialIngredients = collect(old('ingredients', $isEdit ? $recipe->ingredients->map(fn ($ingredient) => [
        'id' => $ingredient->pivot->ingredient_id,
        'name' => $ingredient->name,
        'quantity' => $ingredient->pivot->quantity,
        'unit' => $ingredient->pivot->unit,
        'preparation' => $ingredient->pivot->preparation,
        'position' => $ingredient->pivot->position,
    ])->toArray() : []))
        ->whenEmpty(fn ($collection) => $collection->push([
            'id' => null,
            'name' => '',
            'quantity' => null,
            'unit' => '',
            'preparation' => '',
            'position' => 0,
        ]));

    $selectedTags = collect(old('dietary_tags', $isEdit ? $recipe->dietaryTags->pluck('id')->all() : []));
    $difficulties = $difficulties ?? \App\Models\Recipe::DIFFICULTIES;
    $dishTypes = $dishTypes ?? \App\Models\Recipe::DISH_TYPES;
    $instructionSteps = collect(preg_split('/\r?\n\r?\n/', old('instructions', $isEdit ? $recipe->instructions : '')))
        ->map(fn ($step) => trim($step))
        ->filter(fn ($step) => $step !== '')
        ->values()
        ->all();

    if (empty($instructionSteps)) {
        $instructionSteps = [''];
    }
@endphp

<div x-data="recipeForm({
        ingredients: {{ json_encode($initialIngredients->values()) }},
        availableIngredients: {{ json_encode($ingredients->map(fn ($ingredient) => [
            'id' => $ingredient->id,
            'name' => $ingredient->name,
            'slug' => $ingredient->slug,
        ])) }},
        instructions: {{ json_encode($instructionSteps) }},
    })" class="space-y-10">
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="space-y-6">
            <div>
                <x-input-label for="title" value="Títol de la recepta" />
                <x-text-input id="title" name="title" type="text" class="mt-2 block w-full" :value="old('title', $isEdit ? $recipe->title : '')" required />
                <x-input-error :messages="$errors->get('title')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="summary" value="Descripcció curta" />
                <textarea id="summary" name="summary" rows="3" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('summary', $isEdit ? $recipe->summary : '') }}</textarea>
                <x-input-error :messages="$errors->get('summary')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="description" value="Descripcció llarga" />
                <textarea id="description" name="description" rows="4" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $isEdit ? $recipe->description : '') }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <x-input-label for="instructions" value="Intruccions" />
                    <button type="button" class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-3 py-2 text-sm font-semibold text-black shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        x-on:click="addInstruction()">Afegir pas</button>
                </div>
                <div class="mt-4 space-y-4">
                    <template x-for="(step, index) in instructions" :key="index">
                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-semibold text-gray-700">Pas <span x-text="index + 1"></span></h4>
                                <button type="button" class="text-sm text-red-600 hover:underline" x-show="instructions.length > 1"
                                    x-on:click="removeInstruction(index)">Borrar</button>
                            </div>
                            <textarea :id="'instruction-step-' + index" rows="3"
                                class="mt-3 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                x-model="instructions[index]" placeholder="Descriu el pas"></textarea>
                        </div>
                    </template>
                </div>
                <textarea id="instructions" x-ref="instructionsField" name="instructions" class="hidden" required
                    x-effect="$refs.instructionsField.value = instructions.map(step => step.trim()).filter(step => step.length).join('\n\n')"></textarea>
                <x-input-error :messages="$errors->get('instructions')" class="mt-2" />
            </div>
        </div>
        <div class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="prep_minutes" value="Preparació (minuts)" />
                    <x-text-input id="prep_minutes" name="prep_minutes" type="number" min="0" class="mt-2 block w-full" :value="old('prep_minutes', $isEdit ? $recipe->prep_minutes : '')" />
                    <x-input-error :messages="$errors->get('prep_minutes')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="cook_minutes" value="Cuinant (minuts)" />
                    <x-text-input id="cook_minutes" name="cook_minutes" type="number" min="0" class="mt-2 block w-full" :value="old('cook_minutes', $isEdit ? $recipe->cook_minutes : '')" />
                    <x-input-error :messages="$errors->get('cook_minutes')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="servings" value="Racions" />
                    <x-text-input id="servings" name="servings" type="number" min="1" class="mt-2 block w-full" :value="old('servings', $isEdit ? $recipe->servings : '')" />
                    <x-input-error :messages="$errors->get('servings')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="difficulty" value="Dificultat" />
                    <select id="difficulty" name="difficulty" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Seleccionar la difucultat</option>
                        @foreach ($difficulties as $option)
                            <option value="{{ $option }}" @selected(old('difficulty', $isEdit ? $recipe->difficulty : '') === $option)>
                                {{ Str::title($option) }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('difficulty')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="dish_type" value="Tipus de plat" />
                    <select id="dish_type" name="dish_type" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Seleccionar el tipus de plat</option>
                        @foreach ($dishTypes as $option)
                            <option value="{{ $option }}" @selected(old('dish_type', $isEdit ? $recipe->dish_type : '') === $option)>
                                {{ Str::title($option) }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('dish_type')" class="mt-2" />
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-700">Configuració de la publicació</h3>
                <div class="mt-4 space-y-4">
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" name="is_public" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            @checked(old('is_public', $isEdit ? $recipe->is_public : false))>
                        <span class="text-sm text-gray-700">Fes pǧblica la recepta</span>
                    </label>

                    <div>
                        <x-input-label for="published_at" value="Publicar el" />
                        <x-text-input id="published_at" name="published_at" type="datetime-local"
                            class="mt-2 block w-full" :value="old('published_at', optional($isEdit ? $recipe->published_at : null)?->format('Y-m-d\TH:i'))" />
                        <x-input-error :messages="$errors->get('published_at')" class="mt-2" />
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-700">Nutrució (opcional)</h3>
                <div class="mt-4 grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="calories" value="Calories" />
                        <x-text-input id="calories" name="nutrition[calories]" type="number" min="0" class="mt-2 block w-full"
                            :value="old('nutrition.calories', $isEdit ? $recipe->nutrition['calories'] ?? '' : '')" />
                        <x-input-error :messages="$errors->get('nutrition.calories')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="protein" value="Proteina" />
                        <x-text-input id="protein" name="nutrition[protein]" type="number" min="0" step="0.1" class="mt-2 block w-full"
                            :value="old('nutrition.protein', $isEdit ? $recipe->nutrition['protein'] ?? '' : '')" />
                        <x-input-error :messages="$errors->get('nutrition.protein')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="carbs" value="Carbohidrats" />
                        <x-text-input id="carbs" name="nutrition[carbs]" type="number" min="0" step="0.1" class="mt-2 block w-full"
                            :value="old('nutrition.carbs', $isEdit ? $recipe->nutrition['carbs'] ?? '' : '')" />
                        <x-input-error :messages="$errors->get('nutrition.carbs')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="fat" value="Greix" />
                        <x-text-input id="fat" name="nutrition[fat]" type="number" min="0" step="0.1" class="mt-2 block w-full"
                            :value="old('nutrition.fat', $isEdit ? $recipe->nutrition['fat'] ?? '' : '')" />
                        <x-input-error :messages="$errors->get('nutrition.fat')" class="mt-2" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Dietes</h3>
            <div class="mt-3 flex flex-wrap gap-3">
                @foreach ($dietaryTags as $tag)
                    <label class="flex items-center space-x-2 rounded-full border border-gray-200 px-3 py-1 text-sm">
                        <input type="checkbox" name="dietary_tags[]" value="{{ $tag->id }}" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            @checked($selectedTags->contains($tag->id))>
                        <span>{{ $tag->name }}</span>
                    </label>
                @endforeach
            </div>
            <x-input-error :messages="$errors->get('dietary_tags')" class="mt-2" />
        </div>

        <div>
            <h3 class="text-lg font-semibold text-gray-800">Ingredients</h3>
            <div class="space-y-4">
                <template x-for="(ingredient, index) in ingredients" :key="index">
                    <div class="rounded-lg border border-gray-200 p-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-semibold text-gray-700">Ingredient <span x-text="index + 1"></span></h4>
                            <button type="button" class="text-sm text-red-600 hover:underline" x-show="ingredients.length > 1"
                                x-on:click="removeIngredient(index)">Borrar</button>
                        </div>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <label x-bind:for="'ingredient-name-' + index" class="block text-sm font-medium text-gray-700">Nom</label>
                                <input type="hidden" :name="'ingredients[' + index + '][id]'" x-model="ingredient.id">
                                <input :id="'ingredient-name-' + index" type="text" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    :name="'ingredients[' + index + '][name]'" x-model="ingredient.name" list="ingredient-suggestions" required />
                            </div>
                            <div>
                                <label x-bind:for="'ingredient-quantity-' + index" class="block text-sm font-medium text-gray-700">Cantitat (grams)</label>
                                <input :id="'ingredient-quantity-' + index" type="number" step="0.01" min="0"
                                    class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    :name="'ingredients[' + index + '][quantity]'" x-model="ingredient.quantity" />
                            </div>
                            <div>
                                <label x-bind:for="'ingredient-unit-' + index" class="block text-sm font-medium text-gray-700">Unitats</label>
                                <input :id="'ingredient-unit-' + index" type="text"
                                    class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    :name="'ingredients[' + index + '][unit]'" x-model="ingredient.unit" />
                            </div>
                            <div>
                                <label x-bind:for="'ingredient-preparation-' + index" class="block text-sm font-medium text-gray-700">Preparació</label>
                                <input :id="'ingredient-preparation-' + index" type="text"
                                    class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    :name="'ingredients[' + index + '][preparation]'" x-model="ingredient.preparation" />
                            </div>
                        </div>
                    </div>
                </template>
                <x-input-error :messages="$errors->get('ingredients')" class="mt-2" />
            </div>

            <button type="button" class="mt-4 inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-semibold text-black shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                x-on:click="addIngredient()">
                Afegir ingredient
            </button>
        </div>
    </div>

    <datalist id="ingredient-suggestions">
        @foreach ($ingredients as $ingredient)
            <option value="{{ $ingredient->name }}"></option>
        @endforeach
    </datalist>

    <div class="space-y-6">
        <h3 class="text-lg font-semibold text-gray-800">Imatges</h3>
        <div>
            <x-input-label for="media-primary" value="Imatge principal" />
            <input id="media-primary" name="media[primary]" type="file" accept="image/*" class="mt-2 block w-full text-sm text-gray-700" />
            @if ($isEdit && $recipe->media->where('is_primary', true)->isNotEmpty())
                <div class="mt-3 flex flex-wrap gap-3">
                    @foreach ($recipe->media->where('is_primary', true) as $media)
                        <img src="{{ Storage::disk($media->disk)->url($media->path) }}" alt="Primary image" class="h-24 w-24 rounded object-cover" />
                    @endforeach
                </div>
            @endif
            <x-input-error :messages="$errors->get('media.primary')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="media-gallery" value="Imatges de la Galeria" />
            <input id="media-gallery" name="media[gallery][]" type="file" accept="image/*" multiple class="mt-2 block w-full text-sm text-gray-700" />
            @if ($isEdit && $recipe->media->where('is_primary', false)->isNotEmpty())
                <div class="mt-3 flex flex-wrap gap-3">
                    @foreach ($recipe->media->where('is_primary', false) as $media)
                        <img src="{{ Storage::disk($media->disk)->url($media->path) }}" alt="Gallery image" class="h-24 w-24 rounded object-cover" />
                    @endforeach
                </div>
            @endif
            <x-input-error :messages="$errors->get('media.gallery')" class="mt-2" />
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('recipeForm', (initialState) => ({
                ingredients: initialState.ingredients ?? [],
                instructions: Array.isArray(initialState.instructions) && initialState.instructions.length
                    ? [...initialState.instructions]
                    : [''],
                addIngredient() {
                    this.ingredients.push({ id: null, name: '', quantity: null, unit: '', preparation: '', position: this.ingredients.length });
                },
                removeIngredient(index) {
                    this.ingredients.splice(index, 1);
                },
                addInstruction() {
                    this.instructions.push('');
                },
                removeInstruction(index) {
                    if (this.instructions.length === 1) {
                        this.instructions[0] = '';
                        return;
                    }

                    this.instructions.splice(index, 1);
                },
            }));
        });
    </script>
@endpush
