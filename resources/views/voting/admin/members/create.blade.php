@extends('voting.layouts.admin')
@section('title', 'Tambah Member')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Tambah Member</h1>

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
            <button type="submit" class="bg-primary-yellow text-black px-4 py-2 text-sm shadow-sm border border-gray-100 p-6  font-bold hover:bg-yellow-500 hover:scale-103 transition-transform duration-200">Simpan</button>
            <a href="{{ route('voting.admin.members.index') }}" class="bg-white border-2 text-black px-4 py-2 text-sm shadow-sm border border-gray-100 p-6 hover: hover:bg-white-500 scale-103 transition-transform duration-200">Batal</a>
        </div>
    </form>
@endsection
