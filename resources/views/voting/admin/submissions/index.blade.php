@extends('voting.layouts.admin')
@section('title', 'Submissions: ' . $event->title)
@section('admin_nav_title', 'Submissions Event')
@section('admin_nav_breadcrumb')
    <a href="{{ route('voting.admin.events.index') }}" class="hover:text-ink transition-colors">Events</a>
    <span class="text-ink/40">&gt;</span>
    <a href="{{ route('voting.admin.events.show', $event) }}" class="hover:text-ink transition-colors">{{ $event->title }}</a>
    <span class="text-ink/40">&gt;</span>
    <span class="text-ink font-medium">Submissions</span>
@endsection

@section('content')
    <div class="mb-4">
        <x-button variant="outline" size="sm" href="{{ route('voting.admin.events.show', $event) }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.5" stroke-linecap="square" stroke-linejoin="miter">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Kembali ke Event
        </x-button>
    </div>

    {{-- Filter Tabs --}}
    <div class="flex gap-2 mb-6 flex-wrap">
        @php
            $currentStatus = request()->query('status');

            if (!in_array($currentStatus, ['pending', 'approved', 'rejected'], true)) {
                $currentStatus = 'all';
            }

            $filters = [
                'all' => [
                    'label' => 'Semua',
                    'active_class' => 'bg-ink text-surface shadow-[4px_4px_0px_0px_var(--color-primary-yellow)]',
                ],
                'pending' => [
                    'label' => 'Pending',
                    'active_class' => 'bg-primary-yellow text-ink shadow-[4px_4px_0px_0px_var(--color-ink)]',
                ],
                'approved' => [
                    'label' => 'Approved',
                    'active_class' => 'bg-success text-surface shadow-[4px_4px_0px_0px_var(--color-ink)]',
                ],
                'rejected' => [
                    'label' => 'Rejected',
                    'active_class' => 'bg-primary-red text-surface shadow-[4px_4px_0px_0px_var(--color-ink)]',
                ],
            ];
        @endphp
        @foreach ($filters as $statusVal => $meta)
            @php
                $isActive = $currentStatus === $statusVal;
                $url =
                    $statusVal === 'all'
                        ? route('voting.admin.submissions', $event)
                        : route('voting.admin.submissions', [$event, 'status' => $statusVal]);
            @endphp
            <a href="{{ $url }}"
                class="px-4 py-2 border-2 border-ink font-display font-bold text-xs uppercase tracking-wide transition-all duration-200 cursor-pointer active:translate-x-[2px] active:translate-y-[2px] active:shadow-none {{ $isActive ? $meta['active_class'] : 'bg-surface text-ink shadow-[4px_4px_0px_0px_var(--color-ink)] hover:bg-muted' }}">
                {{ $meta['label'] }}
            </a>
        @endforeach
    </div>

    @forelse($submissions as $sub)
        <div
            class="relative card bg-surface p-5 mb-4 border-2 border-ink shadow-[4px_4px_0px_0px_var(--color-ink)] flex gap-4 flex-col sm:flex-row hover:-translate-y-1 transition-transform duration-200">
            {{-- Full card clickable link --}}
            <a href="{{ route('voting.admin.submissions.show', $sub) }}" class="absolute inset-0 z-0"
                aria-label="Lihat detail {{ $sub->title }}"></a>

            @if ($sub->thumbnail_path)
                @php
                    $thumbnailUrl = \Illuminate\Support\Str::startsWith($sub->thumbnail_path, 'images/')
                        ? asset($sub->thumbnail_path)
                        : \Illuminate\Support\Facades\Storage::url($sub->thumbnail_path);
                @endphp
                <img src="{{ $thumbnailUrl }}"
                    class="w-20 h-20 object-cover border-2 border-ink shadow-[2px_2px_0px_0px_var(--color-ink)] bg-canvas flex-shrink-0 relative z-[1] pointer-events-none"
                    alt="Thumbnail karya {{ $sub->title }}" loading="lazy">
            @else
                <div
                    class="w-20 h-20 bg-muted border-2 border-ink flex items-center justify-center flex-shrink-0 relative z-[1] pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-ink/30" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="square" stroke-linejoin="miter">
                        <rect x="3" y="3" width="18" height="18" rx="0"></rect>
                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                        <polyline points="21 15 16 10 5 21"></polyline>
                    </svg>
                </div>
            @endif

            <div class="flex-1 min-w-0 relative z-[1] pointer-events-none">
                <div class="flex flex-col sm:flex-row sm:justify-between gap-3">
                    <div>
                        <span class="font-display font-black text-lg uppercase tracking-tight text-ink">
                            {{ $sub->title }}
                        </span>
                        <p class="font-body text-sm text-ink/60 mt-1">
                            Oleh: <strong class="text-ink">{{ $sub->submitter->name ?? 'Unknown' }}</strong>
                            <span class="text-ink/40 mx-1">|</span>
                            <span class="text-xs">{{ $sub->created_at->diffForHumans() }}</span>
                        </p>
                    </div>
                    <div class="flex-shrink-0">
                        <x-badge :type="$sub->status" :pill="true">{{ $sub->status }}</x-badge>
                    </div>
                </div>

                @if ($sub->status === 'pending')
                    <div class="mt-3 flex gap-2 pointer-events-auto">
                        <form method="POST" action="{{ route('voting.admin.submissions.review', $sub) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="approved">
                            <x-button type="submit" variant="primary" size="sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 mr-1" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"
                                    stroke-linejoin="miter">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                Approve
                            </x-button>
                        </form>
                        <x-button variant="danger" size="sm"
                            href="{{ route('voting.admin.submissions.show', $sub) }}">
                            Reject (Isi Alasan)
                        </x-button>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="card bg-surface border-2 border-dashed border-ink p-12 text-center shadow-none">
            <div class="icon-container mx-auto mb-4 bg-muted text-ink border-2 border-ink">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="square" stroke-linejoin="miter">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
            </div>
            <p class="font-display font-bold uppercase text-ink/40 text-sm tracking-wide">Belum ada submission.</p>
        </div>
    @endforelse

    <div class="mt-6">
        {{ $submissions->links() }}
    </div>
@endsection
