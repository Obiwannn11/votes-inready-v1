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
        class="bg-white shadow-lg border border-gray-100 p-6">
        @csrf

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Nama Lengkap *</label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full border p-2" required>
            @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Email *</label>
            <input type="email" name="email" value="{{ old('email') }}" class="w-full border p-2" required>
            @error('email')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium mb-1">Password *</label>
            <input type="password" name="password" class="w-full border p-2" required minlength="8">
            @error('password')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-3">
            <button type="submit" 
                class="bg-primary-yellow text-black px-6 py-2 text-sm font-bold border border-black transition-all duration-100 
                    shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] 
                    hover:shadow-none hover:translate-x-[2px] hover:translate-y-[2px]">
                Simpan
            </button>

            <a href="{{ route('voting.admin.members.index') }}" 
                class="bg-white text-black px-6 py-2 text-sm font-bold border-2 border-black transition-all duration-100 text-center
                    shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] 
                    hover:shadow-none hover:translate-x-[2px] hover:translate-y-[2px]">
                Batal
            </a>
        </div>
    </form>
@endsection
