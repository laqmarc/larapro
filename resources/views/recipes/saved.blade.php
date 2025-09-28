<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Saved Recipes</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($recipes as $recipe)
                    <article class="flex h-full flex-col overflow-hidden rounded-lg bg-white shadow">
                        <a href="{{ route('recipes.show', $recipe) }}" class="block aspect-video bg-gray-100">
                            @php $primary = $recipe->media->firstWhere('is_primary', true); @endphp
                            @if ($primary)
                                <img src="{{ Storage::disk($primary->disk)->url($primary->path) }}" alt="{{ $recipe->title }}" class="h-full w-full object-cover" />
                            @endif
                        </a>
                        <div class="flex flex-1 flex-col justify-between p-5">
                            <div>
                                <a href="{{ route('recipes.show', $recipe) }}" class="text-lg font-semibold text-gray-900">{{ $recipe->title }}</a>
                                <p class="mt-2 line-clamp-3 text-sm text-gray-600">{{ $recipe->summary ?? Str::limit($recipe->description, 120) }}</p>
                            </div>
                            <div class="mt-4 flex items-center justify-between text-sm text-gray-500">
                                <span>Per {{ $recipe->author->name }}</span>
                                <form method="POST" action="{{ route('recipes.unsave', $recipe) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-sm font-semibold text-red-600 hover:text-red-500">Borrar</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="lg:col-span-3">
                        <div class="rounded-lg bg-white p-10 text-center shadow">
                            <h3 class="text-lg font-semibold text-gray-800">Encara no s'ha desat res</h3>
                            <p class="mt-2 text-sm text-gray-600">Navega per les receptes i toca Desa per crear la teva llista.</p>
                            <x-primary-button class="mt-4" onclick="window.location='{{ route('recipes.index') }}'">Navega per les receptes</x-primary-button>
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
