@props(['compact' => false])

<div {{ $attributes->merge(['class' => 'flex items-center']) }}>
    <x-application-logo class="{{ $compact ? 'h-8' : 'h-16' }} w-auto" />
</div>
