@extends('voting.layouts.admin')
@section('title', 'Members')
@section('admin_nav_title', 'Data Members')
@section('admin_nav_breadcrumb')
    <a href="{{ route('voting.admin.members.index') }}" class="hover:text-ink transition-colors">Members</a>
    <span class="text-ink/40">&gt;</span>
    <span class="text-ink font-medium">Semua Member</span>
@endsection

@section('content')
    <div class="flex justify-end items-center mb-6">
        <a href="{{ route('voting.admin.members.create') }}"
            class="bg-primary-yellow text-black px-6 py-2 text-sm font-bold shadow-sm border border-black hover:bg-yellow-500 hover:scale-105 transition-all duration-200">+ Tambah Member</a>
    </div>

    <div class="bg-white shadow-md border border-gray-200 overflow-hidden">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-4 font-semibold text-gray-700">Nama</th>
                    <th class="px-6 py-4 font-semibold text-gray-700">Email</th>
                    <th class="px-6 py-4 font-semibold text-gray-700">Status</th>
                    <th class="px-6 py-4 font-semibold text-gray-700">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $member)
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $member->name }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $member->email }}</td>
                        <td class="px-6 py-4">
                            <span
                                class="px-3 py-1 rounded-full text-xs font-medium {{ $member->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $member->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <form method="POST" action="{{ route('voting.admin.members.update', $member) }}">
                                @csrf
                                @method('PUT')
                                <button type="submit"
                                    class="text-sm font-medium {{ $member->is_active ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' }} transition-colors">
                                    {{ $member->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-400">Belum ada data member.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection
