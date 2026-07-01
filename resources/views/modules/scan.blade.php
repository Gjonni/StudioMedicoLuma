<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Scansione') }}</h2>
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
                    <h3 class="font-semibold mb-2">Dispositivi rilevati</h3>
                    <pre class="text-sm text-gray-700 whitespace-pre-wrap">{{ $devices }}</pre>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="font-semibold mb-4">Nuova scansione</h3>
                    <p class="text-sm text-gray-600 mb-4">Il documento verrà salvato direttamente nella cartella condivisa via Samba.</p>
                    <form method="POST" action="{{ route('scan.store') }}">
                        @csrf
                        <button type="submit" class="bg-sky-600 text-white px-4 py-2 rounded hover:bg-sky-700">
                            Scansiona e salva nello share
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
