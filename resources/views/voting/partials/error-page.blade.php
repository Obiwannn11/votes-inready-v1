@extends('voting.layouts.app')

@section('title', 'Error - Inready VOTES')

@section('content')
    <div class="max-w-2xl mx-auto py-16 px-4">
        <div class="card bg-surface p-8 sm:p-12 text-center relative overflow-hidden flex flex-col items-center">
            <div class="card-accent circle bg-primary-red opacity-20 transform scale-150 -top-8 -right-8"></div>
            <div class="card-accent square bg-primary-yellow opacity-20 transform scale-150 -bottom-8 -left-8"></div>

            <p class="font-display font-black text-6xl sm:text-8xl text-ink mb-2 select-none"
                style="text-shadow: 4px 4px 0px var(--color-primary-yellow);">{{ $code ?? '404' }}</p>
            <h1 class="font-display font-black text-2xl sm:text-3xl uppercase tracking-tight mb-4">
                {{ $title ?? 'Halaman Tidak Ditemukan' }}</h1>

            <div class="w-16 h-2 bg-ink mx-auto mb-6"></div>

            <p class="font-body text-ink/80 text-base sm:text-lg mb-8 max-w-md mx-auto">
                {{ $message ?? 'Halaman yang kamu cari tidak ada atau sudah dihapus.' }}</p>

            <x-button href="{{ route('voting.landing') }}" variant="primary"
                class="w-full sm:w-auto justify-center z-10 relative">
                Kembali ke Beranda
            </x-button>
        </div>
    </div>
@endsection
