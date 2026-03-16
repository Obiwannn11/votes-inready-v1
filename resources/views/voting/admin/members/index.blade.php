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
            class="bg-primary-yellow text-black px-4 py-2 text-sm shadow-sm border border-gray-100 p-6 hover:bg-primary-yellow hover:scale-103 transition-transform duration-200 font-bold">+ Tambah Member</a>
    </div>

    <div class="bg-white  shadow-sm border-2 border-black overflow-hidden">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 border-b-2 border-black">
                <tr>
                    <th class="px-4 py-3 border-r-2 border-black last:border-r-0">Nama</th>
                    <th class="px-4 py-3 border-r-2 border-black last:border-r-0">Email</th>
                    <th class="px-4 py-3 border-r-2 border-black last:border-r-0">Status</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $member)
                    <tr class="border-b-2 border-black last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium border-r-2 border-black last:border-r-0">{{ $member->name }}</td>
                        <td class="px-4 py-3 text-gray-500 border-r-2 border-black last:border-r-0">{{ $member->email }}
                        </td>
                        <td class="px-4 py-3 border-r-2 border-black last:border-r-0">
                            <span
                                class="px-2 py-1 rounded text-xs {{ $member->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $member->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('voting.admin.members.update', $member) }}">
                                @csrf
                                @method('PUT')
                                <button type="submit"
                                    class="text-sm {{ $member->is_active ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' }}">
                                    {{ $member->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-400">Belum ada data member.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection
