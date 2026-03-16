@props(['type' => 'website', 'pill' => false])

@php
    $baseClasses =
        'inline-block text-xs font-display font-bold uppercase tracking-widest px-3 py-1 border-2 border-ink shadow-[2px_2px_0px_0px_var(--color-ink)]';

    if ($pill) {
        $baseClasses .= ' rounded-full';
    }

    $types = [
        // Concentration types
        'website' => 'bg-primary-blue text-surface',
        'design' => 'bg-primary-red text-surface',
        'mobile' => 'bg-primary-yellow text-ink',
        'default' => 'bg-surface text-ink',
        // Status types
        'draft' => 'bg-ink text-surface',
        'submission_open' => 'bg-primary-blue text-surface',
        'voting_open' => 'bg-success text-surface',
        'closed' => 'bg-primary-red text-surface',
        'archived' => 'bg-grey text-surface',
        'pending' => 'bg-primary-yellow text-ink',
        'approved' => 'bg-success text-surface',
        'rejected' => 'bg-primary-red text-surface',
    ];

    $classes = $baseClasses . ' ' . ($types[$type] ?? $types['default']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
