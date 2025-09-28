<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm text-gray-500">{{ $recipe->author->name }} - {{ $recipe->published_at?->diffForHumans() ?? 'Draft' }}</p>
                <h1 class="text-2xl font-semibold leading-tight text-gray-900">{{ $recipe->title }}</h1>
            </div>
            <div class="flex items-center gap-3">
                @auth
                    @if ($isSaved)
                        <form method="POST" action="{{ route('recipes.unsave', $recipe) }}">
                            @csrf
                            @method('DELETE')
                            <x-secondary-button>No la desis</x-secondary-button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('recipes.save', $recipe) }}">
                            @csrf
                            <x-primary-button>Desa la recepta</x-primary-button>
                        </form>
                    @endif
                @endauth
                @if (auth()->id() === $recipe->user_id)
                    <x-secondary-button onclick="window.location='{{ route('recipes.edit', $recipe) }}'">Edita</x-secondary-button>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-5xl space-y-10 px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-lg bg-white shadow">
                @php $primary = $recipe->media->firstWhere('is_primary', true); @endphp
                @if ($primary)
                    <img src="{{ Storage::disk($primary->disk)->url($primary->path) }}" alt="{{ $recipe->title }}" class="h-72 w-full object-cover md:h-96" />
                @endif
                <div class="space-y-6 p-6">
                    @if ($recipe->summary)
                        <p class="text-lg text-gray-700">{{ $recipe->summary }}</p>
                    @endif
                    <div class="flex flex-wrap gap-3 text-sm">
                        <span class="rounded-full bg-indigo-50 px-3 py-1 font-semibold text-indigo-600">{{ Str::title($recipe->difficulty ?? 'Qualsevol') }}</span>
                        @if ($recipe->dish_type)
                            <span class="rounded-full bg-emerald-50 px-3 py-1 font-semibold text-emerald-600">{{ Str::title($recipe->dish_type) }}</span>
                        @endif
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-700">Racions {{ $recipe->servings ?? '���' }}</span>
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-700">Preparació {{ $recipe->prep_minutes ?? '���' }}m</span>
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-700">Cuinant {{ $recipe->cook_minutes ?? '���' }}m</span>
                    </div>
                    @if ($recipe->dietaryTags->isNotEmpty())
                        <div class="flex flex-wrap gap-2 text-sm">
                            @foreach ($recipe->dietaryTags as $tag)
                                <span class="rounded bg-{{ $loop->odd ? 'emerald' : 'sky' }}-100 px-3 py-1 text-{{ $loop->odd ? 'emerald' : 'sky' }}-700">{{ $tag->name }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="grid gap-8 lg:grid-cols-3">
                <div class="space-y-8 lg:col-span-2">
                    <section class="rounded-lg bg-white p-6 shadow">
                        <h2 class="text-lg font-semibold text-gray-900">Ingredients</h2>
                        <ul class="mt-4 space-y-3 text-gray-700">
                            @foreach ($recipe->ingredients as $ingredient)
                                <li class="flex justify-between">
                                    <span>{{ $ingredient->name }} @if ($ingredient->pivot->preparation)<span class="text-sm text-gray-500">({{ $ingredient->pivot->preparation }})</span>@endif</span>
                                    <span class="font-medium">
                                        @php
                                            $quantity = $ingredient->pivot->quantity;
                                            $units = $ingredient->pivot->unit;
                                        @endphp

                                        @if (! is_null($quantity) && $quantity !== '')
                                            {{ rtrim(rtrim(number_format($quantity, 2), '0'), '.') }} gr
                                        @endif

                                        @if (! is_null($units) && $units !== '')
                                            @if (! is_null($quantity) && $quantity !== '')
                                                <span aria-hidden="true">&middot;</span>
                                            @endif
                                            @if (is_numeric($units))
                                                {{ rtrim(rtrim(number_format($units, 2), '0'), '.') }} u
                                            @else
                                                {{ $units }}
                                            @endif
                                        @endif
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </section>

                    <section class="rounded-lg bg-white p-6 shadow">
                        <h2 class="text-lg font-semibold text-gray-900">Instruccions</h2>
                        <div class="mt-4 space-y-6 text-gray-700">
                            @foreach (preg_split('/\n\n+/', $recipe->instructions) as $step)
                                <div class="flex gap-4">
                                    <span class="mt-1 h-8 w-8 flex-shrink-0 rounded-full bg-indigo-100 text-center text-sm font-semibold leading-8 text-indigo-700">{{ $loop->iteration }}</span>
                                    <p>{{ $step }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    @if ($recipe->media->where('is_primary', false)->isNotEmpty())
                        <section class="rounded-lg bg-white p-6 shadow">
                            <h2 class="text-lg font-semibold text-gray-900">Galeria</h2>
                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                @foreach ($recipe->media->where('is_primary', false) as $media)
                                    <img src="{{ Storage::disk($media->disk)->url($media->path) }}" alt="Gallery image" class="h-48 w-full rounded object-cover" />
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>

                <aside class="space-y-8">
                    @if ($recipe->nutrition)
                        <section class="rounded-lg bg-white p-6 shadow">
                            <h2 class="text-lg font-semibold text-gray-900">Nutrució</h2>
                            <dl class="mt-4 space-y-2 text-sm text-gray-700">
                                @foreach ($recipe->nutrition as $key => $value)
                                    <div class="flex justify-between">
                                        <dt class="capitalize text-gray-500">{{ $key }}</dt>
                                        <dd class="font-medium">{{ $value }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </section>
                    @endif

                    <section class="rounded-lg bg-white p-6 shadow">
                        <h2 class="text-lg font-semibold text-gray-900">Comentaris</h2>
                        <div class="mt-4 space-y-6">
                            @foreach ($recipe->comments as $comment)
                                <article class="rounded border border-gray-100 p-4">
                                    <div class="flex items-center justify-between text-sm text-gray-500">
                                        <span>{{ $comment->author->name }}</span>
                                        <span>{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="mt-2 text-gray-700">{{ $comment->body }}</p>
                                    @if ($comment->rating)
                                        <p class="mt-2 text-sm text-indigo-600">Rating: {{ $comment->rating }}/5</p>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </section>

                    @if ($related->isNotEmpty())
                        <section class="rounded-lg bg-white p-6 shadow">
                            <h2 class="text-lg font-semibold text-gray-900">Potser també t'agradaria</h2>
                            <div class="mt-4 space-y-4">
                                @foreach ($related as $item)
                                    <a href="{{ route('recipes.show', $item) }}" class="block rounded bg-gray-50 p-4 hover:bg-gray-100">
                                        <p class="text-sm font-semibold text-gray-900">{{ $item->title }}</p>
                                        <p class="mt-1 text-xs text-gray-600">{{ Str::limit($item->summary ?? $item->description, 80) }}</p>
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>

