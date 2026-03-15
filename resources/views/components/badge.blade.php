@props(['type' => 'website'])

@php
    $baseClasses =
        'inline-block text-xs font-display font-bold uppercase tracking-widest px-3 py-1 border-2 border-ink shadow-[2px_2px_0px_0px_var(--color-ink)]';

    $types = [
        'website' => 'bg-primary-blue text-surface',
        'design' => 'bg-primary-red text-surface',
        'mobile' => 'bg-primary-yellow text-ink',
        'default' => 'bg-surface text-ink',
    ];

    $classes = $baseClasses . ' ' . ($types[$type] ?? $types['default']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
