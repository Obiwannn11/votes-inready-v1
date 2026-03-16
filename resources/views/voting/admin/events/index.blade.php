@extends('voting.layouts.admin')
@section('title', 'Events')
@section('admin_nav_title', 'Voting Events')
@section('admin_nav_breadcrumb')
    <a href="{{ route('voting.admin.events.index') }}" class="hover:text-ink transition-colors">Events</a>
    <span class="text-ink/40">&gt;</span>
    <span class="text-ink font-medium">Semua Event</span>
@endsection

@section('content')
    <div class="flex justify-end items-center mb-8">
        <x-button variant="primary" href="{{ route('voting.admin.events.create') }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.5" stroke-linecap="square" stroke-linejoin="miter">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Buat Event
        </x-button>
    </div>

    @forelse($events as $event)
        <a href="{{ route('voting.admin.events.show', $event) }}"
            class="block card bg-surface p-5 mb-4 border-2 border-ink shadow-[4px_4px_0px_0px_var(--color-ink)] hover:-translate-y-1 transition-all duration-200 cursor-pointer no-underline">
            <div class="flex justify-between items-start">
                <div>
                    <span class="font-display font-black text-lg uppercase tracking-tight text-ink">
                        {{ $event->title }}
                    </span>
                    <div class="mt-2">
                        <x-badge :type="$event->status" :pill="true">{{ str_replace('_', ' ', $event->status) }}</x-badge>
                    </div>
                </div>
                <span class="font-body text-xs text-ink/50">
                    Dibuat: {{ $event->created_at->format('d M Y') }}
                </span>
            </div>
        </a>
    @empty
        <div class="card bg-surface border-2 border-dashed border-ink p-12 text-center shadow-none">
            <div class="icon-container mx-auto mb-4 bg-muted text-ink border-2 border-ink">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="square" stroke-linejoin="miter">
                    <rect x="3" y="4" width="18" height="18" rx="0"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
            </div>
            <p class="font-display font-bold uppercase text-ink/40 text-sm tracking-wide">Belum ada event. Buat event
                pertama.</p>
        </div>
    @endforelse
@endsection
