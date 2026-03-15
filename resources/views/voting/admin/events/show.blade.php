@extends('voting.layouts.admin')
@section('title', $event->title)

@section('content')
    <div class="flex justify-between items-start mb-6">
        <div>
            <h1 class="text-2xl font-bold">{{ $event->title }}</h1>
            <p class="text-gray-500 text-sm">Status: <strong>{{ strtoupper($event->status) }}</strong></p>
        </div>
        <a href="{{ route('voting.admin.events.edit', $event) }}" class="text-sm text-blue-600 hover:underline">Edit Event</a>
    </div>

    {{-- Status controls --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 mb-6">
        <h2 class="font-semibold mb-3">Kontrol Status</h2>
        @php
            $statusOptions = [
                'draft' => [
                    'label' => 'Draft',
                    'active' => 'bg-gray-800 text-white',
                    'inactive' => 'bg-gray-200 hover:bg-gray-300',
                ],
                'submission_open' => [
                    'label' => 'Buka Submission',
                    'active' => 'bg-blue-600 text-white',
                    'inactive' => 'bg-gray-200 hover:bg-gray-300',
                ],
                'voting_open' => [
                    'label' => 'Buka Voting',
                    'active' => 'bg-green-600 text-white',
                    'inactive' => 'bg-gray-200 hover:bg-gray-300',
                ],
                'closed' => [
                    'label' => 'Tutup Event',
                    'active' => 'bg-red-600 text-white',
                    'inactive' => 'bg-gray-200 hover:bg-gray-300',
                ],
                'archived' => [
                    'label' => 'Arsipkan Event',
                    'active' => 'bg-purple-700 text-white',
                    'inactive' => 'bg-gray-200 hover:bg-gray-300',
                ],
            ];
        @endphp

        <div class="flex flex-wrap gap-2">
            @foreach ($statusOptions as $status => $meta)
                @php
                    $isActive = $event->status === $status;
                    $isEnabled = $isActive || $event->canTransitionTo($status);
                    $buttonClass = $isActive
                        ? $meta['active']
                        : ($isEnabled
                            ? $meta['inactive']
                            : 'bg-gray-100 text-gray-400 cursor-not-allowed');
                @endphp

                <form method="POST" action="{{ route('voting.admin.events.changeStatus', $event) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="{{ $status }}">
                    <button type="submit" {{ $isEnabled ? '' : 'disabled' }}
                        class="px-3 py-1 rounded text-sm {{ $buttonClass }}">
                        {{ $meta['label'] }}
                    </button>
                </form>
            @endforeach
        </div>

        <p class="text-xs text-gray-400 mt-2">Status nonaktif berarti transisi belum valid dari status saat ini.</p>

        <div class="mt-4 pt-4 border-t border-gray-100 text-sm text-gray-600 space-y-1">
            <p>Voting dibuka:
                {{ $event->voting_opened_at ? $event->voting_opened_at->format('d M Y, H:i') . ' WITA' : '-' }}</p>
            <p>Voting ditutup:
                {{ $event->voting_closed_at ? $event->voting_closed_at->format('d M Y, H:i') . ' WITA' : '-' }}</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
        @foreach (['total_submissions' => 'Total Karya', 'pending' => 'Pending', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'total_votes' => 'Total Vote'] as $key => $label)
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 text-center">
                <p class="text-2xl font-bold">{{ $stats[$key] }}</p>
                <p class="text-xs text-gray-500">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    {{-- Submissions list preview --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-semibold">Submissions</h2>
            <a href="{{ route('voting.admin.submissions', $event) }}" class="text-sm text-blue-600 hover:underline">Kelola
                semua →</a>
        </div>

        @forelse($event->submissions->take(5) as $sub)
            <div class="flex justify-between items-center py-2 border-b last:border-0 text-sm">
                <div>
                    <a href="{{ route('voting.admin.submissions.show', $sub) }}"
                        class="font-medium hover:underline">{{ $sub->title }}</a>
                    <span class="text-gray-500 ml-2">by {{ $sub->submitter->name ?? 'Unknown' }}</span>
                </div>
                <span
                    class="px-2 py-1 rounded text-xs 
            {{ $sub->status === 'approved' ? 'bg-green-100 text-green-700' : ($sub->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                    {{ strtoupper($sub->status) }}
                </span>
            </div>
        @empty
            <p class="text-gray-400 text-sm py-4 text-center">Belum ada submission masuk.</p>
        @endforelse
    </div>
@endsection
