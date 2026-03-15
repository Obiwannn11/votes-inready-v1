<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Inready VOTES')</title>
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

        img {
            max-width: 100%;
            height: auto;
        }
    </style>
    @stack('styles')
</head>

<body class="bg-canvas text-ink min-h-screen font-body antialiased">
    <nav class="bg-surface border-b-4 border-ink shadow-sm">
        <div class="max-w-[1224px] mx-auto px-4 py-3 flex items-center justify-between gap-3">
            <div class="flex items-center gap-4">
                <a href="/"
                    class="font-display font-black text-lg sm:text-xl uppercase tracking-widest shrink-0 text-ink hover:underline">
                    Inready VOTES
                </a>
                <a href="{{ route('voting.landing') }}" class="hidden sm:block font-body text-sm font-semibold hover:underline border-b-2 border-transparent hover:border-ink">Event</a>
            </div>
            <div class="flex items-center justify-end gap-2 sm:gap-3 min-w-0">
                <a href="{{ route('voting.landing') }}" class="sm:hidden font-body text-xs font-semibold mr-2 underline">Event</a>
                @auth
                    @php
                        $currentSlug = \Illuminate\Support\Facades\Request::route('slug');
                    @endphp

                    @if ($currentSlug)
                        <x-button href="{{ route('voting.my-votes', $currentSlug) }}" variant="info" size="sm"
                            class="hidden sm:inline-flex">
                            Vote Saya
                        </x-button>
                    @endif

                    <div
                        class="flex items-center gap-2 px-3 py-1.5 border-2 border-ink bg-surface shadow-[2px_2px_0px_0px_var(--color-ink)] max-w-[120px] sm:max-w-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-ink shrink-0" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="square"
                            stroke-linejoin="miter">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                        <span
                            class="text-xs sm:text-sm font-display font-bold truncate">{{ \Illuminate\Support\Facades\Auth::user()->name }}</span>
                    </div>

                    <div x-data="{ showLogoutModal: false }" class="inline">
                        <x-button type="button" variant="danger" size="sm" @click="showLogoutModal = true"
                            class="px-2 sm:px-4 py-1.5 sm:py-2 text-[10px] sm:text-xs">
                            X
                        </x-button>

                        <div x-show="showLogoutModal" style="display: none;"
                            class="fixed inset-0 z-50 flex items-center justify-center p-4">
                            <div class="fixed inset-0 bg-ink/50 backdrop-blur-sm" @click="showLogoutModal = false"></div>

                            <div
                                class="bg-surface border-4 border-ink p-6 max-w-sm w-full shadow-[8px_8px_0px_0px_var(--color-ink)] z-10 p-6 relative">
                                <h3 class="font-display font-black text-xl mb-2 text-ink">Konfirmasi Logout</h3>
                                <p class="text-ink/80 font-body mb-6">Apakah Anda yakin ingin keluar dari InReady VOTES?</p>

                                <div class="flex justify-end gap-3">
                                    <x-button type="button" variant="outline" @click="showLogoutModal = false">
                                        Batal
                                    </x-button>
                                    <form method="POST" action="{{ route('voting.logout') }}" class="inline">
                                        @csrf
                                        <x-button type="submit" variant="danger">
                                            Ya, Logout
                                        </x-button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <x-button href="{{ route('voting.login') }}" variant="outline" size="sm">
                        Login untuk Vote
                    </x-button>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Custom Toast Notifications (Sonner Alternative) -->
    <div class="fixed bottom-6 right-6 z-50 flex flex-col gap-3 max-w-[320px]" style="pointer-events: none;">
        @if (\Illuminate\Support\Facades\Session::has('success'))
            <div x-data="{ show: true }" x-show="show" x-cloak style="pointer-events: auto;"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-y-12 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="translate-x-12 opacity-0"
                class="bg-surface border-2 border-ink p-4 shadow-[4px_4px_0px_0px_var(--color-ink)] flex gap-3 items-start"
                x-init="setTimeout(() => show = false, 5000)">
                <div
                    class="w-6 h-6 shrink-0 bg-success border-2 border-ink rounded-full flex items-center justify-center text-surface font-bold text-xs mt-0.5">
                    ✓</div>
                <div class="font-body text-sm font-medium pt-1 text-ink">
                    {{ \Illuminate\Support\Facades\Session::get('success') }}</div>
                <button type="button" class="ml-auto text-ink hover:text-red-600 font-bold px-1"
                    @click="show = false">&times;</button>
            </div>
        @endif

        @if (\Illuminate\Support\Facades\Session::has('error'))
            <div x-data="{ show: true }" x-show="show" x-cloak style="pointer-events: auto;"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-y-12 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="translate-x-12 opacity-0"
                class="bg-surface border-2 border-ink p-4 shadow-[4px_4px_0px_0px_var(--color-ink)] flex gap-3 items-start"
                x-init="setTimeout(() => show = false, 5000)">
                <div
                    class="w-6 h-6 shrink-0 bg-primary-red border-2 border-ink flex items-center justify-center text-surface font-bold text-xs mt-0.5">
                    !</div>
                <div class="font-body text-sm font-medium pt-1 text-ink">
                    {{ \Illuminate\Support\Facades\Session::get('error') }}</div>
                <button type="button" class="ml-auto text-ink hover:text-red-600 font-bold px-1"
                    @click="show = false">&times;</button>
            </div>
        @endif
    </div>

    <main class="max-w-[1224px] mx-auto px-4 py-8 sm:py-12">
        @yield('content')
    </main>

    @stack('scripts')
</body>

</html>
