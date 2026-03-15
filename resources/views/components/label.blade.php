@props(['value'])

<label
    {{ $attributes->merge(['class' => 'block font-display font-bold uppercase text-xs tracking-widest text-ink mb-2']) }}>
    {{ $value ?? $slot }}
</label>
