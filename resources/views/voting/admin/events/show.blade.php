@extends('voting.layouts.admin')
@section('title', $event->title)
@section('admin_nav_title', $event->title)
@section('admin_nav_breadcrumb')
    <a href="{{ route('voting.admin.events.index') }}" class="hover:text-ink transition-colors">Events</a>
    <span class="text-ink/40">&gt;</span>
    <span class="text-ink font-medium">{{ $event->title }}</span>
@endsection

@section('content')
    <div class="flex justify-end mb-6">
        <x-button variant="outline" size="sm" href="{{ route('voting.admin.events.edit', $event) }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="square" stroke-linejoin="miter">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
            Edit Event
        </x-button>
    </div>

    {{-- Status Controls --}}
    <div class="card bg-surface p-6 mb-6 border-2 border-ink shadow-[6px_6px_0px_0px_var(--color-ink)]"
        x-data="{ confirmStatusOpen: false, targetStatusForm: null, targetStatusLabel: '' }">
        <h2 class="font-display font-black text-xl mb-4 pl-3 border-l-4 border-primary-yellow uppercase">Kontrol Status</h2>

        @php
            $statusOptions = [
                'draft' => [
                    'label' => 'Draft',
                    'variant_active' => 'bg-ink text-surface shadow-[4px_4px_0px_0px_var(--color-primary-yellow)]',
                    'variant_inactive' =>
                        'bg-surface text-ink shadow-[4px_4px_0px_0px_var(--color-ink)] hover:bg-muted',
                ],
                'submission_open' => [
                    'label' => 'Buka Submission',
                    'variant_active' => 'bg-primary-blue text-surface shadow-[4px_4px_0px_0px_var(--color-ink)]',
                    'variant_inactive' =>
                        'bg-surface text-ink shadow-[4px_4px_0px_0px_var(--color-ink)] hover:bg-muted',
                ],
                'voting_open' => [
                    'label' => 'Buka Voting',
                    'variant_active' => 'bg-success text-surface shadow-[4px_4px_0px_0px_var(--color-ink)]',
                    'variant_inactive' =>
                        'bg-surface text-ink shadow-[4px_4px_0px_0px_var(--color-ink)] hover:bg-muted',
                ],
                'closed' => [
                    'label' => 'Tutup Event',
                    'variant_active' => 'bg-primary-red text-surface shadow-[4px_4px_0px_0px_var(--color-ink)]',
                    'variant_inactive' =>
                        'bg-surface text-ink shadow-[4px_4px_0px_0px_var(--color-ink)] hover:bg-muted',
                ],
                'archived' => [
                    'label' => 'Arsipkan',
                    'variant_active' => 'bg-grey text-surface shadow-[4px_4px_0px_0px_var(--color-ink)]',
                    'variant_inactive' =>
                        'bg-surface text-ink shadow-[4px_4px_0px_0px_var(--color-ink)] hover:bg-muted',
                ],
            ];
            $currentStatusLabel = $statusOptions[$event->status]['label'] ?? str_replace('_', ' ', $event->status);
        @endphp

        <div class="flex flex-wrap gap-3">
            @foreach ($statusOptions as $status => $meta)
                @php
                    $isActive = $event->status === $status;
                    $isEnabled = $isActive || $event->canTransitionTo($status);
                    $statusFormRef = 'statusForm_' . $status;
                    $buttonClass = $isActive
                        ? $meta['variant_active']
                        : ($isEnabled
                            ? $meta['variant_inactive']
                            : 'bg-muted text-ink/30 cursor-not-allowed shadow-none');
                @endphp

                <form x-ref="{{ $statusFormRef }}" method="POST"
                    action="{{ route('voting.admin.events.changeStatus', $event) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="{{ $status }}">
                    <button type="button" {{ $isEnabled ? '' : 'disabled' }}
                        @click="targetStatusForm = '{{ $statusFormRef }}'; targetStatusLabel = '{{ $meta['label'] }}'; confirmStatusOpen = true"
                        class="px-4 py-2 border-2 border-ink font-display font-bold text-sm uppercase tracking-wide transition-all duration-200 cursor-pointer active:translate-x-[2px] active:translate-y-[2px] active:shadow-none {{ $buttonClass }}">
                        @if ($isActive)
                            <span class="inline-block w-2 h-2 rounded-full bg-current mr-2 animate-pulse"></span>
                        @endif
                        {{ $meta['label'] }}
                    </button>
                </form>
            @endforeach
        </div>

        <p class="font-body text-xs text-ink/50 mt-3">Status nonaktif berarti transisi belum valid dari status saat ini.</p>

        <div class="mt-4 pt-4 border-t-2 border-ink/10 font-body text-sm text-ink/70 space-y-1">
            <p>Voting dibuka:
                <strong>{{ $event->voting_opened_at ? $event->voting_opened_at->format('d M Y, H:i') . ' WITA' : '-' }}</strong>
            </p>
            <p>Voting ditutup:
                <strong>{{ $event->voting_closed_at ? $event->voting_closed_at->format('d M Y, H:i') . ' WITA' : '-' }}</strong>
            </p>
        </div>

        <div x-show="confirmStatusOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-ink/60 backdrop-blur-sm" @click="confirmStatusOpen = false"></div>

            <div x-show="confirmStatusOpen" x-transition.scale.95
                class="relative bg-surface border-4 border-ink shadow-[8px_8px_0px_0px_var(--color-ink)] w-full max-w-md p-6 sm:p-8 text-center"
                @keydown.escape.window="confirmStatusOpen = false">
                <h3 class="font-display font-black text-2xl uppercase mb-2">Konfirmasi Status</h3>
                <p class="font-body text-ink/70 text-sm mb-1">Status saat ini: <strong>{{ $currentStatusLabel }}</strong>
                </p>
                <p class="font-body text-ink/80 mb-8">Ubah status event ke <strong x-text="targetStatusLabel"></strong>?</p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <button type="button" @click="confirmStatusOpen = false"
                        class="px-6 py-3 border-2 border-ink bg-surface text-ink font-display font-bold shadow-md active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all uppercase cursor-pointer">
                        Batal
                    </button>
                    <button type="button"
                        @click="if (targetStatusForm && $refs[targetStatusForm]) { $refs[targetStatusForm].submit(); }"
                        class="px-6 py-3 border-2 border-ink bg-primary-blue text-surface font-display font-bold shadow-md active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all uppercase cursor-pointer">
                        Ya, Ubah Status
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        @php
            $statColors = [
                'total_submissions' => 'border-primary-blue',
                'pending' => 'border-primary-yellow',
                'approved' => 'border-success',
                'rejected' => 'border-primary-red',
                'total_votes' => 'border-ink',
            ];
            $statAccents = [
                'total_submissions' => 'bg-primary-blue',
                'pending' => 'bg-primary-yellow',
                'approved' => 'bg-success',
                'rejected' => 'bg-primary-red',
                'total_votes' => 'bg-ink',
            ];
        @endphp
        @foreach (['total_submissions' => 'Total Karya', 'pending' => 'Pending', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'total_votes' => 'Total Vote'] as $key => $label)
            <div
                class="card bg-surface p-4 text-center border-2 border-ink shadow-[4px_4px_0px_0px_var(--color-ink)] relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1 {{ $statAccents[$key] }}"></div>
                <p class="font-display font-black text-3xl text-ink">{{ $stats[$key] }}</p>
                <p class="font-body text-xs text-ink/60 uppercase tracking-wide mt-1">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    {{-- Submissions List --}}
    <div class="card bg-surface p-6 border-2 border-ink shadow-[6px_6px_0px_0px_var(--color-ink)]">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-display font-black text-xl pl-3 border-l-4 border-primary-red uppercase">Submissions</h2>
            <x-button variant="outline" size="sm" href="{{ route('voting.admin.submissions', $event) }}">
                Kelola semua
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="square" stroke-linejoin="miter">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </x-button>
        </div>

        @forelse($event->submissions->take(5) as $sub)
            <div class="flex justify-between items-center py-3 border-b-2 border-ink/10 last:border-0">
                <div>
                    <a href="{{ route('voting.admin.submissions.show', $sub) }}"
                        class="font-display font-bold text-sm uppercase tracking-wide text-ink hover:text-primary-blue transition-colors">
                        {{ $sub->title }}
                    </a>
                    <span class="font-body text-xs text-ink/50 ml-2">by {{ $sub->submitter->name ?? 'Unknown' }}</span>
                </div>
                <x-badge :type="$sub->status" :pill="true">{{ $sub->status }}</x-badge>
            </div>
        @empty
            <div class="py-8 text-center">
                <p class="font-display font-bold uppercase text-ink/30 text-sm tracking-wide">Belum ada submission masuk.
                </p>
            </div>
        @endforelse
    </div>
@endsection
