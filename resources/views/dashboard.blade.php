<x-app-layout>
    <div class="pb-12">

        {{-- Hero --}}
        <div class="bg-gradient-to-r from-sky-700 via-sky-600 to-teal-500 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                <h1 class="text-2xl sm:text-3xl font-bold">Bentornato, {{ Auth::user()->name }}</h1>
                <p class="text-sky-100 mt-1">{{ now()->translatedFormat('l j F Y') }} — Pannello di controllo Studio Luma</p>

                <div class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @can('modulo-stampa')
                        <div class="bg-white/10 rounded-lg p-4">
                            <p class="text-2xl font-bold">{{ $printJobsCount }}</p>
                            <p class="text-xs text-sky-100">Job in coda</p>
                        </div>
                    @endcan
                    @can('modulo-samba')
                        <div class="bg-white/10 rounded-lg p-4">
                            <p class="text-2xl font-bold">{{ $diskUsage['use_percent'] ?? '—' }}</p>
                            <p class="text-xs text-sky-100">Spazio disco usato</p>
                        </div>
                    @endcan
                    @can('modulo-adguard')
                        <div class="bg-white/10 rounded-lg p-4">
                            <p class="text-2xl font-bold">{{ $adguardEnabled ? 'ON' : 'OFF' }}</p>
                            <p class="text-xs text-sky-100">Protezione DNS</p>
                        </div>
                    @endcan
                    <div class="bg-white/10 rounded-lg p-4">
                        <p class="text-2xl font-bold">{{ array_sum($scansByUser) }}</p>
                        <p class="text-xs text-sky-100">Scansioni totali</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Tile dei moduli --}}
            <div class="grid grid-cols-3 sm:grid-cols-5 gap-3 -mt-6">
                @can('modulo-stampa')
                    <x-module-tile :href="route('print.index')" label="Stampa" color="sky">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.32 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86 109.397 109.397 0 00-7.284 0 2.056 2.056 0 00-1.58.86A17.9 17.9 0 002.909 16.876c-.038.62.47 1.124 1.09 1.124H6.34" />
                        </svg>
                    </x-module-tile>
                @endcan
                @can('modulo-scansione')
                    <x-module-tile :href="route('scan.index')" label="Scansione" color="emerald">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-4.352 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                        </svg>
                    </x-module-tile>
                @endcan
                @can('modulo-samba')
                    <x-module-tile :href="route('samba.index')" label="Samba" color="violet">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-19.5 0v6a2.25 2.25 0 002.25 2.25h15a2.25 2.25 0 002.25-2.25v-6m-19.5 0h19.5M12 9.75V4.5a1.5 1.5 0 011.5-1.5h1.086a1.5 1.5 0 011.06.44l1.415 1.415a1.5 1.5 0 001.06.44h1.879a1.5 1.5 0 011.5 1.5v3.75" />
                        </svg>
                    </x-module-tile>
                @endcan
                @can('modulo-adguard')
                    <x-module-tile :href="route('adguard.index')" label="AdGuard" color="amber">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.75h-.152c-3.196 0-6.1-1.248-8.25-3.286z" />
                        </svg>
                    </x-module-tile>
                @endcan
                @can('modulo-calendario')
                    <x-module-tile :href="route('calendar.index')" label="Calendario" color="sky">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                    </x-module-tile>
                @endcan
            </div>

            {{-- Grafici --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">

                @can('modulo-stampa')
                    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100">
                        <div class="p-6 text-gray-900">
                            <h3 class="font-semibold mb-1">Stampe in coda per utente</h3>
                            <p class="text-xs text-gray-500 mb-4">Utente di rete (IPP), non utente dashboard.</p>
                            <x-bar-chart :data="$printsByUser" color="#0284c7" label="Job" />
                        </div>
                    </div>
                @endcan

                @can('modulo-scansione')
                    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100">
                        <div class="p-6 text-gray-900">
                            <h3 class="font-semibold mb-1">Scansioni per utente</h3>
                            <p class="text-xs text-gray-500 mb-4">Azioni eseguite dalla dashboard.</p>
                            <x-bar-chart :data="$scansByUser" color="#10b981" label="Scansioni" />
                        </div>
                    </div>
                @endcan

                @can('modulo-samba')
                    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100">
                        <div class="p-6 text-gray-900">
                            <h3 class="font-semibold mb-1">Spazio disco</h3>
                            <p class="text-xs text-gray-500 mb-4">Globale sullo share USB (Samba non espone quote per utente).</p>
                            @if (! empty($diskUsage))
                                @php
                                    $percent = (int) rtrim($diskUsage['use_percent'] ?? '0', '%');
                                @endphp
                                <x-doughnut-chart :used="$percent" :free="100 - $percent"
                                    :color-used="$percent >= 90 ? '#ef4444' : '#f59e0b'" />
                                <p class="text-sm text-gray-600 text-center -mt-2">{{ $diskUsage['used'] ?? '?' }} / {{ $diskUsage['size'] ?? '?' }}</p>
                            @else
                                <p class="text-sm text-gray-600">Dati disco non disponibili</p>
                            @endif
                        </div>
                    </div>
                @endcan

            </div>
        </div>
    </div>
</x-app-layout>
