# FASE 3: Gallery & Detail Karya

**Produk:** Inready VOTES  
**Estimasi:** 1-2 hari kerja efektif  
**Prasyarat:** FASE 2 checklist 100% centang  
**Output:** Publik bisa browse gallery karya, filter per konsentrasi, lihat detail lengkap

---

## Tujuan Fase Ini

Halaman utama yang dilihat oleh voter dan pengunjung. Gallery menampilkan semua karya yang sudah approved, bisa difilter per konsentrasi, dan setiap karya punya detail page lengkap dengan screenshot dan link demo.

---

## Step 1: Gallery Controller

```bash
php artisan make:controller Voting/GalleryController
```

```php
<?php

namespace App\Http\Controllers\Voting;

use App\Http\Controllers\Controller;
use App\Models\VotingEvent;
use App\Models\Submission;
use App\Models\Vote;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function landing()
    {
        $events = VotingEvent::whereIn('status', ['voting_open', 'closed'])
            ->latest()
            ->get();

        $activeEvent = VotingEvent::where('status', 'voting_open')->first()
            ?? VotingEvent::where('status', 'closed')->latest()->first();

        // Jika ada event aktif, langsung redirect ke gallery
        if ($activeEvent && $events->count() === 1) {
            return redirect()->route('voting.gallery', $activeEvent->slug);
        }

        return view('voting.landing', compact('events'));
    }

    public function index(string $slug, Request $request)
    {
        $event = VotingEvent::where('slug', $slug)->firstOrFail();

        // Event harus minimal submission_open agar gallery bisa dilihat
        abort_unless(
            in_array($event->status, ['submission_open', 'voting_open', 'closed', 'archived']),
            404,
            'Event belum dipublikasikan.'
        );

        $query = $event->approvedSubmissions()->with('screenshots');

        // Filter konsentrasi
        $concentration = $request->query('c');
        if ($concentration && in_array($concentration, ['website', 'design', 'mobile'])) {
            $query->where('concentration', $concentration);
        }

        $submissions = $query->latest()->get();

        // Data vote user saat ini (jika login)
        $userVotes = [];
        $userVoteCount = 0;
        if (auth()->check()) {
            $userVotes = Vote::where('voting_event_id', $event->id)
                ->where('voter_id', auth()->id())
                ->pluck('submission_id', 'concentration')
                ->toArray();
            $userVoteCount = count($userVotes);
        }

        // Hitung vote per submission (hanya tampil kalau voting closed)
        $voteCounts = [];
        if ($event->isClosed()) {
            $voteCounts = Vote::where('voting_event_id', $event->id)
                ->selectRaw('submission_id, COUNT(*) as total')
                ->groupBy('submission_id')
                ->pluck('total', 'submission_id')
                ->toArray();
        }

        return view('voting.gallery.index', compact(
            'event', 'submissions', 'userVotes', 'userVoteCount',
            'voteCounts', 'concentration'
        ));
    }

    public function show(string $slug, int $id)
    {
        $event = VotingEvent::where('slug', $slug)->firstOrFail();
        $submission = Submission::where('id', $id)
            ->where('voting_event_id', $event->id)
            ->where('status', 'approved')
            ->with('screenshots')
            ->firstOrFail();

        // User vote data
        $userVotes = [];
        if (auth()->check()) {
            $userVotes = Vote::where('voting_event_id', $event->id)
                ->where('voter_id', auth()->id())
                ->pluck('submission_id', 'concentration')
                ->toArray();
        }

        // Vote count (hanya saat closed)
        $voteCount = $event->isClosed()
            ? $submission->votes()->count()
            : null;

        return view('voting.gallery.show', compact('event', 'submission', 'userVotes', 'voteCount'));
    }
}
```

---

## Step 2: Gallery View

`resources/views/voting/gallery/index.blade.php`:
```html
@extends('voting.layouts.app')
@section('title', $event->title . ' — Gallery')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold">{{ $event->title }}</h1>
    @if($event->description)
        <p class="text-gray-500 mt-1">{{ $event->description }}</p>
    @endif

    {{-- Status badge --}}
    @if($event->isVotingOpen())
        <span class="inline-block mt-2 text-xs font-medium px-3 py-1 rounded bg-green-100 text-green-700">
            Voting Dibuka
        </span>
        @auth
            <span class="text-xs text-gray-500 ml-2">Vote terpakai: {{ $userVoteCount }}/3</span>
        @endauth
    @elseif($event->isClosed())
        <span class="inline-block mt-2 text-xs font-medium px-3 py-1 rounded bg-gray-100 text-gray-600">
            Voting Ditutup — Hasil Final
        </span>
    @endif
</div>

{{-- Filter konsentrasi --}}
<div class="flex gap-2 mb-6 flex-wrap">
    <a href="{{ route('voting.gallery', $event->slug) }}"
       class="px-4 py-2 rounded text-sm transition
           {{ !$concentration ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
        Semua
    </a>
    @foreach(['website' => 'Website', 'design' => 'Desain', 'mobile' => 'Mobile'] as $key => $label)
        <a href="{{ route('voting.gallery', [$event->slug, 'c' => $key]) }}"
           class="px-4 py-2 rounded text-sm transition
               {{ $concentration === $key ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

{{-- Grid karya --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($submissions as $sub)
        <a href="{{ route('voting.detail', [$event->slug, $sub->id]) }}"
           class="bg-white rounded-lg shadow-sm hover:shadow-md transition overflow-hidden group">
            <div class="relative">
                <img src="{{ Storage::url($sub->thumbnail_path) }}"
                     alt="{{ $sub->title }}"
                     class="w-full h-48 object-cover group-hover:scale-105 transition duration-300"
                     loading="lazy">
                <span class="absolute top-2 left-2 text-xs font-medium px-2 py-1 rounded bg-white/90 text-gray-700">
                    {{ $sub->concentration }}
                </span>

                {{-- Voted indicator --}}
                @if(isset($userVotes[$sub->concentration]) && $userVotes[$sub->concentration] === $sub->id)
                    <span class="absolute top-2 right-2 text-xs font-medium px-2 py-1 rounded bg-green-500 text-white">
                        Voted ✓
                    </span>
                @endif
            </div>

            <div class="p-4">
                <h3 class="font-semibold group-hover:text-blue-600 transition">{{ $sub->title }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ $sub->candidate_name }}</p>

                @if($event->isClosed() && isset($voteCounts[$sub->id]))
                    <p class="text-sm font-medium text-blue-600 mt-2">
                        {{ $voteCounts[$sub->id] }} vote
                    </p>
                @endif
            </div>
        </a>
    @empty
        <div class="col-span-full text-center text-gray-400 py-16">
            @if($concentration)
                Belum ada karya di konsentrasi ini.
            @else
                Belum ada karya yang di-approve.
            @endif
        </div>
    @endforelse
</div>
@endsection
```

---

## Step 3: Detail Karya View

`resources/views/voting/gallery/show.blade.php`:
```html
@extends('voting.layouts.app')
@section('title', $submission->title)

@section('content')
<a href="{{ route('voting.gallery', $event->slug) }}" class="text-sm text-blue-600 hover:underline mb-4 inline-block">← Kembali ke Gallery</a>

<div class="max-w-4xl">
    {{-- Header --}}
    <div class="mb-4">
        <span class="text-xs font-medium px-2 py-1 rounded bg-blue-100 text-blue-700">
            {{ $submission->concentration }}
        </span>
        <h1 class="text-2xl font-bold mt-2">{{ $submission->title }}</h1>
        <p class="text-gray-500">oleh {{ $submission->candidate_name }}</p>
    </div>

    {{-- Thumbnail --}}
    <div class="mb-6">
        <img src="{{ Storage::url($submission->thumbnail_path) }}"
             alt="{{ $submission->title }}"
             class="w-full max-h-[500px] object-contain rounded-lg bg-gray-100">
    </div>

    {{-- Screenshots --}}
    @if($submission->screenshots->count())
    <div class="mb-6">
        <h2 class="font-semibold text-sm text-gray-500 mb-2">Screenshot</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
            @foreach($submission->screenshots as $ss)
            <a href="{{ Storage::url($ss->file_path) }}" target="_blank">
                <img src="{{ Storage::url($ss->file_path) }}"
                     class="w-full h-40 object-cover rounded hover:opacity-90 transition"
                     loading="lazy" alt="">
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Description --}}
    <div class="mb-6">
        <h2 class="font-semibold text-sm text-gray-500 mb-2">Deskripsi</h2>
        <div class="bg-white rounded-lg p-4 shadow-sm">
            <p class="whitespace-pre-line">{{ $submission->description }}</p>
        </div>
    </div>

    {{-- Demo link --}}
    @if($submission->demo_url)
    <div class="mb-6">
        <h2 class="font-semibold text-sm text-gray-500 mb-2">Demo</h2>
        <a href="{{ $submission->demo_url }}" target="_blank" rel="noopener"
           class="inline-flex items-center gap-2 bg-white rounded-lg px-4 py-3 shadow-sm text-blue-600 hover:text-blue-800 transition">
            {{ $submission->demo_url }}
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
        </a>
    </div>
    @endif

    {{-- Vote count (hanya saat closed) --}}
    @if($event->isClosed() && $voteCount !== null)
    <div class="mb-6 bg-white rounded-lg p-4 shadow-sm text-center">
        <p class="text-3xl font-bold text-blue-600">{{ $voteCount }}</p>
        <p class="text-sm text-gray-500">total vote</p>
    </div>
    @endif

    {{-- Vote button — akan aktif di FASE 4 --}}
    <div class="bg-white rounded-lg p-4 shadow-sm">
        @if($event->isVotingOpen())
            @auth
                @if(isset($userVotes[$submission->concentration]) && $userVotes[$submission->concentration] === $submission->id)
                    <div class="bg-green-50 text-green-700 px-6 py-3 rounded text-center font-medium">
                        Kamu sudah vote karya ini ✓
                    </div>
                @elseif(isset($userVotes[$submission->concentration]))
                    <div class="bg-gray-100 text-gray-500 px-6 py-3 rounded text-center">
                        Kamu sudah vote di konsentrasi {{ $submission->concentration }}
                    </div>
                @else
                    {{-- Vote button — akan di-wire di FASE 4 --}}
                    <p class="text-center text-gray-400 py-3">[Vote button — aktif di Fase 4]</p>
                @endif
            @else
                <a href="{{ route('voting.login') }}"
                   class="block text-center bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700 transition">
                    Login untuk Vote
                </a>
            @endauth
        @elseif($event->isClosed())
            <p class="text-center text-gray-500 py-3">Voting sudah ditutup.</p>
        @else
            <p class="text-center text-gray-500 py-3">Voting belum dibuka.</p>
        @endif
    </div>
</div>
@endsection
```

---

## Step 4: Update Landing Page

Update `resources/views/voting/landing.blade.php` untuk menampilkan daftar event:
```html
@extends('voting.layouts.app')
@section('title', 'Inready VOTES — Voting On Talent Excellence & Showcase')

@section('content')
<div class="text-center py-12">
    <h1 class="text-3xl font-bold mb-2">Inready VOTES</h1>
    <p class="text-gray-500 mb-8">Voting On Talent Excellence & Showcase</p>

    @forelse($events as $event)
    <a href="{{ route('voting.gallery', $event->slug) }}"
       class="block max-w-md mx-auto bg-white rounded-lg shadow-sm p-6 mb-3 hover:shadow-md transition text-left">
        <div class="flex justify-between items-start">
            <h2 class="font-semibold text-lg">{{ $event->title }}</h2>
            <span class="text-xs font-medium px-2 py-1 rounded
                {{ $event->status === 'voting_open' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                {{ $event->isVotingOpen() ? 'VOTE SEKARANG' : 'SELESAI' }}
            </span>
        </div>
        @if($event->description)
            <p class="text-sm text-gray-500 mt-2">{{ Str::limit($event->description, 100) }}</p>
        @endif
    </a>
    @empty
    <p class="text-gray-400">Belum ada event voting aktif.</p>
    @endforelse
</div>
@endsection
```

---

## Step 5: Update Routes

**Tambahkan di `routes/voting.php`:**
```php
use App\Http\Controllers\Voting\GalleryController;

// Update landing
Route::get('/', [GalleryController::class, 'landing'])->name('voting.landing');

// Gallery & Detail
Route::get('/event/{slug}', [GalleryController::class, 'index'])->name('voting.gallery');
Route::get('/event/{slug}/karya/{id}', [GalleryController::class, 'show'])->name('voting.detail');
```

**Hapus placeholder landing route yang lama** (yang cuma return view).

---

## ✅ CHECKLIST SEBELUM LANJUT KE FASE 4

### Landing Page
- [ ] `/vote/` menampilkan daftar event yang voting_open atau closed
- [ ] Jika hanya 1 event aktif, otomatis redirect ke gallery event tersebut
- [ ] Event dengan status draft/submission_open tidak tampil di landing

### Gallery
- [ ] `/vote/event/{slug}` menampilkan semua karya approved
- [ ] Filter per konsentrasi berfungsi (Website / Desain / Mobile / Semua)
- [ ] Filter aktif ditandai dengan warna berbeda
- [ ] Setiap card menampilkan: thumbnail, judul, nama peserta, badge konsentrasi
- [ ] Gambar lazy loaded
- [ ] Grid responsive: 1 kolom (mobile), 2 kolom (tablet), 3 kolom (desktop)
- [ ] Event dengan status draft → 404

### Detail Karya
- [ ] `/vote/event/{slug}/karya/{id}` menampilkan detail lengkap
- [ ] Thumbnail tampil penuh
- [ ] Screenshot tambahan tampil dalam grid, bisa diklik untuk full view
- [ ] Deskripsi tampil dengan whitespace preserved
- [ ] Link demo bisa diklik (buka tab baru)
- [ ] Tombol "kembali ke gallery" berfungsi
- [ ] Karya yang belum approved → 404

### Vote Count (saat event closed)
- [ ] Jumlah vote muncul di gallery card saat event closed
- [ ] Jumlah vote muncul di detail page saat event closed
- [ ] Jumlah vote TIDAK muncul saat event masih voting_open

### Vote Button Placeholder
- [ ] User belum login → tampil link "Login untuk Vote"
- [ ] User login + belum vote konsentrasi ini → placeholder vote button visible
- [ ] User login + sudah vote → tampil "Kamu sudah vote"
- [ ] Voting belum dibuka → pesan "Voting belum dibuka"
- [ ] Voting sudah ditutup → pesan "Voting sudah ditutup"

**Semua centang? → Lanjut ke FASE 4: Voting Mechanism.**
