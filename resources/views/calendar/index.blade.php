<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Calendario') }}</h2>

            @if ($googleConnected)
                <form method="POST" action="{{ route('google-calendar.disconnect') }}">
                    @csrf
                    <button type="submit" class="text-sm text-red-600 hover:underline">
                        Disconnetti Google Calendar
                    </button>
                </form>
            @else
                <a href="{{ route('google-calendar.connect') }}" class="text-sm text-sky-600 hover:underline">
                    Collega Google Calendar &rarr;
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-green-100 text-green-800 p-4 rounded">{{ session('status') }}</div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 text-red-800 p-4 rounded">{{ session('error') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-xs text-gray-500 mb-4">
                    Seleziona un intervallo per creare un evento, trascina per spostarlo, clicca per modificarlo o eliminarlo.
                    @if ($googleConnected)
                        Sincronizzato con il tuo Google Calendar.
                    @endif
                </p>
                <div id="calendar"
                     data-events-url="{{ route('calendar.events') }}"
                     data-store-url="{{ route('calendar.store') }}"></div>
            </div>

        </div>
    </div>

    {{-- Modal creazione/modifica evento --}}
    <div id="event-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 px-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6">
            <h3 id="event-modal-title" class="font-semibold text-lg text-gray-900 mb-4">Nuovo evento</h3>
            <label class="block text-sm text-gray-700 mb-1">Titolo</label>
            <input id="event-modal-input" type="text"
                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-sky-500 focus:ring-sky-500 mb-6">
            <div class="flex items-center gap-2">
                <button id="event-modal-delete" type="button" class="hidden text-sm text-red-600 hover:underline mr-auto">
                    Elimina
                </button>
                <button id="event-modal-cancel" type="button" class="px-3 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md">
                    Annulla
                </button>
                <button id="event-modal-confirm" type="button" class="px-4 py-2 text-sm bg-sky-600 text-white rounded-md hover:bg-sky-700">
                    Salva
                </button>
            </div>
        </div>
    </div>

    {{-- Toast di esito --}}
    <div id="toast-container" class="fixed bottom-4 right-4 z-50 flex flex-col gap-2"></div>

    @vite('resources/js/calendar.js')
</x-app-layout>
