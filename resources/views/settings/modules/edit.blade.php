<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Configurazione moduli') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if (session('status'))
                        <div class="bg-green-100 text-green-800 p-4 rounded mb-4">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
                            <ul class="list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('settings.modules.update') }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <h3 class="font-semibold text-sm text-gray-700 mb-3">Stampa (CUPS)</h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm text-gray-700">Host CUPS</label>
                                    <input type="text" name="cups_host" value="{{ old('cups_host', $settings->cups_host) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-700">Nome coda stampante</label>
                                    <input type="text" name="cups_printer_name" value="{{ old('cups_printer_name', $settings->cups_printer_name) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="font-semibold text-sm text-gray-700 mb-3">Scansione (SANE)</h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm text-gray-700">Percorso di salvataggio</label>
                                    <input type="text" name="scan_output_path" value="{{ old('scan_output_path', $settings->scan_output_path) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-700">Dispositivo (opzionale)</label>
                                    <input type="text" name="scan_device" value="{{ old('scan_device', $settings->scan_device) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-700">Host saned</label>
                                    <input type="text" name="saned_host" value="{{ old('saned_host', $settings->saned_host) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="font-semibold text-sm text-gray-700 mb-3">Samba</h3>
                            <div>
                                <label class="block text-sm text-gray-700">Percorso share</label>
                                <input type="text" name="samba_share_path" value="{{ old('samba_share_path', $settings->samba_share_path) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                        </div>

                        <div>
                            <h3 class="font-semibold text-sm text-gray-700 mb-3">AdGuard Home</h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm text-gray-700">Host (con schema, es. http://localhost:3000)</label>
                                    <input type="text" name="adguard_host" value="{{ old('adguard_host', $settings->adguard_host) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-700">Utente</label>
                                    <input type="text" name="adguard_username" value="{{ old('adguard_username', $settings->adguard_username) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-700">Password (lascia vuoto per non modificarla)</label>
                                    <input type="password" name="adguard_password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="bg-sky-600 text-white px-4 py-2 rounded hover:bg-sky-700">
                            Salva configurazione
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
