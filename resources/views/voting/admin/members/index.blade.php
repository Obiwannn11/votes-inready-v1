@extends('voting.layouts.admin')
@section('title', 'Members')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Data Members</h1>
        <a href="{{ route('voting.admin.members.create') }}"
            class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">+ Tambah Member</a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $member)
                    <tr class="border-b last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $member->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $member->email }}</td>
                        <td class="px-4 py-3">
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
