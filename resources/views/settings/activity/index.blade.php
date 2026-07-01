<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Attività') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="border-b">
                                <th class="py-2">Quando</th>
                                <th class="py-2">Utente</th>
                                <th class="py-2">Azione</th>
                                <th class="py-2">Dettagli</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($activities as $activity)
                                <tr class="border-b">
                                    <td class="py-2 whitespace-nowrap">{{ $activity->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="py-2">{{ $activity->causer?->name ?? 'Sistema' }}</td>
                                    <td class="py-2">{{ $activity->description }}</td>
                                    <td class="py-2 text-gray-500">{{ $activity->properties->toJson() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="py-4 text-gray-500" colspan="4">Nessuna attività registrata.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{ $activities->links() }}

        </div>
    </div>
</x-app-layout>
