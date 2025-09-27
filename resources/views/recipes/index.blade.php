<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Descobreix receptes</h2>
            @auth
                <x-primary-button onclick="window.location='{{ route('recipes.create') }}'">Nova recepta</x-primary-button>
            @endauth
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8 mt-4 mb-4">
            <form method="GET" class="grid gap-6 rounded-lg bg-white p-6 shadow lg:grid-cols-4">
                <div class="lg:col-span-2 mb-4">
                    <x-input-label for="search" value="Buscar" />
                    <div class="mt-2 flex">
                        <x-text-input id="search" name="search" type="search" class="block w-full flex-1" :value="request('search')" placeholder="Buscar receptes" />
                        <x-primary-button class="ml-3">Buscar</x-primary-button>
                    </div>
                </div>
                <div>
                    <x-input-label for="difficulty" value="Dificultat" />
                    <select id="difficulty" name="difficulty" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Qualsevol</option>
                        @foreach ($difficulties as $option)
                            <option value="{{ $option }}" @selected(request('difficulty') === $option)>{{ Str::title($option) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="max_time" value="Temps total màxim (minuts)" />
                    <x-text-input id="max_time" name="max_time" type="number" min="10" class="mt-2 block w-full" :value="request('max_time')" />
                </div>
                <div class="lg:col-span-2 mb-4">
                    <x-input-label value="Etiquetes dietètiques" />
                    <div class="fw gap-2 mt-3 ">
                        @foreach ($dietaryTags as $tag)
                            <label class="flex items-center space-x-2 rounded-full border border-gray-200 px-3 py-1 text-sm ">
                                <input type="checkbox" name="dietary[]" value="{{ $tag->slug }}" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" @checked(collect(request('dietary', []))->contains($tag->slug))>
                                <span class="m-5">{{ $tag->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <x-input-label for="ingredient" value="Ingredient" />
                    <select id="ingredient" name="ingredient" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Qualsevol</option>
                        @foreach ($popularIngredients as $ingredient)
                            <option value="{{ $ingredient->slug }}" @selected(request('ingredient') === $ingredient->slug)>{{ $ingredient->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center space-x-4">
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="saved" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" @checked(request('saved'))>
                        <span class="text-sm text-gray-700">Guardats</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="mine" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" @checked(request('mine'))>
                        <span class="text-sm text-gray-700">Les meves receptes</span>
                    </label>
                </div>
                <div class="flex items-center justify-end lg:col-span-4">
                    <a href="{{ route('recipes.index') }}" class="text-sm font-semibold text-gray-500 hover:text-gray-700">Resetejar filtres</a>
                </div>
            </form>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($recipes as $recipe)
                    <article class="flex h-full flex-row overflow-hidden rounded-lg bg-white shadow mt-4">
                        <a href="{{ route('recipes.show', $recipe) }}" class="block aspect-video bg-gray-100">
                            @php $primary = $recipe->media->firstWhere('is_primary', true); @endphp
                            @if ($primary)
                                <img src="{{ Storage::disk($primary->disk)->url($primary->path) }}" alt="{{ $recipe->title }}" title="{{ $recipe->title }}" style="width:100%;max-width: 250px;" class="unters" />
                            @endif
                        </a>
                        <div class="flex flex-1 flex-col space-y-4 p-4">
                            <div class="flex items-start justify-between">
                                <a href="{{ route('recipes.show', $recipe) }}" class="text-lg font-semibold text-gray-900">{{ $recipe->title }}</a>
                                <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600">{{ Str::title($recipe->difficulty ?? 'Any') }}</span>
                            </div>
                            <p class="line-clamp-3 text-sm text-gray-600">{{ $recipe->summary ?? Str::limit($recipe->description, 120) }}</p>
                            <div class="mt-auto space-y-2 text-sm text-gray-500">
                                <p>Recepta feta per {{ $recipe->author->name }}</p>
                                <p>
                                    <span>{{ $recipe->comments_count }} Comentaris</span>
                                    <span class="mx-2 h-1 w-1 rounded-full bg-gray-300 inline-block"></span>
                                    <span>{{ $recipe->saved_by_users_count }} Cops guardada</span>
                                </p>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="lg:col-span-3">
                        <div class="rounded-lg bg-white p-10 text-center shadow">
                            <h3 class="text-lg font-semibold text-gray-800">No s'han trobat receptes</h3>
                            <p class="mt-2 text-sm text-gray-600">Prova d'ajustar els filtres o torna-ho a comprovar més tard.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            <div>
                {{ $recipes->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
