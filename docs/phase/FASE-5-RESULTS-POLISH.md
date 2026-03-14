# FASE 5: Hasil Voting & Polish

**Produk:** Inready VOTES  
**Estimasi:** 1-2 hari kerja efektif  
**Prasyarat:** FASE 4 checklist 100% centang  
**Output:** Halaman hasil voting, UI polish, responsive check, error handling

---

## Tujuan Fase Ini

Setelah admin menutup voting, hasil harus tampil rapi — ranking per konsentrasi, pemenang di-highlight, total vote per karya. Sekaligus fase ini membersihkan semua rough edge di UI sebelum testing.

---

## Step 1: Result Controller

```bash
php artisan make:controller Voting/ResultController
```

```php
<?php

namespace App\Http\Controllers\Voting;

use App\Http\Controllers\Controller;
use App\Models\VotingEvent;
use App\Models\Submission;
use App\Models\Vote;
use Illuminate\Support\Facades\DB;

class ResultController extends Controller
{
    public function index(string $slug)
    {
        $event = VotingEvent::where('slug', $slug)->firstOrFail();

        // Hasil hanya tampil setelah voting ditutup
        abort_unless(
            in_array($event->status, ['closed', 'archived']),
            403,
            'Hasil belum tersedia. Voting masih berlangsung.'
        );

        // Query: ranking per konsentrasi
        $results = Submission::where('voting_event_id', $event->id)
            ->where('status', 'approved')
            ->withCount('votes')
            ->get()
            ->groupBy('concentration')
            ->map(function ($group) {
                return $group->sortByDesc('votes_count')->values();
            });

        // Total statistik
        $totalVoters = Vote::where('voting_event_id', $event->id)
            ->distinct('voter_id')
            ->count('voter_id');

        $totalVotes = Vote::where('voting_event_id', $event->id)->count();

        return view('voting.results.index', compact('event', 'results', 'totalVoters', 'totalVotes'));
    }
}
```

---

## Step 2: Results View

`resources/views/voting/results/index.blade.php`:
```html
@extends('voting.layouts.app')
@section('title', 'Hasil — ' . $event->title)

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold">Hasil Voting</h1>
    <p class="text-gray-500">{{ $event->title }}</p>

    @if($event->voting_closed_at)
        <p class="text-xs text-gray-400 mt-1">
            Ditutup: {{ $event->voting_closed_at->format('d M Y, H:i') }} WITA
        </p>
    @endif
</div>

{{-- Statistik --}}
<div class="grid grid-cols-2 gap-4 mb-8">
    <div class="bg-white rounded-lg shadow-sm p-4 text-center">
        <p class="text-3xl font-bold text-blue-600">{{ $totalVoters }}</p>
        <p class="text-sm text-gray-500">total voter</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-4 text-center">
        <p class="text-3xl font-bold text-blue-600">{{ $totalVotes }}</p>
        <p class="text-sm text-gray-500">total vote</p>
    </div>
</div>

{{-- Ranking per konsentrasi --}}
@foreach(['website' => 'Website', 'design' => 'Desain', 'mobile' => 'Mobile'] as $key => $label)
    @if(isset($results[$key]) && $results[$key]->count() > 0)
    <div class="mb-8">
        <h2 class="text-xl font-bold mb-4">{{ $label }}</h2>

        @foreach($results[$key] as $index => $sub)
        <div class="bg-white rounded-lg shadow-sm p-4 mb-3 flex gap-4 items-center
            {{ $index === 0 ? 'ring-2 ring-yellow-400 bg-yellow-50' : '' }}">

            {{-- Rank --}}
            <div class="w-10 h-10 flex items-center justify-center rounded-full font-bold text-lg
                {{ $index === 0 ? 'bg-yellow-400 text-white' : 'bg-gray-100 text-gray-500' }}">
                {{ $index + 1 }}
            </div>

            {{-- Thumbnail --}}
            <img src="{{ Storage::url($sub->thumbnail_path) }}"
                 class="w-16 h-16 object-cover rounded" alt="">

            {{-- Info --}}
            <div class="flex-1">
                <a href="{{ route('voting.detail', [$event->slug, $sub->id]) }}"
                   class="font-semibold hover:text-blue-600">
                    {{ $sub->title }}
                    @if($index === 0) <span class="text-yellow-500">🏆</span> @endif
                </a>
                <p class="text-sm text-gray-500">{{ $sub->candidate_name }}</p>
            </div>

            {{-- Vote count --}}
            <div class="text-right">
                <p class="text-2xl font-bold {{ $index === 0 ? 'text-yellow-600' : 'text-gray-700' }}">
                    {{ $sub->votes_count }}
                </p>
                <p class="text-xs text-gray-400">vote</p>
            </div>
        </div>
        @endforeach
    </div>
    @endif
@endforeach

<p class="text-center mt-8">
    <a href="{{ route('voting.gallery', $event->slug) }}" class="text-sm text-blue-600 hover:underline">← Kembali ke Gallery</a>
</p>
@endsection
```

---

## Step 3: Update Routes

**Tambahkan di `routes/voting.php`:**
```php
use App\Http\Controllers\Voting\ResultController;

Route::get('/event/{slug}/hasil', [ResultController::class, 'index'])->name('voting.results');
```

---

## Step 4: Link Hasil dari Gallery

**Di `resources/views/voting/gallery/index.blade.php`, tambahkan link ke hasil jika event closed.**

Tambahkan setelah deskripsi event, sebelum filter konsentrasi:
```html
@if($event->isClosed())
    <a href="{{ route('voting.results', $event->slug) }}"
       class="inline-block mt-3 text-sm bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 transition">
        Lihat Hasil Voting →
    </a>
@endif
```

---

## Step 5: Error & Empty States

Buat partial `resources/views/voting/partials/error-page.blade.php`:
```html
@extends('voting.layouts.app')
@section('title', 'Error')

@section('content')
<div class="text-center py-16">
    <p class="text-6xl font-bold text-gray-200 mb-4">{{ $code ?? '404' }}</p>
    <h1 class="text-xl font-bold mb-2">{{ $title ?? 'Halaman Tidak Ditemukan' }}</h1>
    <p class="text-gray-500 mb-6">{{ $message ?? 'Halaman yang kamu cari tidak ada atau sudah dihapus.' }}</p>
    <a href="{{ route('voting.landing') }}" class="text-blue-600 hover:underline">← Kembali ke Inready VOTES</a>
</div>
@endsection
```

**Override error views untuk route voting.** Buat `app/Exceptions/Handler.php` custom atau gunakan Laravel exception rendering:

```php
// Di app/Exceptions/Handler.php (atau bootstrap/app.php di Laravel 11)
// Jika request URL dimulai dengan /vote/, render Blade error page

->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
        if (str_starts_with($request->getPathInfo(), '/vote')) {
            $code = $e->getStatusCode();
            $messages = [
                403 => ['Akses Ditolak', $e->getMessage() ?: 'Kamu tidak punya akses ke halaman ini.'],
                404 => ['Tidak Ditemukan', 'Halaman yang kamu cari tidak ada.'],
                500 => ['Server Error', 'Terjadi kesalahan. Coba lagi nanti.'],
            ];
            [$title, $message] = $messages[$code] ?? ['Error', $e->getMessage()];

            return response()->view('voting.partials.error-page', compact('code', 'title', 'message'), $code);
        }
    });
})
```

---

## Step 6: Responsive Check & Minor Polish

### Checklist responsive (test di browser dev tools):

**Mobile (375px — iPhone SE):**
- [ ] Navbar: nama user tidak overflow
- [ ] Gallery: 1 kolom, card terlihat jelas
- [ ] Detail: gambar full width, teks readable
- [ ] Vote modal: center, tidak terpotong
- [ ] Admin sidebar: hidden, mobile menu visible
- [ ] Form submit: semua field full width

**Tablet (768px — iPad):**
- [ ] Gallery: 2 kolom
- [ ] Admin: sidebar visible

**Desktop (1280px+):**
- [ ] Gallery: 3 kolom
- [ ] Max-width content container berfungsi (tidak stretch full screen)

### CSS fixes yang umum diperlukan:
```html
{{-- Tambahkan di layout app.blade.php jika belum --}}
@stack('styles')

{{-- Tambahkan style global --}}
<style>
    [x-cloak] { display: none !important; }
    img { max-width: 100%; height: auto; }
</style>
```

### Polish items:
- [ ] Tambahkan `loading="lazy"` di semua gambar gallery
- [ ] Tambahkan `alt` text di semua `<img>`
- [ ] Flash messages auto-dismiss (opsional — Alpine.js):
```html
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition>
    {{-- flash content --}}
</div>
```

---

## ✅ CHECKLIST SEBELUM LANJUT KE FASE 6

### Halaman Hasil
- [ ] `/vote/event/{slug}/hasil` tampil HANYA saat event closed/archived
- [ ] Ranking per konsentrasi diurutkan dari vote terbanyak
- [ ] Pemenang (#1) di-highlight dengan warna + badge 🏆
- [ ] Total voter dan total vote tampil
- [ ] Link ke detail karya dari ranking berfungsi
- [ ] Waktu penutupan voting tampil
- [ ] Event masih voting_open → error 403

### Link Navigasi
- [ ] Gallery event closed → ada tombol "Lihat Hasil Voting"
- [ ] Dari hasil → link kembali ke gallery
- [ ] Navbar: semua link berfungsi

### Error Handling
- [ ] URL tidak valid → 404 page yang rapi (bukan Laravel default)
- [ ] Akses voting admin tanpa login → redirect ke login
- [ ] Akses halaman hasil saat voting buka → pesan yang jelas

### Responsive
- [ ] Mobile (375px): gallery 1 kolom, form full width, modal center
- [ ] Tablet (768px): gallery 2 kolom
- [ ] Desktop (1280px): gallery 3 kolom, max-width container
- [ ] Admin mobile: menu accessible

### Polish
- [ ] Tidak ada console error di browser
- [ ] Semua gambar punya alt text
- [ ] Lazy loading aktif di gallery
- [ ] Flash messages tampil dan bisa dismiss

**Semua centang? → Lanjut ke FASE 6: Deploy & Testing.**
