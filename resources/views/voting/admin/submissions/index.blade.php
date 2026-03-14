@extends('voting.layouts.admin')
@section('title', 'Submissions: ' . $event->title)

@section('content')
    <div class="mb-6">
        <a href="{{ route('voting.admin.events.show', $event) }}"
            class="text-sm text-gray-500 hover:text-blue-600 mb-2 inline-block">← Kembali ke Event</a>
        <h1 class="text-2xl font-bold">Submissions</h1>
        <p class="text-gray-500">{{ $event->title }}</p>
    </div>

    {{-- Filter --}}
    <div class="flex gap-2 mb-4">
        <a href="{{ route('voting.admin.submissions', $event) }}"
            class="px-3 py-1 rounded text-sm {{ !\Illuminate\Support\Facades\Request::has('status') ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">Semua</a>
        @foreach (['pending', 'approved', 'rejected'] as $s)
            <a href="{{ route('voting.admin.submissions', [$event, 'status' => $s]) }}"
                class="px-3 py-1 rounded text-sm {{ \Illuminate\Support\Facades\Request::get('status') === $s ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">{{ ucfirst($s) }}</a>
        @endforeach
    </div>

    @forelse($submissions as $sub)
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 mb-3 flex gap-4">
            @if ($sub->thumbnail_path)
                @php
                    $thumbnailUrl = \Illuminate\Support\Str::startsWith($sub->thumbnail_path, 'images/')
                        ? asset($sub->thumbnail_path)
                        : \Illuminate\Support\Facades\Storage::url($sub->thumbnail_path);
                @endphp
                <img src="{{ $thumbnailUrl }}" class="w-20 h-20 object-cover rounded bg-gray-100" alt="">
            @else
                <div
                    class="w-20 h-20 bg-gray-200 rounded flex items-center justify-center text-gray-400 text-xs text-center">
                    No Img</div>
            @endif
            <div class="flex-1">
                <div class="flex justify-between">
                    <div>
                        <a href="{{ route('voting.admin.submissions.show', $sub) }}"
                            class="font-bold text-lg hover:text-blue-600">{{ $sub->title }}</a>
                        <p class="text-sm text-gray-500">Oleh: {{ $sub->submitter->name ?? 'Unknown' }} | <span
                                class="text-xs">{{ $sub->created_at->diffForHumans() }}</span></p>
                    </div>
                    <div>
                        <span
                            class="px-2 py-1 rounded text-xs {{ $sub->status === 'approved' ? 'bg-green-100 text-green-700' : ($sub->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                            {{ strtoupper($sub->status) }}
                        </span>
                    </div>
                </div>

                @if ($sub->status === 'pending')
                    <div class="mt-3 flex gap-2">
                        <form method="POST" action="{{ route('voting.admin.submissions.review', $sub) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="approved">
                            <button type="submit"
                                class="text-sm bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('voting.admin.submissions.review', $sub) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit"
                                class="text-sm bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Reject</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="text-center text-gray-400 py-12 bg-white rounded-lg border border-dashed">Belum ada submission.</div>
    @endforelse

    <div class="mt-4">
        {{ $submissions->links() }}
    </div>
@endsection
