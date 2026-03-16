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

<body class="bg-canvas text-ink min-h-screen font-body antialiased flex flex-col md:flex-row" x-data="{ sidebarOpen: false }">
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

        <div class="p-5 bg-primary-yellow border-t-4 border-ink"></div>
    </aside>

    @php
        $rawTitle = trim($__env->yieldContent('title', 'Admin'));
        $adminNavTitle = trim($__env->yieldContent('admin_nav_title')) ?: $rawTitle;
    @endphp

    {{-- Main content --}}
    <main class="flex-1 md:ml-72 w-full bg-canvas min-h-screen">
        <header class="sticky top-0 z-20 bg-surface border-b-4 border-ink">
            <div class="h-[84px] px-4 md:px-8 flex items-center justify-between gap-4">
                <div class="min-w-0 flex-1 flex items-center gap-2 sm:gap-4">
                    <button type="button"
                        class="md:hidden px-3 py-1.5 border-2 border-ink bg-primary-yellow text-ink font-display font-bold text-xs uppercase tracking-wider shadow-[3px_3px_0px_0px_var(--color-ink)] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all cursor-pointer"
                        @click="sidebarOpen = true" aria-label="Buka menu sidebar admin">
                        Menu
                    </button>

                    <p class="font-display font-black text-base sm:text-lg uppercase tracking-wide truncate">
                        {{ $adminNavTitle }}
                    </p>

                    <div class="hidden sm:flex items-center gap-2 font-body text-xs text-grey truncate">
                        @hasSection('admin_nav_breadcrumb')
                            @yield('admin_nav_breadcrumb')
                        @else
                            <span class="text-ink/50">Admin</span>
                        @endif
                    </div>
                </div>

                <div class="relative shrink-0" x-data="{ sessionDropdownOpen: false }" @click.outside="sessionDropdownOpen = false"
                    @keydown.escape.window="sessionDropdownOpen = false">
                    <button type="button" @click="sessionDropdownOpen = !sessionDropdownOpen"
                        class="inline-flex items-center gap-2 px-3 py-2 border-2 border-ink bg-surface text-ink shadow-[4px_4px_0px_0px_var(--color-ink)] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all cursor-pointer max-w-[200px]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="square"
                            stroke-linejoin="miter">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span class="font-display font-bold text-xs uppercase tracking-wide truncate">
                            {{ \Illuminate\Support\Facades\Auth::user()->name }}
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 shrink-0 transition-transform"
                            :class="sessionDropdownOpen ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="square" stroke-linejoin="miter">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>

                    <div x-show="sessionDropdownOpen" x-cloak x-transition.origin.top.right
                        class="absolute right-0 mt-2 w-56 bg-surface border-2 border-ink shadow-[6px_6px_0px_0px_var(--color-ink)] p-3 z-30">
                        {{-- <p class="font-display font-bold text-[10px] uppercase tracking-widest text-grey mb-2">Active
                            Session</p>
                        <div class="px-3 py-2 border-2 border-ink bg-canvas font-body text-xs mb-3 truncate">
                            {{ \Illuminate\Support\Facades\Auth::user()->name }}
                        </div> --}}

                        <form method="POST" action="{{ route('voting.logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full px-3 py-2 border-2 border-ink bg-primary-red text-surface font-display font-bold text-xs uppercase tracking-wider shadow-[4px_4px_0px_0px_var(--color-ink)] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all cursor-pointer">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <div class="p-4 md:p-8">
            @if (\Illuminate\Support\Facades\Session::has('success'))
                <div class="bg-success/10 border-2 border-success p-4 mb-6 text-success flex items-start justify-between gap-3 font-medium shadow-md"
                    x-data="{ show: true }" x-show="show" x-cloak x-transition x-init="setTimeout(() => show = false, 5000)">
                    <span>{{ \Illuminate\Support\Facades\Session::get('success') }}</span>
                    <button type="button" class="text-success hover:text-ink font-bold cursor-pointer"
                        @click="show = false" aria-label="Tutup notifikasi sukses">x</button>
                </div>
            @endif

            @if (\Illuminate\Support\Facades\Session::has('error'))
                <div class="bg-primary-red/10 border-2 border-primary-red p-4 mb-6 text-primary-red flex items-start justify-between gap-3 font-medium shadow-md"
                    x-data="{ show: true }" x-show="show" x-cloak x-transition x-init="setTimeout(() => show = false, 5000)">
                    <span>{{ \Illuminate\Support\Facades\Session::get('error') }}</span>
                    <button type="button" class="text-primary-red hover:text-ink font-bold cursor-pointer"
                        @click="show = false" aria-label="Tutup notifikasi error">x</button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</body>

</html>
