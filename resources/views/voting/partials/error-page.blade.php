@extends('voting.layouts.app')

@section('title', 'Error')

@section('content')
    <div class="text-center py-16">
        <p class="text-6xl font-bold text-gray-200 mb-4">{{ $code ?? '404' }}</p>
        <h1 class="text-xl font-bold mb-2">{{ $title ?? 'Halaman Tidak Ditemukan' }}</h1>
        <p class="text-gray-500 mb-6">{{ $message ?? 'Halaman yang kamu cari tidak ada atau sudah dihapus.' }}</p>
        <a href="{{ route('voting.landing') }}" class="text-blue-600 hover:underline">Kembali ke Inready VOTES</a>
    </div>
@endsection
