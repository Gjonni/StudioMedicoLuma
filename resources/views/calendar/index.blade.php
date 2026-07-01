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

    @vite('resources/js/calendar.js')
</x-app-layout>
