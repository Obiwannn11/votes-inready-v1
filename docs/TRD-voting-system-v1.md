# TRD: Sistem Voting Inready Workgroup

**Dokumen:** Technical Requirements Document  
**Versi:** 1.0  
**Tanggal:** 13 Maret 2026  
**Status:** AKTIF — Eksekusi dimulai hari ini  
**Audience:** Developer yang akan coding

---

## 1. Stack & Environment

### 1.1 Tech Stack Voting System

| Layer | Teknologi | Catatan |
|---|---|---|
| Backend | Laravel 11 (existing project) | Codebase yang sama dengan company profile |
| Frontend | Blade + Alpine.js | BUKAN React+Inertia. Voting punya frontend sendiri. |
| CSS | Tailwind CSS | Sudah ada di project |
| Database | MySQL 8 | Di server terpisah (2c/4GB) |
| File upload | Laravel Storage (local disk) | Simpan di `storage/app/public/voting/` |
| Web Server | Nginx (existing) | Tambah config untuk route voting |

### 1.2 Infrastruktur Aktual

```
┌─────────────────────────────┐      ┌─────────────────────────────┐
│  VPS DONASI (1c / 1GB RAM)  │      │  SERVER DB (2c / 4GB RAM)   │
│                              │      │                              │
│  Nginx                       │      │  MySQL 8                     │
│  PHP-FPM                     │      │  Database: inready_main      │
│  Node.js (untuk React build) │      │                              │
│                              │      │                              │
│  Laravel App:                │      │                              │
│  ├── Company Profile         │      │                              │
│  │   (React + Inertia)      ├──────┤  Remote connection           │
│  │   Route: /*              │      │  Port 3306                   │
│  │                          │      │  IP whitelisted              │
│  └── Voting System          │      │                              │
│      (Blade + Alpine.js)    │      │                              │
│      Route: /vote/*         │      │                              │
└─────────────────────────────┘      └─────────────────────────────┘
```

### 1.3 Coexistence: Blade + Inertia dalam 1 Laravel

**Ini yang paling penting secara teknis.** Dua frontend approach dalam satu app.

**Prinsip:** Semua route voting menggunakan prefix `/vote` dan TIDAK melewati Inertia middleware. Sisanya tetap seperti sekarang.

```php
// routes/web.php — yang sudah ada untuk company profile (Inertia)
// JANGAN UBAH route yang sudah ada

// routes/voting.php — file BARU, khusus voting
// Semua route voting didefinisikan di sini
```

**Di `app/Providers/RouteServiceProvider.php` atau `bootstrap/app.php`:**
```php
// Daftarkan route file baru
Route::prefix('vote')
    ->middleware('web') // HANYA middleware 'web', BUKAN 'inertia'
    ->group(base_path('routes/voting.php'));
```

**Kunci:** Middleware Inertia (`HandleInertiaRequests`) TIDAK boleh jalan di route `/vote/*`. Pastikan middleware ini hanya di-apply pada route company profile, bukan global.

Cek di `app/Http/Kernel.php` atau `bootstrap/app.php`:
- Jika `HandleInertiaRequests` ada di `$middleware` (global) → PINDAHKAN ke middleware group atau route-specific
- Jika sudah di `$middlewareGroups['web']` → buat group baru atau apply per-route

**Solusi paling aman:**
```php
// Buat middleware group baru
'inertia' => [
    \App\Http\Middleware\HandleInertiaRequests::class,
    // middleware lain yang khusus Inertia
],

// Company profile routes
Route::middleware(['web', 'inertia'])->group(function () {
    // route company profile yang sudah ada
});

// Voting routes — tanpa Inertia
Route::prefix('vote')
    ->middleware(['web']) // web saja, tanpa inertia
    ->group(base_path('routes/voting.php'));
```

---

## 2. Folder Structure

```
Dalam Laravel project yang sudah ada, TAMBAHKAN:

app/
├── Http/
│   ├── Controllers/
│   │   └── Voting/                    ← BARU: semua controller voting
│   │       ├── AdminEventController.php
│   │       ├── AdminSubmissionController.php
│   │       ├── AdminMemberController.php
│   │       ├── SubmitKaryaController.php
│   │       ├── GalleryController.php
│   │       ├── VoteController.php
│   │       └── ResultController.php
│   └── Middleware/
│       └── EnsureVotingAdmin.php      ← BARU: middleware admin voting
├── Models/
│   ├── VotingEvent.php                ← BARU
│   ├── Submission.php                 ← BARU
│   ├── SubmissionScreenshot.php       ← BARU
│   └── Vote.php                       ← BARU
│   └── User.php                       ← EXISTING (tambah relasi)

resources/views/
└── voting/                            ← BARU: semua Blade views
    ├── layouts/
    │   ├── app.blade.php              ← layout publik (gallery, detail, hasil)
    │   └── admin.blade.php            ← layout admin voting
    ├── admin/
    │   ├── events/
    │   │   ├── index.blade.php
    │   │   ├── create.blade.php
    │   │   ├── edit.blade.php
    │   │   └── show.blade.php
    │   ├── submissions/
    │   │   ├── index.blade.php
    │   │   └── show.blade.php
    │   └── members/
    │       ├── index.blade.php
    │       └── create.blade.php
    ├── submit/
    │   ├── form.blade.php
    │   └── status.blade.php
    ├── gallery/
    │   ├── index.blade.php
    │   └── show.blade.php
    ├── vote/
    │   └── my-votes.blade.php
    ├── results/
    │   └── index.blade.php
    └── auth/
        └── login.blade.php            ← login khusus voter (atau reuse existing)

routes/
└── voting.php                         ← BARU

database/migrations/
├── xxxx_create_voting_events_table.php
├── xxxx_create_submissions_table.php
├── xxxx_create_submission_screenshots_table.php
└── xxxx_create_votes_table.php

database/seeders/
└── VotingSeeder.php                   ← dummy data untuk development

storage/app/public/voting/
├── thumbnails/                        ← thumbnail karya
└── screenshots/                       ← screenshot tambahan
```

---

## 3. Routes

```php
// routes/voting.php

use App\Http\Controllers\Voting\*;

// ============================================
// PUBLIC — tanpa auth
// ============================================
Route::get('/', [GalleryController::class, 'landing'])->name('voting.landing');
Route::get('/event/{slug}', [GalleryController::class, 'index'])->name('voting.gallery');
Route::get('/event/{slug}/karya/{id}', [GalleryController::class, 'show'])->name('voting.detail');
Route::get('/event/{slug}/hasil', [ResultController::class, 'index'])->name('voting.results');

// Submit karya (peserta, tanpa login)
Route::get('/submit/{slug}', [SubmitKaryaController::class, 'form'])->name('voting.submit.form');
Route::post('/submit/{slug}', [SubmitKaryaController::class, 'store'])->name('voting.submit.store');
Route::get('/submit/{slug}/status', [SubmitKaryaController::class, 'status'])->name('voting.submit.status');

// ============================================
// AUTH — member login untuk vote
// ============================================
Route::get('/login', [VoteController::class, 'loginForm'])->name('voting.login');
Route::post('/login', [VoteController::class, 'login'])->name('voting.login.post');
Route::post('/logout', [VoteController::class, 'logout'])->name('voting.logout');

Route::middleware('auth')->group(function () {
    Route::post('/event/{slug}/vote/{submission}', [VoteController::class, 'store'])
        ->name('voting.vote')
        ->middleware('throttle:30,1'); // 30 per menit per user
    Route::get('/event/{slug}/my-votes', [VoteController::class, 'myVotes'])->name('voting.my-votes');
});

// ============================================
// ADMIN — admin voting
// ============================================
Route::prefix('admin')->middleware(['auth', 'voting.admin'])->group(function () {
    // Events
    Route::resource('events', AdminEventController::class);
    Route::post('events/{event}/change-status', [AdminEventController::class, 'changeStatus'])
        ->name('voting.admin.change-status');

    // Submissions
    Route::get('events/{event}/submissions', [AdminSubmissionController::class, 'index'])
        ->name('voting.admin.submissions');
    Route::get('submissions/{submission}', [AdminSubmissionController::class, 'show'])
        ->name('voting.admin.submission.show');
    Route::put('submissions/{submission}/review', [AdminSubmissionController::class, 'review'])
        ->name('voting.admin.submission.review');

    // Members
    Route::get('members', [AdminMemberController::class, 'index'])->name('voting.admin.members');
    Route::get('members/create', [AdminMemberController::class, 'create'])->name('voting.admin.members.create');
    Route::post('members', [AdminMemberController::class, 'store'])->name('voting.admin.members.store');
    Route::put('members/{user}/toggle-active', [AdminMemberController::class, 'toggleActive'])
        ->name('voting.admin.members.toggle');
});
```

**Catatan penting soal auth:**
- Jika company profile sudah punya auth system (Login Inertia/React), BUAT login terpisah untuk voting di `/vote/login` yang render Blade, bukan Inertia.
- Atau, jika auth company profile bisa di-share (session-based, bukan token), cukup cek `auth()->check()` di voting routes — tidak perlu login form terpisah.
- Test ini: login di company profile, lalu buka `/vote/event/xxx` — apakah session masih valid? Kalau ya, tidak perlu login form baru.

---

## 4. Model & Relasi

### VotingEvent.php
```php
class VotingEvent extends Model
{
    protected $fillable = [
        'title', 'slug', 'description', 'status',
        'submission_deadline', 'voting_opened_at', 'voting_closed_at'
    ];

    protected $casts = [
        'submission_deadline' => 'datetime',
        'voting_opened_at' => 'datetime',
        'voting_closed_at' => 'datetime',
    ];

    public function submissions() { return $this->hasMany(Submission::class); }
    public function votes() { return $this->hasMany(Vote::class); }
    public function approvedSubmissions() {
        return $this->hasMany(Submission::class)->where('status', 'approved');
    }

    // Status checks
    public function isSubmissionOpen(): bool { return $this->status === 'submission_open'; }
    public function isVotingOpen(): bool { return $this->status === 'voting_open'; }
    public function isClosed(): bool { return $this->status === 'closed'; }

    // Status transition — enforce valid transitions
    public function canTransitionTo(string $newStatus): bool
    {
        $allowed = [
            'draft' => ['submission_open'],
            'submission_open' => ['voting_open', 'draft'],
            'voting_open' => ['closed'],
            'closed' => ['archived'],
            'archived' => [],
        ];
        return in_array($newStatus, $allowed[$this->status] ?? []);
    }
}
```

### Submission.php
```php
class Submission extends Model
{
    protected $fillable = [
        'voting_event_id', 'candidate_name', 'candidate_email',
        'concentration', 'title', 'description',
        'thumbnail_path', 'demo_url', 'status', 'admin_notes'
    ];

    public function event() { return $this->belongsTo(VotingEvent::class, 'voting_event_id'); }
    public function screenshots() { return $this->hasMany(SubmissionScreenshot::class)->orderBy('sort_order'); }
    public function votes() { return $this->hasMany(Vote::class); }

    public function voteCount(): int
    {
        return $this->votes()->count(); // Cukup untuk 40-100 voter. Tidak perlu cache.
    }
}
```

### Vote.php
```php
class Vote extends Model
{
    public $timestamps = false; // hanya created_at

    protected $fillable = [
        'voting_event_id', 'submission_id', 'voter_id', 'concentration'
    ];

    protected $casts = ['created_at' => 'datetime'];

    // Boot: set created_at manually karena no timestamps
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($vote) {
            $vote->created_at = now();
        });
    }

    public function event() { return $this->belongsTo(VotingEvent::class, 'voting_event_id'); }
    public function submission() { return $this->belongsTo(Submission::class); }
    public function voter() { return $this->belongsTo(User::class, 'voter_id'); }
}
```

---

## 5. Core Business Logic

### 5.1 Vote Constraint (PALING KRITIS)

Vote harus dicek di DUA level:

**Level 1 — Database (UNIQUE KEY):**
```sql
UNIQUE KEY unique_vote (voting_event_id, voter_id, concentration)
```
Ini mencegah double vote bahkan jika ada race condition di application layer.

**Level 2 — Application (sebelum insert):**
```php
// Di VoteController::store()
public function store(Request $request, string $slug, Submission $submission)
{
    $event = VotingEvent::where('slug', $slug)->firstOrFail();

    // Guard 1: voting harus buka
    abort_unless($event->isVotingOpen(), 403, 'Voting belum dibuka atau sudah ditutup.');

    // Guard 2: submission harus approved dan milik event ini
    abort_unless(
        $submission->voting_event_id === $event->id && $submission->status === 'approved',
        404
    );

    $user = auth()->user();

    // Guard 3: cek sudah vote di konsentrasi ini belum
    $alreadyVoted = Vote::where('voting_event_id', $event->id)
        ->where('voter_id', $user->id)
        ->where('concentration', $submission->concentration)
        ->exists();

    if ($alreadyVoted) {
        return back()->with('error', 'Kamu sudah vote di konsentrasi ini.');
    }

    // Guard 4: cek total vote (max 3 per event)
    $totalVotes = Vote::where('voting_event_id', $event->id)
        ->where('voter_id', $user->id)
        ->count();

    if ($totalVotes >= 3) {
        return back()->with('error', 'Kamu sudah menggunakan semua 3 vote.');
    }

    // Insert — UNIQUE KEY di DB sebagai safety net terakhir
    try {
        Vote::create([
            'voting_event_id' => $event->id,
            'submission_id' => $submission->id,
            'voter_id' => $user->id,
            'concentration' => $submission->concentration,
        ]);
    } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
        return back()->with('error', 'Vote sudah tercatat.');
    }

    return back()->with('success', 'Vote berhasil!');
}
```

### 5.2 File Upload

```php
// Di SubmitKaryaController::store()
public function store(Request $request, string $slug)
{
    $event = VotingEvent::where('slug', $slug)->firstOrFail();
    abort_unless($event->isSubmissionOpen(), 403, 'Submission sudah ditutup.');

    $validated = $request->validate([
        'candidate_name'  => 'required|string|max:255',
        'candidate_email' => 'required|email|max:255',
        'concentration'   => 'required|in:website,design,mobile',
        'title'           => 'required|string|max:255',
        'description'     => 'required|string|max:5000',
        'thumbnail'       => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        'screenshots'     => 'nullable|array|max:5',
        'screenshots.*'   => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        'demo_url'        => 'nullable|url|max:500',
    ]);

    // Simpan thumbnail
    $thumbnailPath = $request->file('thumbnail')
        ->store('voting/thumbnails', 'public');

    // Buat submission
    $submission = Submission::create([
        'voting_event_id' => $event->id,
        'candidate_name'  => $validated['candidate_name'],
        'candidate_email' => $validated['candidate_email'],
        'concentration'   => $validated['concentration'],
        'title'           => $validated['title'],
        'description'     => $validated['description'],
        'thumbnail_path'  => $thumbnailPath,
        'demo_url'        => $validated['demo_url'] ?? null,
    ]);

    // Simpan screenshots
    if ($request->hasFile('screenshots')) {
        foreach ($request->file('screenshots') as $i => $screenshot) {
            $path = $screenshot->store('voting/screenshots', 'public');
            SubmissionScreenshot::create([
                'submission_id' => $submission->id,
                'file_path'     => $path,
                'sort_order'    => $i,
            ]);
        }
    }

    return redirect()->route('voting.submit.status', [
        'slug' => $slug,
        'email' => $validated['candidate_email']
    ])->with('success', 'Karya berhasil di-submit!');
}
```

### 5.3 Gallery dengan Filter

```php
// Di GalleryController::index()
public function index(string $slug, Request $request)
{
    $event = VotingEvent::where('slug', $slug)->firstOrFail();

    $query = $event->approvedSubmissions()->with('screenshots');

    // Filter konsentrasi
    if ($request->has('concentration') && in_array($request->concentration, ['website', 'design', 'mobile'])) {
        $query->where('concentration', $request->concentration);
    }

    $submissions = $query->latest()->get();

    // Data vote user (jika login)
    $userVotes = [];
    if (auth()->check()) {
        $userVotes = Vote::where('voting_event_id', $event->id)
            ->where('voter_id', auth()->id())
            ->pluck('submission_id', 'concentration')
            ->toArray();
    }

    return view('voting.gallery.index', compact('event', 'submissions', 'userVotes'));
}
```

---

## 6. Blade Layout

### 6.1 Layout Publik: `voting/layouts/app.blade.php`

```html
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Voting') — Inready Workgroup</title>
    <!-- Tailwind CSS — gunakan CDN untuk development, build untuk production -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navbar minimal -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-6xl mx-auto px-4 py-3 flex justify-between items-center">
            <a href="{{ route('voting.landing') }}" class="font-bold text-lg">
                Inready Voting
            </a>
            <div>
                @auth
                    <span class="text-sm text-gray-600 mr-3">{{ auth()->user()->name }}</span>
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

    <!-- Flash messages -->
    @if(session('success'))
        <div class="max-w-6xl mx-auto px-4 mt-4">
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="max-w-6xl mx-auto px-4 mt-4">
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        </div>
    @endif

    <main class="max-w-6xl mx-auto px-4 py-6">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
```

**Catatan:** Tailwind CDN hanya untuk development. Sebelum event, ganti ke build Tailwind yang sudah ada di project (kemungkinan sudah di-setup untuk company profile via Vite). Cukup include CSS output yang sama.

---

## 7. Alpine.js Interactivity

### 7.1 Vote Button dengan Konfirmasi

```html
<!-- Di voting/gallery/show.blade.php -->
@if($event->isVotingOpen() && auth()->check())
    @if(isset($userVotes[$submission->concentration]))
        <div class="bg-gray-100 text-gray-500 px-6 py-3 rounded text-center">
            Kamu sudah vote di konsentrasi {{ $submission->concentration }}
        </div>
    @else
        <div x-data="{ confirming: false }">
            <button
                @click="confirming = true"
                class="w-full bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700 transition"
            >
                Vote Karya Ini
            </button>

            <!-- Confirmation dialog -->
            <div x-show="confirming" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-6 max-w-sm mx-4">
                    <h3 class="font-bold text-lg mb-2">Konfirmasi Vote</h3>
                    <p class="text-gray-600 mb-4">
                        Vote untuk <strong>{{ $submission->title }}</strong>?
                        <br>Vote tidak bisa diubah.
                    </p>
                    <div class="flex gap-3">
                        <button @click="confirming = false" class="flex-1 px-4 py-2 border rounded hover:bg-gray-50">
                            Batal
                        </button>
                        <form method="POST" action="{{ route('voting.vote', [$event->slug, $submission->id]) }}" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                Ya, Vote
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@elseif($event->isVotingOpen() && !auth()->check())
    <a href="{{ route('voting.login') }}" class="block text-center bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700">
        Login untuk Vote
    </a>
@endif
```

### 7.2 Filter Konsentrasi di Gallery

```html
<!-- Di voting/gallery/index.blade.php -->
<div class="flex gap-2 mb-6 flex-wrap">
    <a href="{{ route('voting.gallery', $event->slug) }}"
       class="px-4 py-2 rounded {{ !request('concentration') ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300' }}">
        Semua
    </a>
    @foreach(['website' => 'Website', 'design' => 'Desain', 'mobile' => 'Mobile'] as $key => $label)
        <a href="{{ route('voting.gallery', [$event->slug, 'concentration' => $key]) }}"
           class="px-4 py-2 rounded {{ request('concentration') === $key ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

<!-- Card grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($submissions as $submission)
        <a href="{{ route('voting.detail', [$event->slug, $submission->id]) }}"
           class="bg-white rounded-lg shadow-sm hover:shadow-md transition overflow-hidden">
            <img src="{{ Storage::url($submission->thumbnail_path) }}"
                 alt="{{ $submission->title }}"
                 class="w-full h-48 object-cover"
                 loading="lazy">
            <div class="p-4">
                <span class="text-xs font-medium px-2 py-1 rounded bg-blue-100 text-blue-700">
                    {{ $submission->concentration }}
                </span>
                <h3 class="font-semibold mt-2">{{ $submission->title }}</h3>
                <p class="text-sm text-gray-500">{{ $submission->candidate_name }}</p>

                @if($event->isClosed())
                    <p class="text-sm mt-2 font-medium">{{ $submission->voteCount() }} vote</p>
                @endif
            </div>
        </a>
    @empty
        <div class="col-span-full text-center text-gray-400 py-12">
            Belum ada karya yang di-approve.
        </div>
    @endforelse
</div>
```

---

## 8. Admin Event Status Management

```php
// Di AdminEventController::changeStatus()
public function changeStatus(Request $request, VotingEvent $event)
{
    $newStatus = $request->validate(['status' => 'required|in:draft,submission_open,voting_open,closed,archived'])['status'];

    if (!$event->canTransitionTo($newStatus)) {
        return back()->with('error', "Tidak bisa ubah status dari {$event->status} ke {$newStatus}.");
    }

    // Set timestamps otomatis
    $updates = ['status' => $newStatus];
    if ($newStatus === 'voting_open') {
        $updates['voting_opened_at'] = now();
    } elseif ($newStatus === 'closed') {
        $updates['voting_closed_at'] = now();
    }

    $event->update($updates);

    return back()->with('success', "Status event diubah ke {$newStatus}.");
}
```

---

## 9. Deployment Checklist

### Sebelum event (1 minggu):
```bash
# 1. Run migration di production
php artisan migrate

# 2. Pastikan storage link ada
php artisan storage:link

# 3. Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Cek disk space untuk upload
df -h /var/www/html/storage/

# 5. Test manual:
#    - Buka /vote/ — halaman landing muncul
#    - Login admin — buat event test
#    - Submit karya test — upload gambar berhasil
#    - Login member — vote berhasil
#    - Tutup voting — hasil muncul

# 6. Cek nginx error log
tail -f /var/log/nginx/error.log
```

### Hari event:
```bash
# Monitor
watch -n 5 'free -m'                    # RAM usage
tail -f /var/log/nginx/access.log       # Traffic
tail -f storage/logs/laravel.log        # App errors

# Jika server lambat:
# - Cek apakah PHP-FPM worker maxed out: ps aux | grep php-fpm | wc -l
# - Cek MySQL connection: php artisan tinker → DB::select('SELECT 1')
```

---

## 10. Development Order (Hari Ini Mulai)

**Jangan baca ini lalu planning lagi. BUKA TERMINAL. MULAI.**

### Hari 1-2: Skeleton + Admin Event
```
1. Buat routes/voting.php, daftarkan di route provider
2. Buat layout Blade: voting/layouts/app.blade.php
3. Test: buka /vote/ → render halaman kosong "Voting Inready" → BERHASIL
4. Migration: voting_events table
5. Model: VotingEvent
6. Controller: AdminEventController (CRUD)
7. Views: admin event list, create, edit
8. Test: buat event, ubah status → BERHASIL
```

### Hari 3-4: Submit Karya + Admin Review
```
1. Migration: submissions + submission_screenshots
2. Model: Submission, SubmissionScreenshot
3. Controller: SubmitKaryaController (form + store)
4. View: form submit karya (dengan upload gambar)
5. Controller: AdminSubmissionController (list + approve/reject)
6. Views: admin submission list, detail, review buttons
7. Test: submit karya, approve di admin → BERHASIL
```

### Hari 5-7: Gallery + Detail
```
1. Controller: GalleryController (index + show)
2. View: gallery grid (responsive, filter konsentrasi)
3. View: detail karya (screenshot carousel sederhana, deskripsi, link demo)
4. Test: gallery menampilkan karya approved → BERHASIL
```

### Hari 8-10: Login + Vote
```
1. Auth: login form Blade untuk voter (atau verifikasi session sharing dari Inertia auth)
2. Controller: VoteController (store + myVotes)
3. Vote button dengan konfirmasi Alpine.js
4. Constraint check (application + DB level)
5. My votes page
6. Test: login, vote, cek tidak bisa double vote → BERHASIL
```

### Hari 11-12: Hasil + Admin Member
```
1. Controller: ResultController
2. View: ranking per konsentrasi, pemenang highlighted
3. Controller: AdminMemberController (create, toggle active)
4. View: member list, create form
5. Test: tutup voting, lihat hasil → BERHASIL
```

### Hari 13-14: Polish + Deploy + Test Internal
```
1. Responsive check (buka di HP)
2. Error handling (friendly error messages)
3. Empty states (belum ada karya, belum ada vote)
4. Deploy ke production
5. Ajak 5-10 orang test flow lengkap
6. Fix bugs yang ditemukan
```

**Total: 14 hari kerja efektif.** Jika kamu hanya punya 2-3 jam per hari, itu 4-6 minggu kalender. Sesuaikan dengan tanggal event-mu.

---

## 11. README Minimal (Tulis Sekarang, Update Nanti)

Buat file `docs/VOTING-README.md`:

```markdown
# Voting System — Inready Workgroup

## Cara Run Lokal
1. Clone repo, install dependencies (`composer install`, `npm install`)
2. Copy `.env.example` ke `.env`, isi database credentials
3. `php artisan migrate`
4. `php artisan db:seed --class=VotingSeeder` (data dummy)
5. `php artisan storage:link`
6. `php artisan serve`
7. Buka `localhost:8000/vote/`

## Cara Deploy
1. Pull latest code
2. `composer install --no-dev`
3. `php artisan migrate --force`
4. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
5. Pastikan `storage/` writable

## Akun Test
- Admin: admin@inready.id / password
- Member: member@inready.id / password

## Struktur
- Routes: `routes/voting.php`
- Controllers: `app/Http/Controllers/Voting/`
- Views: `resources/views/voting/`
- Models: VotingEvent, Submission, SubmissionScreenshot, Vote
```

---

*Ini dokumen kerja. Bukan dokumen presentasi. Sekarang buka terminal.*

*Last updated: 13 Maret 2026*
