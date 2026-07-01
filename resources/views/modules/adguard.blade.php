<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('AdGuard Home') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-green-100 text-green-800 p-4 rounded">{{ session('status') }}</div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 text-red-800 p-4 rounded">{{ session('error') }}</div>
            @endif

            @if (! empty($connectionError))
                <div class="bg-red-100 text-red-800 p-4 rounded">{{ $connectionError }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="font-semibold mb-4">Protezione DNS</h3>
                    @php $enabled = $status['protection_enabled'] ?? false; @endphp
                    <p class="text-sm mb-4">
                        Stato attuale:
                        <span class="{{ $enabled ? 'text-green-600' : 'text-red-600' }} font-semibold">
                            {{ $enabled ? 'Attiva' : 'Disattiva' }}
                        </span>
                    </p>
                    <form method="POST" action="{{ route('adguard.protection') }}">
                        @csrf
                        <input type="hidden" name="enabled" value="{{ $enabled ? '0' : '1' }}">
                        <button type="submit" class="bg-sky-600 text-white px-4 py-2 rounded hover:bg-sky-700">
                            {{ $enabled ? 'Disattiva protezione' : 'Attiva protezione' }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="font-semibold mb-4">Statistiche</h3>
                    <ul class="text-sm text-gray-700 space-y-1">
                        <li>Query totali: {{ $stats['num_dns_queries'] ?? '?' }}</li>
                        <li>Bloccate: {{ $stats['num_blocked_filtering'] ?? '?' }}</li>
                    </ul>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="font-semibold mb-4">Query log recenti</h3>

                    @if (empty($queryLog))
                        <p class="text-sm text-gray-600">Nessuna query disponibile.</p>
                    @else
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr class="border-b">
                                    <th class="py-2">Ora</th>
                                    <th class="py-2">Dominio</th>
                                    <th class="py-2">Client</th>
                                    <th class="py-2">Esito</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($queryLog as $entry)
                                    <tr class="border-b">
                                        <td class="py-2">{{ $entry['time'] ?? '' }}</td>
                                        <td class="py-2">{{ $entry['question']['name'] ?? '' }}</td>
                                        <td class="py-2">{{ $entry['client'] ?? '' }}</td>
                                        <td class="py-2">{{ $entry['reason'] ?? '' }}</td>
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
