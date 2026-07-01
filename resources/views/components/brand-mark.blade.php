@props(['compact' => false])

<div {{ $attributes->merge(['class' => 'flex items-center']) }}>
    <img src="{{ asset('logo-luma-bianco.svg') }}" alt="Studio Medico Luma" class="{{ $compact ? 'h-8' : 'h-16' }} w-auto object-contain">
</div>
