<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Inready VOTES')</title>
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

<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white shadow-sm">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
            <a href="{{ route('voting.landing') }}" class="font-bold text-base sm:text-lg shrink-0">
                Inready VOTES
            </a>
            <div class="flex items-center justify-end gap-2 sm:gap-3 min-w-0">
                @auth
                    @php
                        $currentSlug = \Illuminate\Support\Facades\Request::route('slug');
                    @endphp

                    @if ($currentSlug)
                        <a href="{{ route('voting.my-votes', $currentSlug) }}"
                            class="text-xs sm:text-sm text-blue-600 hover:underline shrink-0">
                            Vote Saya
                        </a>
                    @endif

                    <span
                        class="text-xs sm:text-sm text-gray-600 max-w-24 sm:max-w-none truncate">{{ \Illuminate\Support\Facades\Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('voting.logout') }}" class="inline">
                        @csrf
                        <button class="text-xs sm:text-sm text-red-600 hover:underline shrink-0">Logout</button>
                    </form>
                @else
                    <a href="{{ route('voting.login') }}" class="text-xs sm:text-sm text-blue-600 hover:underline">Login
                        untuk Vote</a>
                @endauth
            </div>
        </div>
    </nav>

    @if (\Illuminate\Support\Facades\Session::has('success'))
        <div class="max-w-6xl mx-auto px-4 mt-4" x-data="{ show: true }" x-show="show" x-cloak x-transition>
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded flex items-start justify-between gap-3"
                x-init="setTimeout(() => show = false, 5000)">
                <span>{{ \Illuminate\Support\Facades\Session::get('success') }}</span>
                <button type="button" class="text-green-700 hover:text-green-900" @click="show = false"
                    aria-label="Tutup notifikasi sukses">x</button>
            </div>
        </div>
    @endif

    @if (\Illuminate\Support\Facades\Session::has('error'))
        <div class="max-w-6xl mx-auto px-4 mt-4" x-data="{ show: true }" x-show="show" x-cloak x-transition>
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded flex items-start justify-between gap-3"
                x-init="setTimeout(() => show = false, 5000)">
                <span>{{ \Illuminate\Support\Facades\Session::get('error') }}</span>
                <button type="button" class="text-red-700 hover:text-red-900" @click="show = false"
                    aria-label="Tutup notifikasi error">x</button>
            </div>
        </div>
    @endif

    <main class="max-w-6xl mx-auto px-4 py-6">
        @yield('content')
    </main>

    @stack('scripts')
</body>

</html>
