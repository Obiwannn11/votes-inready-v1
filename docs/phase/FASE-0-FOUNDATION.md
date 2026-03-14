# FASE 0: Foundation & Setup

**Produk:** Inready VOTES — Voting On Talent Excellence & Showcase  
**Estimasi:** 2-4 jam  
**Prasyarat:** Laravel project company profile sudah running  
**Output:** Route `/vote/` bisa diakses, database migration jalan, seeder terisi

---

## Tujuan Fase Ini

Menyiapkan pondasi supaya voting system bisa hidup berdampingan dengan company profile (React + Inertia) dalam satu Laravel project. Setelah fase ini selesai, kamu punya:
- Route `/vote/` yang render Blade (bukan Inertia)
- Semua tabel database voting sudah ada
- Data dummy untuk development
- Folder structure voting terpisah rapi

---

## Step 1: Audit Middleware Inertia

**Ini langkah paling kritis. Jika salah, semua Blade route akan error.**

Buka file middleware config. Tergantung versi Laravel:

**Laravel 11 (bootstrap/app.php):**
```bash
cat bootstrap/app.php
```

**Laravel 10 atau lebih lama (app/Http/Kernel.php):**
```bash
cat app/Http/Kernel.php
```

**Yang dicari:** Di mana `HandleInertiaRequests` terdaftar?

### Skenario A: HandleInertiaRequests di middleware global
```php
// MASALAH — ini akan memaksa semua route pakai Inertia
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        HandleInertiaRequests::class, // ← ini berlaku untuk SEMUA route web
    ]);
})
```

**Solusi:** Pindahkan ke route-specific. Lihat Step 2.

### Skenario B: HandleInertiaRequests sudah di route group tertentu
```php
// AMAN — hanya route tertentu yang pakai Inertia
Route::middleware(['web', HandleInertiaRequests::class])->group(function () {
    // route company profile
});
```

Jika sudah skenario B, lanjut ke Step 3.

---

## Step 2: Pisahkan Middleware Inertia (Jika Skenario A)

### Laravel 11 — Edit `bootstrap/app.php`:

**Sebelum:**
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\HandleInertiaRequests::class,
        \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
    ]);
})
```

**Sesudah:**
```php
->withMiddleware(function (Middleware $middleware) {
    // JANGAN append Inertia ke web global
    $middleware->web(append: [
        \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
    ]);

    // Buat alias untuk Inertia middleware
    $middleware->alias([
        'inertia' => \App\Http\Middleware\HandleInertiaRequests::class,
    ]);
})
```

Lalu update route company profile untuk pakai middleware `inertia` secara eksplisit:

**Edit `routes/web.php`:**
```php
// Semua route company profile yang sudah ada — tambahkan middleware inertia
Route::middleware(['inertia'])->group(function () {
    // Pindahkan SEMUA route company profile yang sudah ada ke dalam group ini
    // Route::get('/', [HomeController::class, 'index']);
    // Route::get('/tentang', ...);
    // ... dan seterusnya
});
```

### Laravel 10 — Edit `app/Http/Kernel.php`:

**Sebelum:**
```php
protected $middlewareGroups = [
    'web' => [
        // ... middleware lain
        \App\Http\Middleware\HandleInertiaRequests::class,
    ],
];
```

**Sesudah:**
```php
protected $middlewareGroups = [
    'web' => [
        // ... middleware lain
        // HandleInertiaRequests DIHAPUS dari sini
    ],
];

protected $middlewareAliases = [
    // ... alias lain
    'inertia' => \App\Http\Middleware\HandleInertiaRequests::class,
];
```

**Verifikasi:** Setelah perubahan ini, buka halaman company profile — harus masih berfungsi normal. Jika error, berarti ada route yang belum di-wrap dengan middleware `inertia`.

---

## Step 3: Buat Route File Voting

**Buat file `routes/voting.php`:**
```php
<?php

// routes/voting.php
// Semua route untuk Inready VOTES
// Menggunakan Blade, BUKAN Inertia

use Illuminate\Support\Facades\Route;

// Placeholder — akan diisi di fase berikutnya
Route::get('/', function () {
    return view('voting.landing');
})->name('voting.landing');
```

**Daftarkan di route provider.**

### Laravel 11 — Edit `bootstrap/app.php`:
```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
    then: function () {
        Route::prefix('vote')
            ->middleware('web')
            ->group(base_path('routes/voting.php'));
    },
)
```

### Laravel 10 — Edit `app/Providers/RouteServiceProvider.php`:
```php
public function boot(): void
{
    $this->routes(function () {
        // Route web yang sudah ada (company profile)
        Route::middleware('web')
            ->group(base_path('routes/web.php'));

        // Route voting (Blade, tanpa Inertia)
        Route::prefix('vote')
            ->middleware('web')
            ->group(base_path('routes/voting.php'));
    });
}
```

---

## Step 4: Buat Blade Layout & Landing Page

**Buat folder structure:**
```bash
mkdir -p resources/views/voting/layouts
mkdir -p resources/views/voting/admin/events
mkdir -p resources/views/voting/admin/submissions
mkdir -p resources/views/voting/admin/members
mkdir -p resources/views/voting/submit
mkdir -p resources/views/voting/gallery
mkdir -p resources/views/voting/vote
mkdir -p resources/views/voting/results
mkdir -p resources/views/voting/auth
```

**Buat layout utama `resources/views/voting/layouts/app.blade.php`:**
```html
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Inready VOTES')</title>
    <script src="https://cdn.tailwindcss.com"></script>
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

**Buat landing page `resources/views/voting/landing.blade.php`:**
```html
@extends('voting.layouts.app')

@section('title', 'Inready VOTES — Voting On Talent Excellence & Showcase')

@section('content')
<div class="text-center py-16">
    <h1 class="text-3xl font-bold mb-4">Inready VOTES</h1>
    <p class="text-gray-600 mb-2">Voting On Talent Excellence & Showcase</p>
    <p class="text-gray-400 text-sm">Sistem voting pameran karya Inready Workgroup</p>
</div>
@endsection
```

---

## Step 5: Jalankan Migration

**Buat migration files:**
```bash
php artisan make:migration add_voting_fields_to_users_table --table=users
php artisan make:migration create_voting_events_table --create=voting_events
php artisan make:migration create_submissions_table --create=submissions
php artisan make:migration create_submission_screenshots_table --create=submission_screenshots
php artisan make:migration create_votes_table --create=votes
```

Isi setiap file dengan code dari TRD Section 3 (3.1 sampai 3.5). Lalu:

```bash
php artisan migrate
```

**Verifikasi migration berhasil:**
```bash
php artisan migrate:status
```

Semua migration harus berstatus `Ran`.

---

## Step 6: Buat Model

```bash
php artisan make:model VotingEvent
php artisan make:model Submission
php artisan make:model SubmissionScreenshot
php artisan make:model Vote
```

Isi setiap model dengan code dari TRD Section 5. Untuk `User.php` yang sudah ada, tambahkan relasi:

```php
// Di app/Models/User.php — TAMBAHKAN method ini

public function votes()
{
    return $this->hasMany(\App\Models\Vote::class, 'voter_id');
}
```

---

## Step 7: Buat Seeder & Jalankan

```bash
php artisan make:seeder VotingSeeder
```

Isi dengan code dari TRD Section 3.6. Lalu:

```bash
php artisan db:seed --class=VotingSeeder
```

**Verifikasi data masuk:**
```bash
php artisan tinker
```
```php
App\Models\VotingEvent::count(); // harus 1
App\Models\Submission::count();  // harus 9
App\Models\User::where('role', 'member')->count(); // harus 5
App\Models\User::where('role', 'admin')->count();  // minimal 1
```

---

## Step 8: Setup Storage

```bash
php artisan storage:link
mkdir -p storage/app/public/voting/thumbnails
mkdir -p storage/app/public/voting/screenshots
```

Buat placeholder image untuk development:
```bash
# Download placeholder atau copy gambar apapun
# Yang penting ada file di path yang dipakai seeder
cp [gambar-apapun.jpg] storage/app/public/voting/thumbnails/placeholder.jpg
```

---

## Step 9: Test Akhir Fase 0

```bash
php artisan serve
```

Buka browser:
- `localhost:8000/vote/` → Harus tampil halaman landing "Inready VOTES"
- `localhost:8000/` → Company profile harus masih berfungsi normal (React + Inertia)

---

## ✅ CHECKLIST SEBELUM LANJUT KE FASE 1

**Semua harus centang. Jika ada yang gagal, fix dulu sebelum lanjut.**

### Infrastruktur
- [ ] Middleware `HandleInertiaRequests` TIDAK berlaku di route `/vote/*`
- [ ] Route `routes/voting.php` terdaftar dengan prefix `vote` dan middleware `web` saja
- [ ] Company profile masih berfungsi normal setelah perubahan middleware

### Database
- [ ] Semua migration berhasil (`php artisan migrate:status` — semua `Ran`)
- [ ] Tabel `users` punya kolom `role` dan `is_active`
- [ ] Tabel `voting_events` ada dan benar
- [ ] Tabel `submissions` ada dengan foreign key ke `voting_events`
- [ ] Tabel `submission_screenshots` ada dengan foreign key ke `submissions`
- [ ] Tabel `votes` ada dengan UNIQUE constraint `(voting_event_id, voter_id, concentration)`

### Data
- [ ] Seeder berhasil jalan — ada 1 event, 9 submissions, 5 member, 1 admin
- [ ] `php artisan tinker` → query model berhasil

### Views
- [ ] Folder `resources/views/voting/` sudah dibuat lengkap
- [ ] Layout `voting/layouts/app.blade.php` ada
- [ ] Landing page `voting/landing.blade.php` ada
- [ ] `localhost:8000/vote/` render halaman Blade (BUKAN Inertia)

### Storage
- [ ] `php artisan storage:link` sudah dijalankan
- [ ] Folder `storage/app/public/voting/thumbnails/` ada
- [ ] Folder `storage/app/public/voting/screenshots/` ada

### Sanity Check
- [ ] `localhost:8000/vote/` → tampil landing Blade ✓
- [ ] `localhost:8000/` → company profile React+Inertia masih jalan ✓
- [ ] Tidak ada error di `storage/logs/laravel.log`

**Semua centang? → Lanjut ke FASE 1: Admin Panel.**
