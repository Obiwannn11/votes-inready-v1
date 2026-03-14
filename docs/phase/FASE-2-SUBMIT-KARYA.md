# FASE 2: Submit Karya

**Produk:** Inready VOTES  
**Estimasi:** 1-2 hari kerja efektif  
**Prasyarat:** FASE 1 checklist 100% centang  
**Output:** Peserta bisa submit karya via form publik, admin bisa review di panel

---

## Tujuan Fase Ini

Peserta (calon anggota) bisa mengakses form submit karya tanpa login, mengisi data + upload gambar, dan melihat status submission mereka. Admin langsung bisa melihat dan review submission baru di panel admin.

---

## Step 1: Controller Submit Karya

```bash
php artisan make:controller Voting/SubmitKaryaController
```

```php
<?php

namespace App\Http\Controllers\Voting;

use App\Http\Controllers\Controller;
use App\Models\VotingEvent;
use App\Models\Submission;
use App\Models\SubmissionScreenshot;
use Illuminate\Http\Request;

class SubmitKaryaController extends Controller
{
    public function form(string $slug)
    {
        $event = VotingEvent::where('slug', $slug)->firstOrFail();
        abort_unless($event->isSubmissionOpen(), 403, 'Submission belum dibuka atau sudah ditutup.');

        return view('voting.submit.form', compact('event'));
    }

    public function store(Request $request, string $slug)
    {
        $event = VotingEvent::where('slug', $slug)->firstOrFail();
        abort_unless($event->isSubmissionOpen(), 403, 'Submission sudah ditutup.');

        // Cek deadline jika ada
        if ($event->submission_deadline && now()->isAfter($event->submission_deadline)) {
            return back()->withInput()
                ->with('error', 'Batas waktu submission sudah lewat.');
        }

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
        ], [
            'thumbnail.required'  => 'Thumbnail wajib diupload.',
            'thumbnail.image'     => 'Thumbnail harus berupa gambar.',
            'thumbnail.max'       => 'Thumbnail maksimal 2MB.',
            'screenshots.max'     => 'Maksimal 5 screenshot.',
            'screenshots.*.max'   => 'Setiap screenshot maksimal 2MB.',
            'description.max'     => 'Deskripsi maksimal 5000 karakter.',
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
            foreach ($request->file('screenshots') as $i => $file) {
                $path = $file->store('voting/screenshots', 'public');
                SubmissionScreenshot::create([
                    'submission_id' => $submission->id,
                    'file_path'     => $path,
                    'sort_order'    => $i,
                ]);
            }
        }

        return redirect()->route('voting.submit.status', [
            'slug'  => $slug,
            'email' => $validated['candidate_email'],
        ])->with('success', 'Karya berhasil di-submit! Admin akan mereview.');
    }

    public function status(string $slug, Request $request)
    {
        $event = VotingEvent::where('slug', $slug)->firstOrFail();
        $email = $request->query('email');

        $submissions = [];
        if ($email) {
            $submissions = Submission::where('voting_event_id', $event->id)
                ->where('candidate_email', $email)
                ->latest()
                ->get();
        }

        return view('voting.submit.status', compact('event', 'submissions', 'email'));
    }
}
```

---

## Step 2: Form Submit View

`resources/views/voting/submit/form.blade.php`:
```html
@extends('voting.layouts.app')
@section('title', 'Submit Karya — ' . $event->title)

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-2">Submit Karya</h1>
    <p class="text-gray-500 mb-6">{{ $event->title }}</p>

    @if($event->submission_deadline)
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded mb-6 text-sm">
            Deadline: <strong>{{ $event->submission_deadline->format('d M Y, H:i') }} WITA</strong>
        </div>
    @endif

    <form method="POST" action="{{ route('voting.submit.store', $event->slug) }}"
          enctype="multipart/form-data"
          class="bg-white rounded-lg shadow-sm p-6">
        @csrf

        {{-- Data Peserta --}}
        <h2 class="font-semibold text-lg mb-4">Data Peserta</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium mb-1">Nama Lengkap *</label>
                <input type="text" name="candidate_name" value="{{ old('candidate_name') }}" required
                       class="w-full border rounded px-3 py-2 @error('candidate_name') border-red-500 @enderror">
                @error('candidate_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email *</label>
                <input type="email" name="candidate_email" value="{{ old('candidate_email') }}" required
                       class="w-full border rounded px-3 py-2 @error('candidate_email') border-red-500 @enderror">
                @error('candidate_email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium mb-2">Konsentrasi *</label>
            <div class="flex gap-4">
                @foreach(['website' => 'Website', 'design' => 'Desain', 'mobile' => 'Mobile'] as $val => $label)
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="concentration" value="{{ $val }}"
                           {{ old('concentration') === $val ? 'checked' : '' }} required
                           class="text-blue-600">
                    <span class="text-sm">{{ $label }}</span>
                </label>
                @endforeach
            </div>
            @error('concentration') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <hr class="my-6">

        {{-- Data Karya --}}
        <h2 class="font-semibold text-lg mb-4">Data Karya</h2>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Judul Karya *</label>
            <input type="text" name="title" value="{{ old('title') }}" required
                   class="w-full border rounded px-3 py-2 @error('title') border-red-500 @enderror">
            @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Deskripsi Karya *</label>
            <textarea name="description" rows="5" required
                      class="w-full border rounded px-3 py-2 @error('description') border-red-500 @enderror"
                      placeholder="Jelaskan karya kamu: apa yang dibuat, teknologi yang dipakai, fitur utama...">{{ old('description') }}</textarea>
            <p class="text-gray-400 text-xs mt-1">Maksimal 5000 karakter</p>
            @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4" x-data="{ preview: null }">
            <label class="block text-sm font-medium mb-1">Thumbnail Karya * <span class="text-gray-400 font-normal">(max 2MB)</span></label>
            <input type="file" name="thumbnail" accept="image/jpeg,image/png,image/webp" required
                   @change="preview = URL.createObjectURL($event.target.files[0])"
                   class="w-full border rounded px-3 py-2 text-sm @error('thumbnail') border-red-500 @enderror">
            <img x-show="preview" :src="preview" class="mt-2 max-h-48 rounded" x-cloak alt="">
            @error('thumbnail') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Screenshot Tambahan <span class="text-gray-400 font-normal">(max 5 file, masing-masing max 2MB)</span></label>
            <input type="file" name="screenshots[]" accept="image/jpeg,image/png,image/webp" multiple
                   class="w-full border rounded px-3 py-2 text-sm">
            @error('screenshots') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            @error('screenshots.*') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium mb-1">Link Demo <span class="text-gray-400 font-normal">(opsional)</span></label>
            <input type="url" name="demo_url" value="{{ old('demo_url') }}"
                   placeholder="https://..."
                   class="w-full border rounded px-3 py-2">
            <p class="text-gray-400 text-xs mt-1">URL ke demo live, Figma prototype, video YouTube, dll.</p>
            @error('demo_url') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded hover:bg-blue-700 font-medium">
            Submit Karya
        </button>
    </form>

    <p class="text-center text-sm text-gray-400 mt-4">
        Sudah submit? <a href="{{ route('voting.submit.status', [$event->slug]) }}" class="text-blue-600 hover:underline">Cek status →</a>
    </p>
</div>
@endsection
```

---

## Step 3: Status Check View

`resources/views/voting/submit/status.blade.php`:
```html
@extends('voting.layouts.app')
@section('title', 'Status Submission')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-2">Cek Status Submission</h1>
    <p class="text-gray-500 mb-6">{{ $event->title }}</p>

    {{-- Form cek email --}}
    <form method="GET" action="{{ route('voting.submit.status', $event->slug) }}"
          class="bg-white rounded-lg shadow-sm p-4 mb-6 flex gap-2">
        <input type="email" name="email" value="{{ $email }}" required
               placeholder="Masukkan email yang dipakai saat submit"
               class="flex-1 border rounded px-3 py-2 text-sm">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">Cek</button>
    </form>

    @if($email)
        @forelse($submissions as $sub)
        <div class="bg-white rounded-lg shadow-sm p-4 mb-3">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="font-semibold">{{ $sub->title }}</h3>
                    <p class="text-sm text-gray-500">{{ $sub->concentration }} · {{ $sub->created_at->format('d M Y H:i') }}</p>
                </div>
                <span class="text-xs font-medium px-2 py-1 rounded
                    {{ $sub->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                    {{ $sub->status === 'approved' ? 'bg-green-100 text-green-700' : '' }}
                    {{ $sub->status === 'rejected' ? 'bg-red-100 text-red-700' : '' }}">
                    {{ $sub->status === 'pending' ? 'Menunggu Review' : '' }}
                    {{ $sub->status === 'approved' ? 'Approved ✓' : '' }}
                    {{ $sub->status === 'rejected' ? 'Rejected ✗' : '' }}
                </span>
            </div>
            @if($sub->admin_notes)
                <p class="text-sm text-gray-500 mt-2 p-2 bg-gray-50 rounded">
                    <strong>Catatan admin:</strong> {{ $sub->admin_notes }}
                </p>
            @endif
        </div>
        @empty
        <div class="text-center text-gray-400 py-8 bg-white rounded-lg shadow-sm">
            Tidak ditemukan submission dengan email <strong>{{ $email }}</strong>.
        </div>
        @endforelse
    @endif

    <p class="text-center mt-4">
        <a href="{{ route('voting.submit.form', $event->slug) }}" class="text-sm text-blue-600 hover:underline">← Kembali ke form submit</a>
    </p>
</div>
@endsection
```

---

## Step 4: Update Routes

**Tambahkan di `routes/voting.php` (sebelum admin group):**
```php
use App\Http\Controllers\Voting\SubmitKaryaController;

// Submit karya (peserta, tanpa login)
Route::get('/submit/{slug}', [SubmitKaryaController::class, 'form'])->name('voting.submit.form');
Route::post('/submit/{slug}', [SubmitKaryaController::class, 'store'])->name('voting.submit.store');
Route::get('/submit/{slug}/status', [SubmitKaryaController::class, 'status'])->name('voting.submit.status');
```

---

## Step 5: Test End-to-End

1. Login sebagai admin → buat event → ubah status ke `submission_open`
2. Copy link submit karya dari detail event
3. Buka link di browser incognito (simulasi peserta)
4. Isi form lengkap + upload gambar
5. Submit → redirect ke status page → status "Menunggu Review"
6. Kembali ke admin → submissions → karya baru muncul
7. Approve karya → status berubah
8. Cek status sebagai peserta → status "Approved ✓"

---

## ✅ CHECKLIST SEBELUM LANJUT KE FASE 3

### Form Submit
- [ ] Form submit accessible di `/vote/submit/{slug}` tanpa login
- [ ] Form HANYA bisa diakses kalau event status = `submission_open`
- [ ] Semua field validasi bekerja (nama, email, konsentrasi, judul, deskripsi, thumbnail)
- [ ] Upload thumbnail berhasil (max 2MB, hanya jpg/png/webp)
- [ ] Upload screenshot multiple berhasil (max 5 file)
- [ ] Link demo opsional dan tervalidasi sebagai URL
- [ ] Preview thumbnail muncul sebelum submit (Alpine.js)
- [ ] Error messages tampil jelas per field
- [ ] Setelah submit, redirect ke status page dengan pesan sukses

### Status Page
- [ ] Peserta bisa cek status via email
- [ ] Status pending/approved/rejected tampil dengan benar
- [ ] Catatan admin tampil jika ada
- [ ] Email yang tidak ada → pesan "tidak ditemukan"

### File Storage
- [ ] Thumbnail tersimpan di `storage/app/public/voting/thumbnails/`
- [ ] Screenshot tersimpan di `storage/app/public/voting/screenshots/`
- [ ] Gambar bisa diakses via URL (`/storage/voting/thumbnails/xxx.jpg`)

### Admin Integration
- [ ] Submission baru muncul di admin panel (status: pending)
- [ ] Admin bisa approve/reject dari list maupun detail view
- [ ] Statistik di event detail terupdate setelah ada submission baru

### Edge Cases
- [ ] Submit saat event bukan `submission_open` → error 403
- [ ] Upload file > 2MB → error message yang jelas
- [ ] Upload file bukan gambar → ditolak
- [ ] Upload > 5 screenshot → ditolak

**Semua centang? → Lanjut ke FASE 3: Gallery & Detail.**
