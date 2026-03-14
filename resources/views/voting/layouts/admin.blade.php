<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Inready VOTES</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside class="w-64 bg-gray-900 text-white flex flex-col">
            <div class="p-4 bg-gray-950 font-bold text-lg">
                VOTES Admin Panel
            </div>

            <nav class="flex-1 p-4 space-y-2 text-sm">
                <a href="{{ route('voting.admin.events.index') }}"
                    class="block p-2 rounded hover:bg-gray-800 {{ \Illuminate\Support\Facades\Request::is('vote/admin/events*') ? 'bg-gray-800' : '' }}">
                    Events
                </a>
                <a href="{{ route('voting.admin.members.index') }}"
                    class="block p-2 rounded hover:bg-gray-800 {{ \Illuminate\Support\Facades\Request::is('vote/admin/members*') ? 'bg-gray-800' : '' }}">
                    Members
                </a>
            </nav>

            <div class="p-4 bg-gray-950 text-sm">
                <p class="mb-2">{{ \Illuminate\Support\Facades\Auth::user()->name }}</p>
                <form method="POST" action="{{ route('voting.logout') }}">
                    @csrf
                    <button class="text-red-400 hover:underline">Logout</button>
                </form>
            </div>
        </aside>

        {{-- Main content --}}
        <main class="flex-1 p-8">
            @if (\Illuminate\Support\Facades\Session::has('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 text-green-700">
                    {{ \Illuminate\Support\Facades\Session::get('success') }}
                </div>
            @endif

            @if (\Illuminate\Support\Facades\Session::has('error'))
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 text-red-700">
                    {{ \Illuminate\Support\Facades\Session::get('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>

</html>
