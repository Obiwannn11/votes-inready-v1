@extends('voting.layouts.admin')
@section('title', 'Tambah Member')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Tambah Member</h1>

    <form method="POST" action="{{ route('voting.admin.members.store') }}"
        class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 max-w-lg">
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
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Simpan</button>
            <a href="{{ route('voting.admin.members.index') }}" class="px-6 py-2 border rounded hover:bg-gray-50">Batal</a>
        </div>
    </form>
@endsection
