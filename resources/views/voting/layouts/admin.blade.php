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

<body class="bg-canvas text-ink min-h-screen font-body antialiased" x-data="{ sidebarOpen: false }">
    <div
        class="md:hidden bg-ink text-surface px-4 py-3 flex items-center justify-between border-b-4 border-primary-yellow">
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
            class="fixed inset-y-0 left-0 z-40 w-64 bg-ink text-surface border-r-4 border-ink flex flex-col transform transition-transform duration-200 -translate-x-full md:static md:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
            <div
                class="p-4 bg-ink border-b-2 border-surface/20 font-display font-black tracking-wide text-lg flex items-center justify-between text-primary-yellow">
                <span class="uppercase">Admin Panel</span>
                <button type="button" class="md:hidden text-sm text-surface/70"
                    @click="sidebarOpen = false">Tutup</button>
            </div>

            <nav class="flex-1 p-4 space-y-2 text-sm font-medium">
                <a href="{{ route('voting.admin.events.index') }}" @click="sidebarOpen = false"
                    class="block p-2 border-2 border-transparent hover:border-surface hover:bg-surface/10 {{ \Illuminate\Support\Facades\Request::is('vote/admin/events*') ? 'border-primary-yellow bg-primary-yellow text-ink' : '' }}">
                    Events
                </a>
                <a href="{{ route('voting.admin.members.index') }}" @click="sidebarOpen = false"
                    class="block p-2 border-2 border-transparent hover:border-surface hover:bg-surface/10 {{ \Illuminate\Support\Facades\Request::is('vote/admin/members*') ? 'border-primary-yellow bg-primary-yellow text-ink' : '' }}">
                    Members
                </a>
            </nav>

            <div class="p-4 bg-ink border-t-2 border-surface/20 text-sm">
                <p class="mb-2 truncate text-primary-yellow">{{ \Illuminate\Support\Facades\Auth::user()->name }}</p>
                <form method="POST" action="{{ route('voting.logout') }}">
                    @csrf
                    <button class="text-primary-red hover:underline font-bold">Logout</button>
                </form>
            </div>
        </aside>

        {{-- Main content --}}
        <main class="flex-1 p-4 md:p-8 md:ml-0 w-full bg-canvas">
            @if (\Illuminate\Support\Facades\Session::has('success'))
                <div class="bg-success/10 border-2 border-success p-4 mb-6 text-success flex items-start justify-between gap-3 font-medium shadow-[4px_4px_0px_0px_var(--color-ink)]"
                    x-data="{ show: true }" x-show="show" x-cloak x-transition x-init="setTimeout(() => show = false, 5000)">
                    <span>{{ \Illuminate\Support\Facades\Session::get('success') }}</span>
                    <button type="button" class="text-success hover:text-ink font-bold" @click="show = false"
                        aria-label="Tutup notifikasi sukses">x</button>
                </div>
            @endif

            @if (\Illuminate\Support\Facades\Session::has('error'))
                <div class="bg-primary-red/10 border-2 border-primary-red p-4 mb-6 text-primary-red flex items-start justify-between gap-3 font-medium shadow-[4px_4px_0px_0px_var(--color-ink)]"
                    x-data="{ show: true }" x-show="show" x-cloak x-transition x-init="setTimeout(() => show = false, 5000)">
                    <span>{{ \Illuminate\Support\Facades\Session::get('error') }}</span>
                    <button type="button" class="text-primary-red hover:text-ink font-bold" @click="show = false"
                        aria-label="Tutup notifikasi error">x</button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>

</html>
