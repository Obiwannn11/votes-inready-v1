@extends('voting.layouts.admin')
@section('title', 'Events')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Voting Events</h1>
        <a href="{{ route('voting.admin.events.create') }}"
            class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">
            + Buat Event
        </a>
    </div>

    @forelse($events as $event)
        <div class="bg-white rounded-lg shadow-sm p-4 mb-3 border border-gray-100">
            <div class="flex justify-between items-start">
                <div>
                    <a href="{{ route('voting.admin.events.show', $event) }}"
                        class="text-lg font-semibold hover:text-blue-600">
                        {{ $event->title }}
                    </a>
                    <p class="text-sm text-gray-500 mt-1">Status: <span
                            class="font-medium text-gray-700">{{ strtoupper($event->status) }}</span></p>
                </div>
                <span class="text-xs text-gray-400">
                    Dibuat: {{ $event->created_at->format('d M Y') }}
                </span>
            </div>
        </div>
    @empty
        <div class="text-center text-gray-400 py-12 bg-white rounded-lg border border-dashed">Belum ada event. Buat event
            pertama.</div>
    @endforelse
@endsection
