<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Ruoli') }}</h2>
            <a href="{{ route('settings.roles.create') }}" class="bg-sky-600 text-white px-4 py-2 rounded text-sm hover:bg-sky-700">
                Nuovo ruolo
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-green-100 text-green-800 p-4 rounded">{{ session('status') }}</div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 text-red-800 p-4 rounded">{{ session('error') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <x-data-table :columns="$columns" :rows="$rows" />
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
