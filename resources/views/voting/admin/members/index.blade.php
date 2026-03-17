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
            class="bg-primary-yellow text-black px-6 py-2 text-sm font-bold border border-black transition-all duration-200 
                shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] 
                hover:shadow-none hover:translate-x-[2px] hover:translate-y-[2px]">
            + Tambah Member
        </a>
    </div>

    <div class="bg-white shadow-lg border-2 border-black overflow-hidden">
    <table class="w-full text-sm text-left border-collapse">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-4 font-semibold text-gray-700 border-b-2 border-r-2 border-black">Nama</th>
                <th class="px-6 py-4 font-semibold text-gray-700 border-b-2 border-r-2 border-black">Email</th>
                <th class="px-6 py-4 font-semibold text-gray-700 border-b-2 border-r-2 border-black">Status</th>
                <th class="px-6 py-4 font-semibold text-gray-700 border-b-2 border-black">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($members as $member)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 font-medium text-gray-900 border-b-2 border-r-2 border-black">{{ $member->name }}</td>
                    <td class="px-6 py-4 text-gray-600 border-b-2 border-r-2 border-black">{{ $member->email }}</td>
                    <td class="px-6 py-4 border-b-2 border-r-2 border-black">
                        <span class="px-3 py-1 rounded-full text-xs font-medium {{ $member->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $member->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 border-b-2 border-black">
                        <form method="POST" action="{{ route('voting.admin.members.update', $member) }}">
                            @csrf
                            @method('PUT')
                            <button type="submit"
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-display font-bold uppercase tracking-widest border-2 border-ink shadow-[2px_2px_0px_0px_var(--color-ink)] transition-all duration-200 hover:shadow-none hover:translate-x-[1px] hover:translate-y-[1px]
                                {{ $member->is_active ? 'bg-primary-red text-surface' : 'bg-success text-surface' }}">
                                
                                <span class="mr-1.5">
                                    @if($member->is_active)
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    @else
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    @endif
                                </span>

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
