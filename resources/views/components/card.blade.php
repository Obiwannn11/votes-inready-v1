@props([
    'padding' => 'p-5 sm:p-6 lg:p-8',
    'shadow' => 'lg', // sm, md, lg, xl
    'border' => 'thick', // default, thick
    'accent' => null, // null, 'circle', 'square', 'triangle'
])

@php
    $baseClasses = 'relative transition-all duration-300 hover:-translate-y-1';

    // Apply bg-surface by default if no overriding background class is passed
    if (!str_contains($attributes->get('class', ''), 'bg-')) {
        $baseClasses .= ' bg-surface';
    }

    $borderClasses = [
        'default' => 'border-2 border-ink',
        'thick' => 'border-2 lg:border-4 border-ink',
    ];

    $shadowClasses = [
        'sm' => 'shadow-[3px_3px_0px_0px_var(--color-ink)]',
        'md' => 'shadow-[4px_4px_0px_0px_var(--color-ink)]',
        'lg' => 'shadow-[6px_6px_0px_0px_var(--color-ink)]',
        'xl' => 'shadow-[8px_8px_0px_0px_var(--color-ink)]',
    ];

    $classes = $baseClasses . ' ' . $borderClasses[$border] . ' ' . $shadowClasses[$shadow];
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    @if ($accent === 'circle')
        <div class="absolute top-4 right-4 w-3 h-3 rounded-full bg-primary-red border-2 border-ink"></div>
    @elseif ($accent === 'circle-success')
        <div class="absolute top-4 right-4 w-3 h-3 rounded-full bg-success border-2 border-ink"></div>
    @elseif ($accent === 'circle-muted')
        <div class="absolute top-4 right-4 w-3 h-3 rounded-full bg-muted border-2 border-ink"></div>
    @elseif ($accent === 'square')
        <div class="absolute top-4 right-4 w-3 h-3 bg-primary-blue border-2 border-ink"></div>
    @elseif ($accent === 'triangle')
        <div
            class="absolute top-4 right-4 w-0 h-0 border-l-[6px] border-l-transparent border-r-[6px] border-r-transparent border-b-[10px] border-b-ink">
        </div>
    @endif

    <div class="{{ $padding }}">
        {{ $slot }}
    </div>
</div>
