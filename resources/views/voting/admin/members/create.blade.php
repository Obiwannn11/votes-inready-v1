@extends('voting.layouts.admin')
@section('title', 'Tambah Member')
@section('admin_nav_title', 'Tambah Member')
@section('admin_nav_breadcrumb')
    <a href="{{ route('voting.admin.members.index') }}" class="hover:text-ink transition-colors">Members</a>
    <span class="text-ink/40">&gt;</span>
    <span class="text-ink font-medium">Tambah Member</span>
@endsection

@section('content')
    <form method="POST" action="{{ route('voting.admin.members.store') }}"
        class="card bg-surface p-6 shadow-[6px_6px_0px_0px_var(--color-ink)] max-w-2xl border-2 border-ink">
        @csrf

        <h2 class="font-display font-black text-xl mb-6 pl-3 border-l-4 border-primary-blue uppercase">Detail Member</h2>

        <div class="form-group mb-6">
            <x-label for="name" required>Nama Lengkap</x-label>
            <x-input type="text" name="name" id="name" value="{{ old('name') }}" required
                placeholder="Masukkan nama lengkap" :error="$errors->has('name')" class="{{ $errors->has('name') ? 'error' : '' }}" />
            @error('name')
                <p class="form-helper error mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group mb-6">
            <x-label for="email" required>Email</x-label>
            <x-input type="email" name="email" id="email" value="{{ old('email') }}" required
                placeholder="Masukkan alamat email" :error="$errors->has('email')" class="{{ $errors->has('email') ? 'error' : '' }}" />
            @error('email')
                <p class="form-helper error mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group mb-8">
            <x-label for="password" required>Password</x-label>
            <x-input type="password" name="password" id="password" required minlength="8"
                placeholder="Masukkan password (minimal 8 karakter)" :error="$errors->has('password')" class="{{ $errors->has('password') ? 'error' : '' }}" />
            @error('password')
                <p class="form-helper error mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- <hr class="border-t-2 border-ink my-8"> --}}

        <div class="flex gap-3">
            <x-button type="submit" variant="primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="square" stroke-linejoin="miter">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                Simpan
            </x-button>
            <x-button variant="outline" href="{{ route('voting.admin.members.index') }}">Batal</x-button>
        </div>
    </form>
@endsection
