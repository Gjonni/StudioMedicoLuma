<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $user->exists ? 'Modifica utente' : 'Nuovo utente' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if ($errors->any())
                        <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
                            <ul class="list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ $user->exists ? route('settings.users.update', $user) : route('settings.users.store') }}" class="space-y-4">
                        @csrf
                        @if ($user->exists)
                            @method('PUT')
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nome</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Password {{ $user->exists ? '(lascia vuoto per non modificarla)' : '' }}
                            </label>
                            <input type="password" name="password"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ruoli</label>
                            <div class="space-y-1">
                                @foreach ($roles as $role)
                                    <label class="flex items-center gap-2 text-sm">
                                        <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                               @checked($user->roles->contains('name', $role->name))>
                                        {{ $role->name }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Permessi diretti sui moduli</label>
                            <div class="space-y-1">
                                @foreach ($permissions as $permission)
                                    <label class="flex items-center gap-2 text-sm">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                               @checked($user->exists && $user->permissions->contains('name', $permission->name))>
                                        {{ $permission->name }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit" class="bg-sky-600 text-white px-4 py-2 rounded hover:bg-sky-700">
                                Salva
                            </button>
                            <a href="{{ route('settings.users.index') }}" class="text-sm text-gray-600 hover:underline">Annulla</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
