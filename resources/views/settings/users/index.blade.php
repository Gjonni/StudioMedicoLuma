<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Utenti') }}</h2>
            <a href="{{ route('settings.users.create') }}" class="bg-sky-600 text-white px-4 py-2 rounded text-sm hover:bg-sky-700">
                Nuovo utente
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
                                <th class="py-2">Email</th>
                                <th class="py-2">Ruoli</th>
                                <th class="py-2">Permessi diretti</th>
                                <th class="py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr class="border-b">
                                    <td class="py-2">{{ $user->name }}</td>
                                    <td class="py-2">{{ $user->email }}</td>
                                    <td class="py-2">{{ $user->roles->pluck('name')->join(', ') ?: '—' }}</td>
                                    <td class="py-2">{{ $user->permissions->pluck('name')->join(', ') ?: '—' }}</td>
                                    <td class="py-2 space-x-2">
                                        <a href="{{ route('settings.users.edit', $user) }}" class="text-sky-600 hover:underline">Modifica</a>
                                        @if ($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('settings.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Eliminare questo utente?');">
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
