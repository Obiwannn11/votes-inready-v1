@extends('voting.layouts.admin')
@section('title', 'Submissions: ' . $event->title)

@section('content')
    <div class="mb-8">
        <a href="{{ route('voting.admin.events.show', $event) }}"
            class="inline-flex items-center gap-1 font-display font-bold text-xs uppercase tracking-widest text-ink/50 hover:text-primary-blue transition-colors mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.5" stroke-linecap="square" stroke-linejoin="miter">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Kembali ke Event
        </a>
        <h1 class="section-title mb-1">Submissions</h1>
        <p class="section-subtitle">{{ $event->title }}</p>
    </div>

    {{-- Filter Tabs --}}
    <div class="flex gap-2 mb-6 flex-wrap">
        @php
            $currentStatus = \Illuminate\Support\Facades\Request::get('status');
            $filters = [
                null => 'Semua',
                'pending' => 'Pending',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
            ];
            $filterTypes = [
                null => 'default',
                'pending' => 'pending',
                'approved' => 'approved',
                'rejected' => 'rejected',
            ];
        @endphp
        @foreach ($filters as $statusVal => $label)
            @php
                $isActive = $currentStatus === $statusVal;
                $url = $statusVal
                    ? route('voting.admin.submissions', [$event, 'status' => $statusVal])
                    : route('voting.admin.submissions', $event);
            @endphp
            <a href="{{ $url }}"
                class="px-4 py-2 border-2 border-ink font-display font-bold text-xs uppercase tracking-wide transition-all duration-200 active:translate-x-[2px] active:translate-y-[2px] active:shadow-none {{ $isActive ? 'bg-ink text-surface shadow-[4px_4px_0px_0px_var(--color-primary-yellow)]' : 'bg-surface text-ink shadow-[4px_4px_0px_0px_var(--color-ink)] hover:bg-muted' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    @forelse($submissions as $sub)
        <div class="card bg-surface p-5 mb-4 border-2 border-ink shadow-[4px_4px_0px_0px_var(--color-ink)] flex gap-4 flex-col sm:flex-row">
            @if ($sub->thumbnail_path)
                @php
                    $thumbnailUrl = \Illuminate\Support\Str::startsWith($sub->thumbnail_path, 'images/')
                        ? asset($sub->thumbnail_path)
                        : \Illuminate\Support\Facades\Storage::url($sub->thumbnail_path);
                @endphp
                <img src="{{ $thumbnailUrl }}"
                    class="w-20 h-20 object-cover border-2 border-ink shadow-[2px_2px_0px_0px_var(--color-ink)] bg-canvas flex-shrink-0"
                    alt="Thumbnail karya {{ $sub->title }}" loading="lazy">
            @else
                <div class="w-20 h-20 bg-muted border-2 border-ink flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-ink/30" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="square" stroke-linejoin="miter">
                        <rect x="3" y="3" width="18" height="18" rx="0"></rect>
                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                        <polyline points="21 15 16 10 5 21"></polyline>
                    </svg>
                </div>
            @endif

            <div class="flex-1 min-w-0">
                <div class="flex flex-col sm:flex-row sm:justify-between gap-3">
                    <div>
                        <a href="{{ route('voting.admin.submissions.show', $sub) }}"
                            class="font-display font-black text-lg uppercase tracking-tight text-ink hover:text-primary-blue transition-colors">
                            {{ $sub->title }}
                        </a>
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
                    <div class="mt-3 flex gap-2">
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
