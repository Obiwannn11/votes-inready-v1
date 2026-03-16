@extends('voting.layouts.app')
@section('title', 'Status Submission')

@section('content')
    <div class="max-w-3xl mx-auto">
        <h1 class="text-2xl font-bold mb-2">Status Submission</h1>
        <p class="text-gray-500 mb-6">{{ $event->title }}</p>

        @if (session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if (session('info'))
            <div class="mb-4 bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded">
                {{ session('info') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        <div
            class="bg-white rounded-lg shadow-sm p-5 border-l-4 {{ !$submission ? 'border-gray-300' : ($submission->status === 'pending' ? 'border-yellow-400' : ($submission->status === 'approved' ? 'border-green-400' : 'border-red-400')) }}">
            <div class="flex gap-4 justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">Karya Kamu</h2>

                @if (!$submission && $event->isSubmissionOpen())
                    <a href="{{ route('voting.submit.form', $event) }}"
                        class="text-sm bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Submit Karya
                    </a>
                @endif

                @if ($submission && $submission->status === 'rejected' && $event->isSubmissionOpen())
                    <a href="{{ route('voting.submit.form', $event) }}"
                        class="text-sm bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Edit & Kirim Ulang
                    </a>
                @endif
            </div>

            @if (!$submission)
                <div class="text-gray-500 py-6">
                    Belum ada karya yang di-submit untuk event ini.
                </div>
            @else
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <h3 class="font-bold text-lg">{{ $submission->title }}</h3>
                        <p class="text-sm text-gray-500 capitalize">{{ $submission->concentration }} ·
                            {{ $submission->updated_at->format('d M Y H:i') }}</p>
                    </div>
                    <span
                        class="text-xs font-semibold px-2 py-1 rounded {{ $submission->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : ($submission->status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                        {{ $submission->status === 'pending' ? 'Menunggu Review' : '' }}
                        {{ $submission->status === 'approved' ? 'Approved ✓' : '' }}
                        {{ $submission->status === 'rejected' ? 'Rejected ✗' : '' }}
                    </span>
                </div>

                @if ($submission->status === 'approved')
                    <div class="mt-4 p-4 bg-green-50 rounded text-sm text-green-900 border border-green-200">
                        <strong class="block mb-1">Selamat!</strong>
                        Karya Anda sudah disetujui admin dan tidak dapat diubah lagi.
                    </div>
                @endif

                @if ($submission->status === 'pending')
                    <div class="mt-4 p-4 bg-yellow-50 rounded text-sm text-yellow-900 border border-yellow-200">
                        Karya Anda sedang menunggu proses review admin.
                    </div>
                @endif

                @if ($submission->status === 'rejected')
                    <div class="mt-4 p-4 bg-red-50 rounded text-sm text-red-900 border border-red-200">
                        <strong class="block mb-1">Submission ditolak admin.</strong>
                        @if ($submission->admin_notes)
                            <span>Alasan: {{ $submission->admin_notes }}</span>
                        @else
                            <span>Silakan hubungi admin untuk detail alasan penolakan.</span>
                        @endif
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
