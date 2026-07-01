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
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

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

                    @if (empty($rows))
                        <p class="text-sm text-gray-600">Nessun job in coda.</p>
                    @else
                        <x-data-table :columns="$columns" :rows="$rows" />
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
