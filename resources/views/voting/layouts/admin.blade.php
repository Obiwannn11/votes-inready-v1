<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Inready VOTES</title>
    <!-- Google Fonts for InReady Bauhaus Design System -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;700;900&family=Poppins:wght@400;500;600&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-canvas text-ink min-h-screen font-body antialiased flex flex-col md:flex-row" x-data="{ sidebarOpen: false, logoutModalOpen: false }">
    <!-- Mobile Header -->
    <div
        class="md:hidden bg-primary-yellow text-ink px-4 py-3 flex items-center justify-between border-b-4 border-ink sticky top-0 z-20">
        <p class="font-display font-black tracking-wide text-xl uppercase">VOTES Admin</p>
        <button type="button"
            class="px-4 py-2 border-2 border-ink bg-primary-yellow text-ink font-display font-bold shadow-md active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all uppercase"
            @click="sidebarOpen = true" aria-label="Buka menu admin">
            Menu
        </button>
    </div>

    <!-- Mobile Overlay -->
    <div x-show="sidebarOpen" x-cloak class="md:hidden fixed inset-0 bg-ink/50 backdrop-blur-sm z-30"
        @click="sidebarOpen = false" @keydown.escape.window="sidebarOpen = false">
    </div>

    {{-- Sidebar --}}
    <aside
        class="fixed inset-y-0 left-0 z-40 w-72 bg-primary-yellow text-ink border-r-4 border-ink flex flex-col transform transition-transform duration-200 -translate-x-full md:translate-x-0"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        <div
            class="p-5 bg-primary-yellow border-b-4 border-ink font-display font-black tracking-wide text-2xl flex items-center justify-between text-ink">
            <span class="uppercase">Admin Panel</span>
            <button type="button"
                class="md:hidden px-3 py-1 border-2 border-ink bg-primary-red text-surface font-display font-bold shadow-sm active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all uppercase"
                @click="sidebarOpen = false">X</button>
        </div>

        <nav class="flex-1 p-5 space-y-4 text-base font-display font-bold overflow-y-auto">
            <a href="{{ route('voting.admin.events.index') }}" @click="sidebarOpen = false"
                class="block w-full text-left px-4 py-3 border-2 border-ink text-ink shadow-md transition-all duration-200 ease-out hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-none active:translate-x-0.5 active:translate-y-0.5 active:shadow-none {{ \Illuminate\Support\Facades\Request::is('vote/admin/events*') ? 'bg-black text-white shadow-none' : 'bg-primary-yellow' }}">
                EVENTS
            </a>
            <a href="{{ route('voting.admin.members.index') }}" @click="sidebarOpen = false"
                class="block w-full text-left px-4 py-3 border-2 border-ink text-ink shadow-md transition-all duration-200 ease-out hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-none active:translate-x-0.5 active:translate-y-0.5 active:shadow-none {{ \Illuminate\Support\Facades\Request::is('vote/admin/members*') ? 'bg-black text-white shadow-none' : 'bg-primary-yellow' }}">
                MEMBERS
            </a>
        </nav>

        <div class="p-5 bg-primary-yellow border-t-4 border-ink flex flex-col gap-4">
            <div
                class="block w-full text-center px-4 py-3 border-2 border-ink bg-primary-yellow text-ink shadow-[4px_4px_0px_0px_var(--color-ink)] transition-all duration-200 ease-out hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-none active:translate-x-[2px] active:translate-y-[2px] active:shadow-none font-display font-bold truncate cursor-default">
                👤 {{ \Illuminate\Support\Facades\Auth::user()->name }}
            </div>
            <button type="button" @click="logoutModalOpen = true"
                class="block w-full text-center px-4 py-3 border-2 border-ink bg-primary-red text-white shadow-[4px_4px_0px_0px_var(--color-ink)] transition-all duration-200 ease-out hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-none active:translate-x-[2px] active:translate-y-[2px] active:shadow-none font-display font-bold uppercase tracking-wider">
                Logout
            </button>
        </div>
    </aside>

    {{-- Main content --}}
    <main class="flex-1 p-4 md:p-8 md:ml-72 w-full bg-canvas min-h-screen">
        @if (\Illuminate\Support\Facades\Session::has('success'))
            <div class="bg-success/10 border-2 border-success p-4 mb-6 text-success flex items-start justify-between gap-3 font-medium shadow-md"
                x-data="{ show: true }" x-show="show" x-cloak x-transition x-init="setTimeout(() => show = false, 5000)">
                <span>{{ \Illuminate\Support\Facades\Session::get('success') }}</span>
                <button type="button" class="text-success hover:text-ink font-bold" @click="show = false"
                    aria-label="Tutup notifikasi sukses">x</button>
            </div>
        @endif

        @if (\Illuminate\Support\Facades\Session::has('error'))
            <div class="bg-primary-red/10 border-2 border-primary-red p-4 mb-6 text-primary-red flex items-start justify-between gap-3 font-medium shadow-md"
                x-data="{ show: true }" x-show="show" x-cloak x-transition x-init="setTimeout(() => show = false, 5000)">
                <span>{{ \Illuminate\Support\Facades\Session::get('error') }}</span>
                <button type="button" class="text-primary-red hover:text-ink font-bold" @click="show = false"
                    aria-label="Tutup notifikasi error">x</button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Logout Confirmation Modal -->
    <div x-show="logoutModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div x-show="logoutModalOpen" x-transition.opacity class="fixed inset-0 bg-ink/60 backdrop-blur-sm"
            @click="logoutModalOpen = false"></div>

        <!-- Modal Content -->
        <div x-show="logoutModalOpen" x-transition.scale.95
            class="relative bg-surface border-4 border-ink shadow-xl w-full max-w-md p-6 sm:p-8 text-center"
            @keydown.escape.window="logoutModalOpen = false">
            <div
                class="w-16 h-16 mx-auto mb-4 border-4 border-ink rounded-full flex items-center justify-center bg-primary-red text-white">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                    </path>
                </svg>
            </div>
            <h3 class="font-display font-black text-2xl uppercase mb-2">Konfirmasi Logout</h3>
            <p class="font-body text-ink/80 mb-8">Apakah Anda yakin ingin keluar dari sesi admin?</p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button type="button" @click="logoutModalOpen = false"
                    class="px-6 py-3 border-2 border-ink bg-surface text-ink font-display font-bold shadow-md active:translate-x-0.5 active:translate-y-0.5 active:shadow-none hover:-translate-y-1 transition-all uppercase">
                    Batal
                </button>
                <form method="POST" action="{{ route('voting.logout') }}" class="m-0">
                    @csrf
                    <button type="submit"
                        class="w-full px-6 py-3 border-2 border-ink bg-primary-red text-white font-display font-bold shadow-md active:translate-x-0.5 active:translate-y-0.5 active:shadow-none hover:-translate-y-1 transition-all uppercase">
                        Ya, Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
