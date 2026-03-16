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
        class="bg-white shadow-sm border border-gray-100 p-6">
        @csrf

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Nama Lengkap *</label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full border p-2 rounded" required>
            @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Email *</label>
            <input type="email" name="email" value="{{ old('email') }}" class="w-full border p-2 rounded" required>
            @error('email')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium mb-1">Password *</label>
            <input type="password" name="password" class="w-full border p-2 rounded" required minlength="8">
            @error('password')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-primary-yellow text-black px-6 py-2 text-sm font-bold shadow-sm border border-black hover:bg-yellow-500 hover:scale-105 transition-all duration-200">Simpan</button>
            <a href="{{ route('voting.admin.members.index') }}" class="bg-white text-black px-6 py-2 text-sm font-bold shadow-sm border-2 border-black hover:bg-gray-100 hover:scale-105 transition-all duration-200 text-center">Batal</a>
        </div>
    </form>
@endsection
