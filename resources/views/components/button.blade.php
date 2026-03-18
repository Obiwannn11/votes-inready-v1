@props([
    'variant' => 'primary', // primary, secondary, danger, info, outline, ghost
    'size' => 'md', // sm, md, lg
    'type' => 'button',
    'href' => null,
])

@php
    $baseClasses =
        'inline-flex items-center justify-center font-display font-bold uppercase tracking-widest transition-all duration-200 ease-out border-2 border-ink cursor-pointer active:translate-x-[2px] active:translate-y-[2px] active:shadow-none focus:outline-none focus:ring-2 focus:ring-ink focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

    $variants = [
        'primary' =>
            'bg-primary-yellow text-ink shadow-[4px_4px_0px_0px_var(--color-ink)] hover:bg-primary-yellow-dark',
        'secondary' => 'bg-ink text-surface shadow-[4px_4px_0px_0px_var(--color-primary-yellow)] hover:bg-gray-800',
        'danger' => 'bg-primary-red text-surface shadow-[4px_4px_0px_0px_var(--color-ink)] hover:bg-primary-red-dark',
        'success' => 'bg-success text-surface shadow-[4px_4px_0px_0px_var(--color-ink)] hover:bg-green-700',
        'info' => 'bg-primary-blue text-surface shadow-[4px_4px_0px_0px_var(--color-ink)] hover:bg-primary-blue-dark',
        'outline' => 'bg-transparent text-ink shadow-[4px_4px_0px_0px_var(--color-ink)] hover:bg-canvas',
        'ghost' =>
            'border-transparent shadow-none hover:bg-muted text-ink active:shadow-none active:translate-x-0 active:translate-y-0',
    ];

    $sizes = [
        'sm' => 'px-4 py-2 text-xs',
        'md' => 'px-6 py-3 text-sm',
        'lg' => 'px-8 py-4 text-base',
    ];

    $classes = $baseClasses . ' ' . $variants[$variant] . ' ' . $sizes[$size];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
