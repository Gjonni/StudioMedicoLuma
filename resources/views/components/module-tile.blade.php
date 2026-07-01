@props(['href', 'label', 'color' => 'sky'])

@php
    $colors = [
        'sky' => 'bg-sky-100 text-sky-600',
        'emerald' => 'bg-emerald-100 text-emerald-600',
        'violet' => 'bg-violet-100 text-violet-600',
        'amber' => 'bg-amber-100 text-amber-600',
    ][$color] ?? 'bg-sky-100 text-sky-600';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'bg-white rounded-xl shadow-sm border border-gray-100 p-3 flex flex-col items-center gap-2 text-center hover:shadow-md hover:-translate-y-0.5 transition']) }}>
    <span class="{{ $colors }} h-9 w-9 rounded-full flex items-center justify-center">
        {{ $slot }}
    </span>
    <span class="font-semibold text-xs text-gray-800">{{ $label }}</span>
</a>
