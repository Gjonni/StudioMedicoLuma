<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Samba') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="font-semibold mb-2">Spazio disco</h3>
                    @if (! empty($disk))
                        <ul class="text-sm text-gray-700 space-y-1">
                            <li>Filesystem: {{ $disk['filesystem'] }}</li>
                            <li>Dimensione: {{ $disk['size'] }}</li>
                            <li>Usato: {{ $disk['used'] }} ({{ $disk['use_percent'] }})</li>
                            <li>Disponibile: {{ $disk['available'] }}</li>
                            <li>Mount point: {{ $disk['mounted_on'] }}</li>
                        </ul>
                    @else
                        <p class="text-sm text-gray-600">Dati disco non disponibili.</p>
                    @endif
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="font-semibold mb-4">Utenti connessi</h3>

                    @if (empty($users))
                        <p class="text-sm text-gray-600">Nessun utente connesso.</p>
                    @else
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr class="border-b">
                                    <th class="py-2">PID</th>
                                    <th class="py-2">Utente</th>
                                    <th class="py-2">Gruppo</th>
                                    <th class="py-2">Macchina</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr class="border-b">
                                        <td class="py-2">{{ $user['pid'] }}</td>
                                        <td class="py-2">{{ $user['username'] }}</td>
                                        <td class="py-2">{{ $user['group'] }}</td>
                                        <td class="py-2">{{ $user['machine'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
