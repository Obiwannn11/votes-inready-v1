<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Inready VOTES')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('styles')
</head>

<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white shadow-sm">
        <div class="max-w-6xl mx-auto px-4 py-3 flex justify-between items-center">
            <a href="{{ route('voting.landing') }}" class="font-bold text-lg">
                Inready VOTES
            </a>
            <div>
                @auth
                    <span class="text-sm text-gray-600 mr-3">{{ \Illuminate\Support\Facades\Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('voting.logout') }}" class="inline">
                        @csrf
                        <button class="text-sm text-red-600 hover:underline">Logout</button>
                    </form>
                @else
                    <a href="{{ route('voting.login') }}" class="text-sm text-blue-600 hover:underline">Login untuk Vote</a>
                @endauth
            </div>
        </div>
    </nav>

    @if (\Illuminate\Support\Facades\Session::has('success'))
        <div class="max-w-6xl mx-auto px-4 mt-4">
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
                {{ \Illuminate\Support\Facades\Session::get('success') }}
            </div>
        </div>
    @endif

    @if (\Illuminate\Support\Facades\Session::has('error'))
        <div class="max-w-6xl mx-auto px-4 mt-4">
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                {{ \Illuminate\Support\Facades\Session::get('error') }}
            </div>
        </div>
    @endif

    <main class="max-w-6xl mx-auto px-4 py-6">
        @yield('content')
    </main>

    @stack('scripts')
</body>

</html>
