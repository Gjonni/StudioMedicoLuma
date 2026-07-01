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
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-green-100 text-green-800 p-4 rounded">{{ session('status') }}</div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 text-red-800 p-4 rounded">{{ session('error') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="border-b">
                                <th class="py-2">Nome</th>
                                <th class="py-2">Permessi</th>
                                <th class="py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $role)
                                <tr class="border-b">
                                    <td class="py-2">{{ $role->name }}</td>
                                    <td class="py-2">{{ $role->permissions->pluck('name')->join(', ') ?: '—' }}</td>
                                    <td class="py-2 space-x-2">
                                        <a href="{{ route('settings.roles.edit', $role) }}" class="text-sky-600 hover:underline">Modifica</a>
                                        @if ($role->name !== 'admin')
                                            <form method="POST" action="{{ route('settings.roles.destroy', $role) }}" class="inline" onsubmit="return confirm('Eliminare questo ruolo?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:underline">Elimina</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
