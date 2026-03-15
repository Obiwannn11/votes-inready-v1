<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Inready VOTES</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false }">
    <div class="md:hidden bg-gray-900 text-white px-4 py-3 flex items-center justify-between">
        <p class="font-semibold">VOTES Admin Panel</p>
        <button type="button" class="px-2 py-1 rounded bg-gray-800" @click="sidebarOpen = true"
            aria-label="Buka menu admin">
            Menu
        </button>
    </div>

    <div x-show="sidebarOpen" x-cloak class="md:hidden fixed inset-0 bg-black/40 z-30" @click="sidebarOpen = false"
        @keydown.escape.window="sidebarOpen = false">
    </div>

    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside
            class="fixed inset-y-0 left-0 z-40 w-64 bg-gray-900 text-white flex flex-col transform transition-transform duration-200 -translate-x-full md:static md:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
            <div class="p-4 bg-gray-950 font-bold text-lg flex items-center justify-between">
                <span>VOTES Admin Panel</span>
                <button type="button" class="md:hidden text-sm text-gray-300"
                    @click="sidebarOpen = false">Tutup</button>
            </div>

            <nav class="flex-1 p-4 space-y-2 text-sm">
                <a href="{{ route('voting.admin.events.index') }}" @click="sidebarOpen = false"
                    class="block p-2 rounded hover:bg-gray-800 {{ \Illuminate\Support\Facades\Request::is('vote/admin/events*') ? 'bg-gray-800' : '' }}">
                    Events
                </a>
                <a href="{{ route('voting.admin.members.index') }}" @click="sidebarOpen = false"
                    class="block p-2 rounded hover:bg-gray-800 {{ \Illuminate\Support\Facades\Request::is('vote/admin/members*') ? 'bg-gray-800' : '' }}">
                    Members
                </a>
            </nav>

            <div class="p-4 bg-gray-950 text-sm">
                <p class="mb-2 truncate">{{ \Illuminate\Support\Facades\Auth::user()->name }}</p>
                <form method="POST" action="{{ route('voting.logout') }}">
                    @csrf
                    <button class="text-red-400 hover:underline">Logout</button>
                </form>
            </div>
        </aside>

        {{-- Main content --}}
        <main class="flex-1 p-4 md:p-8 md:ml-0 w-full">
            @if (\Illuminate\Support\Facades\Session::has('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 text-green-700 flex items-start justify-between gap-3"
                    x-data="{ show: true }" x-show="show" x-cloak x-transition x-init="setTimeout(() => show = false, 5000)">
                    <span>{{ \Illuminate\Support\Facades\Session::get('success') }}</span>
                    <button type="button" class="text-green-700 hover:text-green-900" @click="show = false"
                        aria-label="Tutup notifikasi sukses">x</button>
                </div>
            @endif

            @if (\Illuminate\Support\Facades\Session::has('error'))
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 text-red-700 flex items-start justify-between gap-3"
                    x-data="{ show: true }" x-show="show" x-cloak x-transition x-init="setTimeout(() => show = false, 5000)">
                    <span>{{ \Illuminate\Support\Facades\Session::get('error') }}</span>
                    <button type="button" class="text-red-700 hover:text-red-900" @click="show = false"
                        aria-label="Tutup notifikasi error">x</button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>

</html>
