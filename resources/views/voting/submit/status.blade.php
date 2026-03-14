@extends('voting.layouts.app')
@section('title', 'Status Submission')

@section('content')
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-2">Status Submission</h1>
        <p class="text-gray-500 mb-6">{{ $event->title }}</p>

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex gap-4 justify-between items-center mb-6">
            <h2 class="text-lg font-semibold">Karya Kamu</h2>
            @if ($event->isSubmissionOpen())
                <a href="{{ route('voting.submit.form', $event->slug) }}"
                    class="text-sm bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Submit Karya Baru
                </a>
            @endif
        </div>

        @forelse($submissions as $sub)
            <div
                class="bg-white rounded-lg shadow-sm p-5 mb-4 border-l-4 
        {{ $sub->status === 'pending' ? 'border-yellow-400' : '' }}
        {{ $sub->status === 'approved' ? 'border-green-400' : '' }}
        {{ $sub->status === 'rejected' ? 'border-red-400' : '' }}">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <h3 class="font-bold text-lg">{{ $sub->title }}</h3>
                        <p class="text-sm text-gray-500 capitalize">{{ $sub->concentration }} ·
                            {{ $sub->created_at->format('d M Y H:i') }}</p>
                    </div>
                    <span
                        class="text-xs font-semibold px-2 py-1 rounded
                {{ $sub->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                {{ $sub->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                {{ $sub->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                        {{ $sub->status === 'pending' ? 'Menunggu Review' : '' }}
                        {{ $sub->status === 'approved' ? 'Approved ✓' : '' }}
                        {{ $sub->status === 'rejected' ? 'Rejected ✗' : '' }}
                    </span>
                </div>

                @if ($sub->admin_notes)
                    <div class="mt-4 p-3 bg-gray-50 rounded text-sm text-gray-700">
                        <strong class="text-gray-900 block mb-1">Catatan Admin:</strong>
                        {{ $sub->admin_notes }}
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center text-gray-500 py-10 bg-white rounded-lg shadow-sm">
                Belum ada karya yang di-submit.
            </div>
        @endforelse

    </div>
@endsection
