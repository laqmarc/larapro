<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Crea una recepta</h2>
            <a href="{{ route('recipes.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">Torna a les receptes</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <div class="rounded-lg bg-white p-6 shadow">
                <form method="POST" action="{{ route('recipes.store') }}" enctype="multipart/form-data" class="space-y-8">
                    @csrf

                    @include('recipes._form')

                    <div class="flex items-center justify-end gap-3">
                        <x-secondary-button type="reset">Borra</x-secondary-button>
                        <x-primary-button>Guarda la recepta</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
