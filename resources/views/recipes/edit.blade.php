<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Editar</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $recipe->title }}</h2>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('recipes.show', $recipe) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">Veure la recepta</a>
                <form method="POST" action="{{ route('recipes.destroy', $recipe) }}" onsubmit="return confirm('Delete this recipe?');">
                    @csrf
                    @method('DELETE')
                    <x-danger-button>Borrar</x-danger-button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <div class="rounded-lg bg-white p-6 shadow">
                <form method="POST" action="{{ route('recipes.update', $recipe) }}" enctype="multipart/form-data" class="space-y-8">
                    @csrf
                    @method('PUT')

                    @include('recipes._form')

                    <div class="flex items-center justify-end gap-3">
                        <x-secondary-button type="button" onclick="history.back()">Cancela</x-secondary-button>
                        <x-primary-button>Actualitza la recepta</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
