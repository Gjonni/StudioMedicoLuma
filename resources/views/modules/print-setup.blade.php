<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Configura stampante sul tuo dispositivo') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="font-semibold mb-2">Stato attuale della stampante</h3>
                    <pre class="text-sm text-gray-700 whitespace-pre-wrap">{{ $status }}</pre>
                    <p class="text-xs text-gray-500 mt-2">Se qui vedi un errore, verifica che il Pi e CUPS siano raggiungibili prima di seguire la guida sotto.</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p class="text-sm text-gray-600 mb-1">Host stampante rilevato: <code class="bg-gray-100 px-1 rounded">{{ $host }}</code></p>
                    <p class="text-sm text-gray-600 mb-4">Nome coda CUPS: <code class="bg-gray-100 px-1 rounded">{{ $printerName }}</code></p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" x-data="{ tab: 'windows' }">
                <div class="border-b border-gray-200 flex">
                    <button type="button" @click="tab = 'windows'"
                        :class="tab === 'windows' ? 'border-sky-500 text-sky-600' : 'border-transparent text-gray-500'"
                        class="px-4 py-3 text-sm font-medium border-b-2">
                        Windows 10/11
                    </button>
                    <button type="button" @click="tab = 'linux'"
                        :class="tab === 'linux' ? 'border-sky-500 text-sky-600' : 'border-transparent text-gray-500'"
                        class="px-4 py-3 text-sm font-medium border-b-2">
                        Linux
                    </button>
                </div>

                <div class="p-6 text-gray-900" x-show="tab === 'windows'">
                    <p class="text-sm text-gray-600 mb-4">
                        Windows 10/11 supporta IPP nativamente: nella maggior parte dei casi non serve installare
                        alcun driver aggiuntivo.
                    </p>
                    <ol class="list-decimal list-inside text-sm text-gray-700 space-y-2 mb-4">
                        <li>Impostazioni &rarr; Bluetooth e dispositivi &rarr; Stampanti e scanner &rarr; <strong>Aggiungi dispositivo</strong></li>
                        <li>Clicca su <strong>La stampante che voglio non è nell'elenco</strong></li>
                        <li>Seleziona <strong>Seleziona una stampante condivisa in base al nome</strong></li>
                        <li>Incolla l'URL qui sotto e prosegui</li>
                        <li>Segui la procedura guidata fino al termine</li>
                    </ol>
                    <div class="flex items-center gap-2">
                        <code class="bg-gray-100 px-2 py-1 rounded text-sm flex-1 overflow-x-auto">{{ $ippUrl }}</code>
                    </div>
                </div>

                <div class="p-6 text-gray-900 space-y-6" x-show="tab === 'linux'" style="display: none">
                    <div>
                        <h4 class="font-semibold mb-2">Opzione 1 — Comando CLI diretto</h4>
                        <p class="text-sm text-gray-600 mb-2">Da terminale, con CUPS installato:</p>
                        <code class="block bg-gray-100 px-2 py-1 rounded text-sm overflow-x-auto">lpadmin -p {{ $printerName }} -E -v ipp://{{ $host }}:631/printers/{{ $printerName }} -m everywhere</code>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-2">Opzione 2 — Interfaccia grafica (system-config-printer)</h4>
                        <ol class="list-decimal list-inside text-sm text-gray-700 space-y-2">
                            <li>Apri <strong>system-config-printer</strong> &rarr; <strong>Aggiungi</strong></li>
                            <li>Seleziona <strong>Network Printer</strong> &rarr; <strong>Internet Printing Protocol (ipp)</strong></li>
                            <li>Host: <code class="bg-gray-100 px-1 rounded">{{ $host }}</code></li>
                            <li>Coda/Path: <code class="bg-gray-100 px-1 rounded">printers/{{ $printerName }}</code></li>
                            <li>Completa la procedura scegliendo il driver "everywhere" o generico IPP</li>
                        </ol>
                    </div>
                </div>
            </div>

            <p class="text-xs text-gray-500">
                Nota: su macOS non è necessaria alcuna configurazione manuale — la stampante viene rilevata
                automaticamente via Bonjour/Avahi.
            </p>

        </div>
    </div>
</x-app-layout>
