<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Stampa') }}</h2>
            <a href="{{ route('print.setup') }}" class="text-sm text-sky-600 hover:underline">
                Configura stampante sul tuo dispositivo &rarr;
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
                    <h3 class="font-semibold mb-2">Stato stampante</h3>
                    <pre class="text-sm text-gray-700 whitespace-pre-wrap">{{ $status }}</pre>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="font-semibold mb-4">Coda di stampa</h3>

                    @if (empty($jobs))
                        <p class="text-sm text-gray-600">Nessun job in coda.</p>
                    @else
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr class="border-b">
                                    <th class="py-2">Job</th>
                                    <th class="py-2">Utente</th>
                                    <th class="py-2">Dimensione</th>
                                    <th class="py-2">Inviato</th>
                                    <th class="py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($jobs as $job)
                                    <tr class="border-b">
                                        <td class="py-2">{{ $job['id'] }}</td>
                                        <td class="py-2">{{ $job['user'] }}</td>
                                        <td class="py-2">{{ $job['size'] }}</td>
                                        <td class="py-2">{{ $job['submitted_at'] }}</td>
                                        <td class="py-2">
                                            <form method="POST" action="{{ route('print.cancel', $job['id']) }}">
                                                @csrf
                                                <button type="submit" class="text-red-600 hover:underline">Annulla</button>
                                            </form>
                                        </td>
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
