# FASE 1: Admin Panel

**Produk:** Inready VOTES  
**Estimasi:** 2-3 hari kerja efektif  
**Prasyarat:** FASE 0 checklist 100% centang  
**Output:** Admin bisa login, buat event, manage member, review submission

---

## Tujuan Fase Ini

Admin punya control panel untuk mengelola seluruh voting. Tanpa ini, tidak ada event, tidak ada yang bisa disubmit, tidak ada yang bisa di-vote.

---

## Step 1: Middleware Admin Voting

**Buat middleware:**
```bash
php artisan make:middleware EnsureVotingAdmin
```

**Edit `app/Http/Middleware/EnsureVotingAdmin.php`:**
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVotingAdmin extends Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            if ($request->expectsJson()) {
                abort(403, 'Unauthorized.');
            }
            return redirect()->route('voting.login')
                ->with('error', 'Akses khusus admin.');
        }

        return $next($request);
    }
}
```

**Daftarkan alias:**

Laravel 11 — `bootstrap/app.php`:
```php
$middleware->alias([
    'inertia' => \App\Http\Middleware\HandleInertiaRequests::class,
    'voting.admin' => \App\Http\Middleware\EnsureVotingAdmin::class,
]);
```

Laravel 10 — `Kernel.php`:
```php
protected $middlewareAliases = [
    'voting.admin' => \App\Http\Middleware\EnsureVotingAdmin::class,
];
```

---

## Step 2: Admin Layout

**Buat `resources/views/voting/layouts/admin.blade.php`:**
```html
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Inready VOTES</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside class="w-64 bg-white shadow-sm hidden md:block">
            <div class="p-4 border-b">
                <a href="{{ route('voting.landing') }}" class="font-bold text-lg">Inready VOTES</a>
                <p class="text-xs text-gray-400">Admin Panel</p>
            </div>
            <nav class="p-4 space-y-1">
                <a href="{{ route('voting.admin.events.index') }}"
                   class="block px-3 py-2 rounded text-sm {{ request()->routeIs('voting.admin.events.*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                    Events
                </a>
                <a href="{{ route('voting.admin.members') }}"
                   class="block px-3 py-2 rounded text-sm {{ request()->routeIs('voting.admin.members*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                    Members
                </a>
            </nav>
            <div class="absolute bottom-0 w-64 p-4 border-t">
                <p class="text-sm text-gray-500 truncate">{{ auth()->user()->name }}</p>
                <form method="POST" action="{{ route('voting.logout') }}">
                    @csrf
                    <button class="text-sm text-red-500 hover:underline mt-1">Logout</button>
                </form>
            </div>
        </aside>

        {{-- Main content --}}
        <main class="flex-1 p-6">
            {{-- Mobile header --}}
            <div class="md:hidden mb-4 flex justify-between items-center">
                <span class="font-bold">Inready VOTES Admin</span>
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="text-sm bg-gray-200 px-3 py-1 rounded">Menu</button>
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white shadow-lg rounded z-50">
                        <a href="{{ route('voting.admin.events.index') }}" class="block px-4 py-2 text-sm hover:bg-gray-50">Events</a>
                        <a href="{{ route('voting.admin.members') }}" class="block px-4 py-2 text-sm hover:bg-gray-50">Members</a>
                    </div>
                </div>
            </div>

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
```

---

## Step 3: Admin Event CRUD

**Buat controller:**
```bash
php artisan make:controller Voting/AdminEventController --resource
```

**Edit `app/Http/Controllers/Voting/AdminEventController.php`:**
```php
<?php

namespace App\Http\Controllers\Voting;

use App\Http\Controllers\Controller;
use App\Models\VotingEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminEventController extends Controller
{
    public function index()
    {
        $events = VotingEvent::latest()->get();
        return view('voting.admin.events.index', compact('events'));
    }

    public function create()
    {
        return view('voting.admin.events.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'               => 'required|string|max:255',
            'description'         => 'nullable|string|max:5000',
            'submission_deadline' => 'nullable|date|after:now',
        ]);

        $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(5);

        VotingEvent::create($validated);

        return redirect()->route('voting.admin.events.index')
            ->with('success', 'Event berhasil dibuat.');
    }

    public function show(VotingEvent $event)
    {
        $event->load(['submissions' => function ($q) {
            $q->latest();
        }]);

        $stats = [
            'total_submissions' => $event->submissions->count(),
            'pending'           => $event->submissions->where('status', 'pending')->count(),
            'approved'          => $event->submissions->where('status', 'approved')->count(),
            'rejected'          => $event->submissions->where('status', 'rejected')->count(),
            'total_votes'       => $event->votes()->count(),
        ];

        return view('voting.admin.events.show', compact('event', 'stats'));
    }

    public function edit(VotingEvent $event)
    {
        return view('voting.admin.events.edit', compact('event'));
    }

    public function update(Request $request, VotingEvent $event)
    {
        $validated = $request->validate([
            'title'               => 'required|string|max:255',
            'description'         => 'nullable|string|max:5000',
            'submission_deadline' => 'nullable|date',
        ]);

        $event->update($validated);

        return redirect()->route('voting.admin.events.show', $event)
            ->with('success', 'Event berhasil diupdate.');
    }

    public function changeStatus(Request $request, VotingEvent $event)
    {
        $newStatus = $request->validate([
            'status' => 'required|in:draft,submission_open,voting_open,closed,archived'
        ])['status'];

        if (!$event->canTransitionTo($newStatus)) {
            return back()->with('error', "Tidak bisa ubah dari {$event->status} ke {$newStatus}.");
        }

        $updates = ['status' => $newStatus];
        if ($newStatus === 'voting_open') {
            $updates['voting_opened_at'] = now();
        } elseif ($newStatus === 'closed') {
            $updates['voting_closed_at'] = now();
        }

        $event->update($updates);

        return back()->with('success', "Status diubah ke {$newStatus}.");
    }
}
```

**Buat views:**

`resources/views/voting/admin/events/index.blade.php`:
```html
@extends('voting.layouts.admin')
@section('title', 'Events')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Voting Events</h1>
    <a href="{{ route('voting.admin.events.create') }}"
       class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">
        + Buat Event
    </a>
</div>

@forelse($events as $event)
<div class="bg-white rounded-lg shadow-sm p-4 mb-3">
    <div class="flex justify-between items-start">
        <div>
            <a href="{{ route('voting.admin.events.show', $event) }}" class="font-semibold text-lg hover:text-blue-600">
                {{ $event->title }}
            </a>
            <p class="text-sm text-gray-500">Slug: {{ $event->slug }}</p>
        </div>
        <span class="text-xs font-medium px-2 py-1 rounded
            {{ $event->status === 'draft' ? 'bg-gray-100 text-gray-600' : '' }}
            {{ $event->status === 'submission_open' ? 'bg-yellow-100 text-yellow-700' : '' }}
            {{ $event->status === 'voting_open' ? 'bg-green-100 text-green-700' : '' }}
            {{ $event->status === 'closed' ? 'bg-red-100 text-red-700' : '' }}
            {{ $event->status === 'archived' ? 'bg-gray-100 text-gray-400' : '' }}">
            {{ strtoupper($event->status) }}
        </span>
    </div>
</div>
@empty
<div class="text-center text-gray-400 py-12">Belum ada event. Buat event pertama.</div>
@endforelse
@endsection
```

`resources/views/voting/admin/events/create.blade.php`:
```html
@extends('voting.layouts.admin')
@section('title', 'Buat Event')

@section('content')
<h1 class="text-2xl font-bold mb-6">Buat Event Baru</h1>

<form method="POST" action="{{ route('voting.admin.events.store') }}" class="bg-white rounded-lg shadow-sm p-6 max-w-2xl">
    @csrf

    <div class="mb-4">
        <label class="block text-sm font-medium mb-1">Judul Event *</label>
        <input type="text" name="title" value="{{ old('title') }}" required
               class="w-full border rounded px-3 py-2 @error('title') border-red-500 @enderror">
        @error('title') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="mb-4">
        <label class="block text-sm font-medium mb-1">Deskripsi</label>
        <textarea name="description" rows="4"
                  class="w-full border rounded px-3 py-2">{{ old('description') }}</textarea>
    </div>

    <div class="mb-6">
        <label class="block text-sm font-medium mb-1">Deadline Submission</label>
        <input type="datetime-local" name="submission_deadline" value="{{ old('submission_deadline') }}"
               class="border rounded px-3 py-2">
        <p class="text-gray-400 text-xs mt-1">Opsional. Kosongkan jika tidak ada deadline.</p>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Simpan</button>
        <a href="{{ route('voting.admin.events.index') }}" class="px-6 py-2 border rounded hover:bg-gray-50">Batal</a>
    </div>
</form>
@endsection
```

`resources/views/voting/admin/events/show.blade.php`:
```html
@extends('voting.layouts.admin')
@section('title', $event->title)

@section('content')
<div class="flex justify-between items-start mb-6">
    <div>
        <h1 class="text-2xl font-bold">{{ $event->title }}</h1>
        <p class="text-gray-500 text-sm">Status: <strong>{{ strtoupper($event->status) }}</strong></p>
    </div>
    <a href="{{ route('voting.admin.events.edit', $event) }}" class="text-sm text-blue-600 hover:underline">Edit</a>
</div>

{{-- Status controls --}}
<div class="bg-white rounded-lg shadow-sm p-4 mb-6">
    <h2 class="font-semibold mb-3">Kontrol Status</h2>
    <div class="flex flex-wrap gap-2">
        @php
            $transitions = [
                'draft' => ['submission_open' => 'Buka Submission'],
                'submission_open' => ['voting_open' => 'Buka Voting', 'draft' => 'Kembali ke Draft'],
                'voting_open' => ['closed' => 'Tutup Voting'],
                'closed' => ['archived' => 'Archive'],
            ];
            $available = $transitions[$event->status] ?? [];
        @endphp

        @foreach($available as $status => $label)
            <form method="POST" action="{{ route('voting.admin.change-status', $event) }}">
                @csrf
                <input type="hidden" name="status" value="{{ $status }}">
                <button type="submit"
                    class="px-4 py-2 rounded text-sm
                        {{ $status === 'closed' ? 'bg-red-600 text-white hover:bg-red-700' : 'bg-blue-600 text-white hover:bg-blue-700' }}"
                    onclick="return confirm('Ubah status ke {{ $status }}?')">
                    {{ $label }}
                </button>
            </form>
        @endforeach

        @if(empty($available))
            <p class="text-gray-400 text-sm">Tidak ada aksi tersedia untuk status ini.</p>
        @endif
    </div>

    @if($event->isSubmissionOpen())
        <div class="mt-3 p-3 bg-blue-50 rounded text-sm">
            <strong>Link submit karya:</strong>
            <code class="bg-white px-2 py-1 rounded text-xs">{{ route('voting.submit.form', $event->slug) }}</code>
        </div>
    @endif
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
    @foreach(['total_submissions' => 'Total', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'total_votes' => 'Votes'] as $key => $label)
    <div class="bg-white rounded-lg shadow-sm p-4 text-center">
        <p class="text-2xl font-bold">{{ $stats[$key] }}</p>
        <p class="text-xs text-gray-500">{{ $label }}</p>
    </div>
    @endforeach
</div>

{{-- Submissions list --}}
<div class="bg-white rounded-lg shadow-sm p-4">
    <div class="flex justify-between items-center mb-4">
        <h2 class="font-semibold">Submissions</h2>
        <a href="{{ route('voting.admin.submissions', $event) }}" class="text-sm text-blue-600 hover:underline">Lihat semua →</a>
    </div>

    @forelse($event->submissions->take(10) as $sub)
    <div class="flex justify-between items-center py-2 border-b last:border-0">
        <div>
            <span class="font-medium">{{ $sub->title }}</span>
            <span class="text-sm text-gray-400">oleh {{ $sub->candidate_name }}</span>
        </div>
        <span class="text-xs px-2 py-1 rounded
            {{ $sub->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
            {{ $sub->status === 'approved' ? 'bg-green-100 text-green-700' : '' }}
            {{ $sub->status === 'rejected' ? 'bg-red-100 text-red-700' : '' }}">
            {{ $sub->status }}
        </span>
    </div>
    @empty
    <p class="text-gray-400 text-sm py-4 text-center">Belum ada submission.</p>
    @endforelse
</div>
@endsection
```

`resources/views/voting/admin/events/edit.blade.php`:
```html
@extends('voting.layouts.admin')
@section('title', 'Edit: ' . $event->title)

@section('content')
<h1 class="text-2xl font-bold mb-6">Edit Event</h1>

<form method="POST" action="{{ route('voting.admin.events.update', $event) }}" class="bg-white rounded-lg shadow-sm p-6 max-w-2xl">
    @csrf
    @method('PUT')

    <div class="mb-4">
        <label class="block text-sm font-medium mb-1">Judul Event *</label>
        <input type="text" name="title" value="{{ old('title', $event->title) }}" required
               class="w-full border rounded px-3 py-2">
        @error('title') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="mb-4">
        <label class="block text-sm font-medium mb-1">Deskripsi</label>
        <textarea name="description" rows="4"
                  class="w-full border rounded px-3 py-2">{{ old('description', $event->description) }}</textarea>
    </div>

    <div class="mb-6">
        <label class="block text-sm font-medium mb-1">Deadline Submission</label>
        <input type="datetime-local" name="submission_deadline"
               value="{{ old('submission_deadline', $event->submission_deadline?->format('Y-m-d\TH:i')) }}"
               class="border rounded px-3 py-2">
    </div>

    <div class="flex gap-3">
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Update</button>
        <a href="{{ route('voting.admin.events.show', $event) }}" class="px-6 py-2 border rounded hover:bg-gray-50">Batal</a>
    </div>
</form>
@endsection
```

---

## Step 4: Admin Submission Review

**Buat controller:**
```bash
php artisan make:controller Voting/AdminSubmissionController
```

```php
<?php

namespace App\Http\Controllers\Voting;

use App\Http\Controllers\Controller;
use App\Models\VotingEvent;
use App\Models\Submission;
use Illuminate\Http\Request;

class AdminSubmissionController extends Controller
{
    public function index(VotingEvent $event, Request $request)
    {
        $query = $event->submissions()->with('screenshots')->latest();

        if ($request->has('status') && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $request->status);
        }

        $submissions = $query->get();

        return view('voting.admin.submissions.index', compact('event', 'submissions'));
    }

    public function show(Submission $submission)
    {
        $submission->load(['event', 'screenshots']);
        return view('voting.admin.submissions.show', compact('submission'));
    }

    public function review(Request $request, Submission $submission)
    {
        $validated = $request->validate([
            'status'      => 'required|in:approved,rejected',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $submission->update($validated);

        $label = $validated['status'] === 'approved' ? 'di-approve' : 'di-reject';
        return back()->with('success', "Karya \"{$submission->title}\" berhasil {$label}.");
    }
}
```

`resources/views/voting/admin/submissions/index.blade.php`:
```html
@extends('voting.layouts.admin')
@section('title', 'Submissions: ' . $event->title)

@section('content')
<h1 class="text-2xl font-bold mb-2">Submissions</h1>
<p class="text-gray-500 mb-6">{{ $event->title }}</p>

{{-- Filter --}}
<div class="flex gap-2 mb-4">
    <a href="{{ route('voting.admin.submissions', $event) }}"
       class="px-3 py-1 rounded text-sm {{ !request('status') ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">Semua</a>
    @foreach(['pending', 'approved', 'rejected'] as $s)
    <a href="{{ route('voting.admin.submissions', [$event, 'status' => $s]) }}"
       class="px-3 py-1 rounded text-sm {{ request('status') === $s ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">{{ ucfirst($s) }}</a>
    @endforeach
</div>

@forelse($submissions as $sub)
<div class="bg-white rounded-lg shadow-sm p-4 mb-3 flex gap-4">
    <img src="{{ Storage::url($sub->thumbnail_path) }}" class="w-20 h-20 object-cover rounded" alt="">
    <div class="flex-1">
        <div class="flex justify-between">
            <a href="{{ route('voting.admin.submission.show', $sub) }}" class="font-semibold hover:text-blue-600">{{ $sub->title }}</a>
            <span class="text-xs px-2 py-1 rounded
                {{ $sub->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                {{ $sub->status === 'approved' ? 'bg-green-100 text-green-700' : '' }}
                {{ $sub->status === 'rejected' ? 'bg-red-100 text-red-700' : '' }}">
                {{ $sub->status }}
            </span>
        </div>
        <p class="text-sm text-gray-500">{{ $sub->candidate_name }} · {{ $sub->concentration }} · {{ $sub->created_at->diffForHumans() }}</p>

        @if($sub->status === 'pending')
        <div class="flex gap-2 mt-2">
            <form method="POST" action="{{ route('voting.admin.submission.review', $sub) }}">
                @csrf @method('PUT')
                <input type="hidden" name="status" value="approved">
                <button class="text-xs bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">Approve</button>
            </form>
            <form method="POST" action="{{ route('voting.admin.submission.review', $sub) }}">
                @csrf @method('PUT')
                <input type="hidden" name="status" value="rejected">
                <button class="text-xs bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Reject</button>
            </form>
        </div>
        @endif
    </div>
</div>
@empty
<div class="text-center text-gray-400 py-12">Belum ada submission.</div>
@endforelse
@endsection
```

`resources/views/voting/admin/submissions/show.blade.php`:
```html
@extends('voting.layouts.admin')
@section('title', $submission->title)

@section('content')
<a href="{{ route('voting.admin.submissions', $submission->event) }}" class="text-sm text-blue-600 hover:underline mb-4 inline-block">← Kembali</a>

<div class="bg-white rounded-lg shadow-sm p-6 max-w-3xl">
    <div class="flex justify-between items-start mb-4">
        <div>
            <h1 class="text-2xl font-bold">{{ $submission->title }}</h1>
            <p class="text-gray-500">{{ $submission->candidate_name }} · {{ $submission->candidate_email }}</p>
            <span class="text-xs font-medium px-2 py-1 rounded bg-blue-100 text-blue-700 mt-1 inline-block">
                {{ $submission->concentration }}
            </span>
        </div>
        <span class="text-xs px-2 py-1 rounded
            {{ $submission->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
            {{ $submission->status === 'approved' ? 'bg-green-100 text-green-700' : '' }}
            {{ $submission->status === 'rejected' ? 'bg-red-100 text-red-700' : '' }}">
            {{ strtoupper($submission->status) }}
        </span>
    </div>

    {{-- Thumbnail --}}
    <img src="{{ Storage::url($submission->thumbnail_path) }}" class="w-full max-h-96 object-contain rounded mb-4 bg-gray-100" alt="">

    {{-- Screenshots --}}
    @if($submission->screenshots->count())
    <div class="grid grid-cols-3 gap-2 mb-4">
        @foreach($submission->screenshots as $ss)
        <img src="{{ Storage::url($ss->file_path) }}" class="w-full h-32 object-cover rounded" alt="">
        @endforeach
    </div>
    @endif

    {{-- Description --}}
    <div class="mb-4">
        <h3 class="font-semibold text-sm text-gray-500 mb-1">Deskripsi</h3>
        <p class="whitespace-pre-line">{{ $submission->description }}</p>
    </div>

    {{-- Demo URL --}}
    @if($submission->demo_url)
    <div class="mb-4">
        <h3 class="font-semibold text-sm text-gray-500 mb-1">Demo</h3>
        <a href="{{ $submission->demo_url }}" target="_blank" class="text-blue-600 hover:underline">{{ $submission->demo_url }}</a>
    </div>
    @endif

    {{-- Review actions --}}
    <div class="border-t pt-4 mt-4">
        <h3 class="font-semibold text-sm mb-3">Review</h3>
        <form method="POST" action="{{ route('voting.admin.submission.review', $submission) }}">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="block text-sm mb-1">Catatan (opsional)</label>
                <textarea name="admin_notes" rows="2" class="w-full border rounded px-3 py-2 text-sm">{{ old('admin_notes', $submission->admin_notes) }}</textarea>
            </div>

            <div class="flex gap-2">
                <button type="submit" name="status" value="approved" class="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700">Approve</button>
                <button type="submit" name="status" value="rejected" class="bg-red-600 text-white px-4 py-2 rounded text-sm hover:bg-red-700">Reject</button>
            </div>
        </form>
    </div>
</div>
@endsection
```

---

## Step 5: Admin Member Management

**Buat controller:**
```bash
php artisan make:controller Voting/AdminMemberController
```

```php
<?php

namespace App\Http\Controllers\Voting;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminMemberController extends Controller
{
    public function index()
    {
        $members = User::where('role', 'member')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('voting.admin.members.index', compact('members'));
    }

    public function create()
    {
        return view('voting.admin.members.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'role'      => 'member',
            'is_active' => true,
        ]);

        return redirect()->route('voting.admin.members')
            ->with('success', "Member {$validated['name']} berhasil dibuat.");
    }

    public function toggleActive(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "{$user->name} berhasil {$status}.");
    }
}
```

`resources/views/voting/admin/members/index.blade.php`:
```html
@extends('voting.layouts.admin')
@section('title', 'Members')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Members</h1>
    <a href="{{ route('voting.admin.members.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">+ Tambah Member</a>
</div>

<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="text-left px-4 py-3">Nama</th>
                <th class="text-left px-4 py-3">Email</th>
                <th class="text-left px-4 py-3">Status</th>
                <th class="text-left px-4 py-3">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($members as $member)
            <tr class="border-t">
                <td class="px-4 py-3">{{ $member->name }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $member->email }}</td>
                <td class="px-4 py-3">
                    <span class="text-xs px-2 py-1 rounded {{ $member->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $member->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <form method="POST" action="{{ route('voting.admin.members.toggle', $member) }}">
                        @csrf @method('PUT')
                        <button class="text-xs text-blue-600 hover:underline">
                            {{ $member->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-center py-8 text-gray-400">Belum ada member.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
```

`resources/views/voting/admin/members/create.blade.php`:
```html
@extends('voting.layouts.admin')
@section('title', 'Tambah Member')

@section('content')
<h1 class="text-2xl font-bold mb-6">Tambah Member</h1>

<form method="POST" action="{{ route('voting.admin.members.store') }}" class="bg-white rounded-lg shadow-sm p-6 max-w-lg">
    @csrf

    <div class="mb-4">
        <label class="block text-sm font-medium mb-1">Nama *</label>
        <input type="text" name="name" value="{{ old('name') }}" required class="w-full border rounded px-3 py-2">
        @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="mb-4">
        <label class="block text-sm font-medium mb-1">Email *</label>
        <input type="email" name="email" value="{{ old('email') }}" required class="w-full border rounded px-3 py-2">
        @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="mb-6">
        <label class="block text-sm font-medium mb-1">Password *</label>
        <input type="text" name="password" value="{{ Str::random(8) }}" required class="w-full border rounded px-3 py-2 font-mono">
        <p class="text-gray-400 text-xs mt-1">Catat password ini. Tidak bisa dilihat lagi setelah submit.</p>
        @error('password') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="flex gap-3">
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Simpan</button>
        <a href="{{ route('voting.admin.members') }}" class="px-6 py-2 border rounded hover:bg-gray-50">Batal</a>
    </div>
</form>
@endsection
```

---

## Step 6: Update Routes

**Update `routes/voting.php` — tambahkan semua admin routes:**

```php
<?php

use App\Http\Controllers\Voting\AdminEventController;
use App\Http\Controllers\Voting\AdminSubmissionController;
use App\Http\Controllers\Voting\AdminMemberController;
use Illuminate\Support\Facades\Route;

// Public
Route::get('/', function () {
    return view('voting.landing');
})->name('voting.landing');

// Admin
Route::prefix('admin')->middleware(['auth', 'voting.admin'])->group(function () {
    Route::resource('events', AdminEventController::class)->names([
        'index'   => 'voting.admin.events.index',
        'create'  => 'voting.admin.events.create',
        'store'   => 'voting.admin.events.store',
        'show'    => 'voting.admin.events.show',
        'edit'    => 'voting.admin.events.edit',
        'update'  => 'voting.admin.events.update',
        'destroy' => 'voting.admin.events.destroy',
    ]);
    Route::post('events/{event}/change-status', [AdminEventController::class, 'changeStatus'])
        ->name('voting.admin.change-status');

    Route::get('events/{event}/submissions', [AdminSubmissionController::class, 'index'])
        ->name('voting.admin.submissions');
    Route::get('submissions/{submission}', [AdminSubmissionController::class, 'show'])
        ->name('voting.admin.submission.show');
    Route::put('submissions/{submission}/review', [AdminSubmissionController::class, 'review'])
        ->name('voting.admin.submission.review');

    Route::get('members', [AdminMemberController::class, 'index'])->name('voting.admin.members');
    Route::get('members/create', [AdminMemberController::class, 'create'])->name('voting.admin.members.create');
    Route::post('members', [AdminMemberController::class, 'store'])->name('voting.admin.members.store');
    Route::put('members/{user}/toggle-active', [AdminMemberController::class, 'toggleActive'])
        ->name('voting.admin.members.toggle');
});

// Placeholder routes yang akan diisi di fase berikutnya
// Route::get('/login', ...)->name('voting.login');
// Route::post('/logout', ...)->name('voting.logout');
```

**Catatan:** Route `voting.login` dan `voting.logout` belum ada. Untuk sementara, agar admin bisa akses, login dulu via company profile auth (kalau session-based) atau buat route login sementara. Ini akan dibuat proper di FASE 4.

**Workaround sementara untuk login admin:**
```php
// Tambahkan di routes/voting.php (temporary, hapus di Fase 4)
Route::get('/login', function () {
    return view('voting.auth.login');
})->name('voting.login');

Route::post('/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email'    => 'required|email',
        'password' => 'required',
    ]);

    if (auth()->attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->route('voting.admin.events.index');
    }

    return back()->with('error', 'Email atau password salah.');
})->name('voting.login.post');

Route::post('/logout', function () {
    auth()->logout();
    return redirect()->route('voting.login');
})->name('voting.logout')->middleware('auth');
```

**Buat `resources/views/voting/auth/login.blade.php`:**
```html
@extends('voting.layouts.app')
@section('title', 'Login')

@section('content')
<div class="max-w-sm mx-auto mt-12">
    <h1 class="text-2xl font-bold mb-6 text-center">Login</h1>

    <form method="POST" action="{{ route('voting.login.post') }}" class="bg-white rounded-lg shadow-sm p-6">
        @csrf

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full border rounded px-3 py-2">
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium mb-1">Password</label>
            <input type="password" name="password" required
                   class="w-full border rounded px-3 py-2">
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Login</button>
    </form>
</div>
@endsection
```

---

## ✅ CHECKLIST SEBELUM LANJUT KE FASE 2

### Admin Event
- [ ] Admin bisa login via `/vote/login`
- [ ] Admin bisa lihat daftar event di `/vote/admin/events`
- [ ] Admin bisa buat event baru (judul, deskripsi, deadline)
- [ ] Admin bisa edit event yang sudah ada
- [ ] Admin bisa lihat detail event + statistik submission
- [ ] Admin bisa ubah status: draft → submission_open → voting_open → closed
- [ ] Status transition yang invalid ditolak (misal: draft langsung ke closed)
- [ ] Link submit karya muncul saat status = submission_open

### Admin Submission
- [ ] Admin bisa lihat list submission per event
- [ ] Admin bisa filter submission by status (pending/approved/rejected)
- [ ] Admin bisa lihat detail submission (thumbnail, screenshot, deskripsi, demo URL)
- [ ] Admin bisa approve submission
- [ ] Admin bisa reject submission (dengan catatan opsional)
- [ ] Status submission berubah setelah review

### Admin Member
- [ ] Admin bisa lihat daftar member
- [ ] Admin bisa tambah member baru (nama, email, password)
- [ ] Admin bisa toggle aktif/nonaktif member
- [ ] Password di-hash (bukan plain text)

### Navigasi & UI
- [ ] Sidebar admin berfungsi (Events, Members)
- [ ] Flash messages (success/error) tampil dengan benar
- [ ] Mobile: menu admin accessible

### Sanity Check
- [ ] Non-admin yang coba akses `/vote/admin/*` di-redirect ke login
- [ ] Company profile masih berfungsi normal
- [ ] Tidak ada error di `storage/logs/laravel.log`

**Semua centang? → Lanjut ke FASE 2: Submit Karya.**
