@props([
    'disabled' => false,
    'error' => false,
])

@php
    $baseClasses =
        'w-full bg-surface border-2 border-ink px-4 py-3 text-sm font-body transition-all duration-200 outline-none focus:shadow-[4px_4px_0px_0px_var(--color-ink)]';
    if ($error) {
        $baseClasses .= ' border-primary-red focus:shadow-[4px_4px_0px_0px_var(--color-primary-red)]';
    }
    if ($disabled) {
        $baseClasses .= ' opacity-50 bg-muted cursor-not-allowed';
    }
@endphp

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => $baseClasses]) !!}>
