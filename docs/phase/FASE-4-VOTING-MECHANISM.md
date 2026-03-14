# FASE 4: Voting Mechanism

**Produk:** Inready VOTES  
**Estimasi:** 1-2 hari kerja efektif  
**Prasyarat:** FASE 3 checklist 100% centang  
**Output:** Member bisa login, vote karya (max 1 per konsentrasi, max 3 total), lihat vote sendiri

---

## Tujuan Fase Ini

Ini jantung sistem. Member login, browse gallery, vote karya favorit per konsentrasi. Constraint harus bulletproof — di level aplikasi DAN database. Tidak boleh ada double vote dalam kondisi apapun.

---

## Step 1: Vote Controller

```bash
php artisan make:controller Voting/VoteController
```

```php
<?php

namespace App\Http\Controllers\Voting;

use App\Http\Controllers\Controller;
use App\Models\VotingEvent;
use App\Models\Submission;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VoteController extends Controller
{
    // ============================
    // AUTH (bisa dihapus jika share session dari company profile)
    // ============================

    public function loginForm()
    {
        if (auth()->check()) {
            return redirect()->route('voting.landing');
        }
        return view('voting.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = auth()->user();

            // Cek: harus member atau admin, dan harus aktif
            if (!$user->is_active) {
                Auth::logout();
                return back()->with('error', 'Akun tidak aktif. Hubungi admin.');
            }

            $request->session()->regenerate();

            // Redirect ke halaman sebelumnya atau landing
            return redirect()->intended(route('voting.landing'));
        }

        return back()
            ->withInput($request->only('email'))
            ->with('error', 'Email atau password salah.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('voting.landing');
    }

    // ============================
    // VOTE
    // ============================

    public function store(Request $request, string $slug, Submission $submission)
    {
        $event = VotingEvent::where('slug', $slug)->firstOrFail();
        $user = auth()->user();

        // Guard 1: Voting harus buka
        if (!$event->isVotingOpen()) {
            return back()->with('error', 'Voting belum dibuka atau sudah ditutup.');
        }

        // Guard 2: Submission harus approved dan milik event ini
        if ($submission->voting_event_id !== $event->id || $submission->status !== 'approved') {
            abort(404);
        }

        // Guard 3: User harus aktif
        if (!$user->is_active) {
            return back()->with('error', 'Akun kamu tidak aktif.');
        }

        // Guard 4: Belum vote di konsentrasi ini
        $alreadyVotedConcentration = Vote::where('voting_event_id', $event->id)
            ->where('voter_id', $user->id)
            ->where('concentration', $submission->concentration)
            ->exists();

        if ($alreadyVotedConcentration) {
            return back()->with('error', 'Kamu sudah vote di konsentrasi ' . $submission->concentration . '.');
        }

        // Guard 5: Total vote belum 3
        $totalVotes = Vote::where('voting_event_id', $event->id)
            ->where('voter_id', $user->id)
            ->count();

        if ($totalVotes >= 3) {
            return back()->with('error', 'Kamu sudah menggunakan semua 3 vote.');
        }

        // Insert — UNIQUE constraint di DB sebagai safety net
        try {
            Vote::create([
                'voting_event_id' => $event->id,
                'submission_id'   => $submission->id,
                'voter_id'        => $user->id,
                'concentration'   => $submission->concentration,
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            return back()->with('error', 'Vote sudah tercatat.');
        }

        return back()->with('success', 'Vote berhasil untuk "' . $submission->title . '"!');
    }

    // ============================
    // MY VOTES
    // ============================

    public function myVotes(string $slug)
    {
        $event = VotingEvent::where('slug', $slug)->firstOrFail();

        $votes = Vote::where('voting_event_id', $event->id)
            ->where('voter_id', auth()->id())
            ->with('submission')
            ->get();

        return view('voting.vote.my-votes', compact('event', 'votes'));
    }
}
```

---

## Step 2: Update Detail View — Wire Vote Button

Ganti placeholder vote button di `resources/views/voting/gallery/show.blade.php`.

**Cari dan ganti bagian ini:**
```html
{{-- Vote button — akan di-wire di FASE 4 --}}
<p class="text-center text-gray-400 py-3">[Vote button — aktif di Fase 4]</p>
```

**Ganti dengan:**
```html
<div x-data="{ confirming: false }">
    <button @click="confirming = true"
            class="w-full bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700 transition font-medium">
        Vote Karya Ini
    </button>

    {{-- Confirmation modal --}}
    <div x-show="confirming" x-cloak
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
         x-transition.opacity>
        <div class="bg-white rounded-lg p-6 max-w-sm w-full" @click.away="confirming = false">
            <h3 class="font-bold text-lg mb-2">Konfirmasi Vote</h3>
            <p class="text-gray-600 mb-1">
                Vote untuk <strong>{{ $submission->title }}</strong>?
            </p>
            <p class="text-sm text-red-500 mb-4">
                Vote tidak bisa diubah setelah dikonfirmasi.
            </p>
            <div class="flex gap-3">
                <button @click="confirming = false"
                        class="flex-1 px-4 py-2 border rounded hover:bg-gray-50 transition">
                    Batal
                </button>
                <form method="POST"
                      action="{{ route('voting.vote', [$event->slug, $submission->id]) }}"
                      class="flex-1">
                    @csrf
                    <button type="submit"
                            class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                        Ya, Vote!
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
```

---

## Step 3: My Votes Page

`resources/views/voting/vote/my-votes.blade.php`:
```html
@extends('voting.layouts.app')
@section('title', 'Vote Saya')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-2">Vote Saya</h1>
    <p class="text-gray-500 mb-6">{{ $event->title }} · {{ $votes->count() }}/3 vote terpakai</p>

    @forelse($votes as $vote)
    <div class="bg-white rounded-lg shadow-sm p-4 mb-3 flex gap-4">
        <img src="{{ Storage::url($vote->submission->thumbnail_path) }}"
             class="w-16 h-16 object-cover rounded" alt="">
        <div>
            <a href="{{ route('voting.detail', [$event->slug, $vote->submission->id]) }}"
               class="font-semibold hover:text-blue-600">
                {{ $vote->submission->title }}
            </a>
            <p class="text-sm text-gray-500">
                {{ $vote->submission->candidate_name }}
                · <span class="font-medium">{{ $vote->concentration }}</span>
            </p>
            <p class="text-xs text-gray-400 mt-1">Divote {{ $vote->created_at->diffForHumans() }}</p>
        </div>
    </div>
    @empty
    <div class="text-center text-gray-400 py-12 bg-white rounded-lg shadow-sm">
        Kamu belum memberikan vote.
        <br>
        <a href="{{ route('voting.gallery', $event->slug) }}" class="text-blue-600 hover:underline mt-2 inline-block">Browse gallery →</a>
    </div>
    @endforelse

    @if($votes->count() < 3)
    <p class="text-center mt-4">
        <a href="{{ route('voting.gallery', $event->slug) }}" class="text-sm text-blue-600 hover:underline">
            Kamu masih punya {{ 3 - $votes->count() }} vote lagi →
        </a>
    </p>
    @endif
</div>
@endsection
```

---

## Step 4: Update Routes

**Update `routes/voting.php` — ganti temporary auth routes dengan versi final:**

```php
use App\Http\Controllers\Voting\VoteController;

// Auth
Route::get('/login', [VoteController::class, 'loginForm'])->name('voting.login');
Route::post('/login', [VoteController::class, 'login'])->name('voting.login.post');
Route::post('/logout', [VoteController::class, 'logout'])->name('voting.logout')->middleware('auth');

// Vote (auth required)
Route::middleware('auth')->group(function () {
    Route::post('/event/{slug}/vote/{submission}', [VoteController::class, 'store'])
        ->name('voting.vote')
        ->middleware('throttle:30,1');
    Route::get('/event/{slug}/my-votes', [VoteController::class, 'myVotes'])
        ->name('voting.my-votes');
});
```

**Hapus temporary auth routes dari FASE 1 jika masih ada.**

---

## Step 5: Update Navbar

**Di `voting/layouts/app.blade.php`, update navbar untuk tampilkan link My Votes:**

Ganti bagian `@auth` di navbar dengan:
```html
@auth
    @php
        $currentSlug = request()->route('slug');
    @endphp
    @if($currentSlug)
        <a href="{{ route('voting.my-votes', $currentSlug) }}" class="text-sm text-blue-600 hover:underline mr-3">
            Vote Saya
        </a>
    @endif
    <span class="text-sm text-gray-600 mr-3">{{ auth()->user()->name }}</span>
    <form method="POST" action="{{ route('voting.logout') }}" class="inline">
        @csrf
        <button class="text-sm text-red-600 hover:underline">Logout</button>
    </form>
@else
    <a href="{{ route('voting.login') }}" class="text-sm text-blue-600 hover:underline">Login untuk Vote</a>
@endauth
```

---

## Step 6: Test Scenario — WAJIB semua pass

### Test 1: Happy Path
1. Login sebagai member1@inready.id
2. Buka gallery → pilih karya website → vote → berhasil
3. Kembali ke gallery → badge "Voted ✓" muncul
4. Buka karya website lain → pesan "sudah vote di konsentrasi ini"
5. Pilih karya design → vote → berhasil
6. Pilih karya mobile → vote → berhasil
7. Coba vote lagi → pesan "sudah menggunakan semua 3 vote"
8. My Votes → 3 karya tampil

### Test 2: Constraint Enforcement
1. Login member, vote 1 karya website
2. Buka detail karya website lain → TIDAK ada tombol vote (sudah vote konsentrasi ini)
3. Buka Tinker → manual insert vote dengan event+voter+concentration yang sama → harus ERROR (UNIQUE constraint)

### Test 3: Status Guards
1. Admin: ubah event ke `draft` → member buka gallery → 404
2. Admin: ubah event ke `submission_open` → member buka gallery → bisa lihat tapi vote button disabled
3. Admin: ubah event ke `voting_open` → vote button aktif
4. Admin: ubah event ke `closed` → vote button disabled, vote count muncul

### Test 4: Auth Guards
1. User belum login → buka gallery → bisa lihat
2. User belum login → klik vote → redirect ke login
3. User login → kembali ke halaman yang tadi → bisa vote
4. Admin yang login → juga bisa vote (admin juga member)

---

## ✅ CHECKLIST SEBELUM LANJUT KE FASE 5

### Authentication
- [ ] Login form di `/vote/login` berfungsi
- [ ] Login gagal → error message yang jelas
- [ ] User is_active = false → tidak bisa login
- [ ] Logout berfungsi → session cleared
- [ ] Session sharing: jika sudah login di company profile, apakah otomatis login di voting? (test dan dokumentasikan hasilnya — kedua behavior OK)

### Vote Mechanism
- [ ] Vote button tampil HANYA saat: login + voting_open + belum vote konsentrasi
- [ ] Konfirmasi dialog muncul sebelum vote
- [ ] Vote berhasil → flash message sukses
- [ ] Vote berhasil → redirect kembali ke detail karya
- [ ] Badge "Voted ✓" muncul di gallery card untuk karya yang sudah di-vote
- [ ] Pesan "sudah vote konsentrasi ini" tampil untuk karya lain di konsentrasi yang sama

### Constraints (KRITIS — test semua)
- [ ] 1 vote per konsentrasi per event → dicek di aplikasi
- [ ] 1 vote per konsentrasi per event → dicek di database (UNIQUE KEY)
- [ ] Max 3 vote total per event → dicek di aplikasi
- [ ] Vote saat voting belum buka → ditolak
- [ ] Vote saat voting sudah tutup → ditolak
- [ ] Vote submission yang belum approved → 404
- [ ] Vote submission dari event lain → 404
- [ ] Rate limiting aktif di endpoint vote (30/menit per user)

### My Votes
- [ ] `/vote/event/{slug}/my-votes` menampilkan semua vote user
- [ ] Setiap vote menampilkan: thumbnail, judul, nama peserta, konsentrasi
- [ ] Counter "x/3 vote terpakai" akurat
- [ ] Link ke detail karya berfungsi
- [ ] Belum ada vote → empty state dengan link ke gallery

### UI/UX
- [ ] Navbar menampilkan "Vote Saya" link saat di halaman event
- [ ] Navbar menampilkan nama user saat login
- [ ] Flash messages (success/error) tampil setelah vote
- [ ] Modal konfirmasi bisa di-close dengan klik di luar

**Semua centang? → Lanjut ke FASE 5: Hasil Voting & Polish.**
