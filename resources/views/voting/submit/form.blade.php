@extends('voting.layouts.app')
@section('title', 'Submit Karya — ' . $event->title)

@php
    $isResubmission = isset($submission) && $submission !== null;
    $thumbnailPreviewUrl = null;

    if ($isResubmission && $submission->thumbnail_path) {
        $thumbnailPreviewUrl = \Illuminate\Support\Str::startsWith($submission->thumbnail_path, 'images/')
            ? asset($submission->thumbnail_path)
            : \Illuminate\Support\Facades\Storage::url($submission->thumbnail_path);
    }
@endphp

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="mb-8">
            <h1 class="section-title mb-2">{{ $isResubmission ? 'Edit & Kirim Ulang Karya' : 'Submit Karya' }}</h1>
            <p class="section-subtitle">{{ $event->title }}</p>
        </div>

        @if ($isResubmission)
            <div class="card bg-primary-red/10 border-primary-red p-4 mb-6">
                <p class="font-body text-sm font-bold text-ink mb-1">Submission Anda sebelumnya ditolak admin.</p>
                <p class="font-body text-sm text-ink/80">
                    Silakan perbaiki karya lalu kirim ulang untuk review ulang.
                </p>
                @if ($submission->admin_notes)
                    <div class="mt-3 p-3 border-2 border-ink bg-surface text-sm text-ink">
                        <strong class="block mb-1">Alasan Rejected dari Admin:</strong>
                        {{ $submission->admin_notes }}
                    </div>
                @endif
            </div>
        @endif

        @if ($event->submission_deadline)
            <div class="card bg-primary-yellow/20 border-primary-yellow p-4 mb-6">
                <div class="font-body text-sm font-bold text-ink">
                    Deadline: {{ $event->submission_deadline->format('d M Y, H:i') }} WITA
                </div>
            </div>
        @endif

        {{-- Flash messages using Design System are handled globally in app.blade.php now --}}

        <div x-data="{
            confirmOpen: false,
            preview: @js($thumbnailPreviewUrl),
            previewModal: null,
            screenshotFiles: [],
            screenshotRemoveIndex: null,
            submitForm() {
                $refs.form.submit();
            },
            handleScreenshots(e) {
                const newFiles = Array.from(e.target.files);
                this.screenshotFiles = newFiles.map(f => ({
                    file: f,
                    url: URL.createObjectURL(f)
                }));
            },
            confirmRemoveScreenshot(index) {
                this.screenshotRemoveIndex = index;
            },
            removeScreenshot() {
                if (this.screenshotRemoveIndex === null) return;
                this.screenshotFiles.splice(this.screenshotRemoveIndex, 1);
        
                // Update file input using DataTransfer
                const dt = new DataTransfer();
                this.screenshotFiles.forEach(f => dt.items.add(f.file));
                this.$refs.screenshotInput.files = dt.files;
        
                this.screenshotRemoveIndex = null;
            }
        }">

            <form x-ref="form" method="POST" action="{{ route('voting.submit.store', $event->slug) }}"
                enctype="multipart/form-data" class="card bg-surface p-6 shadow-md" @submit.prevent="confirmOpen = true">
                @csrf

                {{-- Data Author --}}
                <div class="mb-8">
                    <h2 class="font-display font-black text-xl mb-4 pl-3 border-l-4 border-primary-blue uppercase">Informasi
                        Tim/Author</h2>
                    <div class="card bg-canvas/50 p-4 border-2 border-ink shadow-[4px_4px_0px_0px_var(--color-ink)]">
                        <p class="font-body text-sm text-ink mb-1"><strong>Nama:</strong> {{ auth()->user()->name }}</p>
                        <p class="font-body text-sm text-ink"><strong>Email:</strong> {{ auth()->user()->email }}</p>
                    </div>
                </div>

                <div class="mb-8 form-group">
                    <x-label required>Konsentrasi</x-label>
                    <div class="grid grid-cols-3 gap-3 mt-2">
                        @foreach (['website' => 'Website', 'design' => 'Desain', 'mobile' => 'Mobile'] as $val => $label)
                            <label class="cursor-pointer relative">
                                <input type="radio" name="concentration" value="{{ $val }}"
                                    {{ old('concentration', $submission->concentration ?? '') === $val ? 'checked' : '' }}
                                    required class="peer sr-only">
                                <div
                                    class="w-full text-center border-2 border-ink p-3 font-body font-bold text-sm bg-surface text-ink transition-all peer-checked:bg-primary-yellow peer-checked:shadow-[4px_4px_0px_0px_var(--color-ink)] hover:bg-muted">
                                    {{ $label }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('concentration')
                        <p class="form-helper error mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <hr class="border-t-2 border-ink my-8">

                {{-- Data Karya --}}
                <div class="mb-8">
                    <h2 class="font-display font-black text-xl mb-4 pl-3 border-l-4 border-primary-red uppercase">Data Karya
                    </h2>

                    <div class="form-group mb-6">
                        <x-label for="title" required>Judul Karya</x-label>
                        <x-input type="text" name="title" id="title"
                            value="{{ old('title', $submission->title ?? '') }}" required
                            placeholder="Masukkan judul karya" class="{{ $errors->has('title') ? 'error' : '' }}" />
                        @error('title')
                            <p class="form-helper error mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group mb-6">
                        <x-label for="description" required>Deskripsi Karya</x-label>
                        <textarea name="description" id="description" rows="5" required
                            class="w-full border-2 border-ink bg-surface p-3 font-body text-ink focus:outline-none focus:ring-0 focus:shadow-[4px_4px_0px_0px_var(--color-ink)] transition-shadow {{ $errors->has('description') ? 'border-primary-red' : '' }}"
                            placeholder="Jelaskan karya kamu: apa yang dibuat, teknologi yang dipakai, dsb...">{{ old('description', $submission->description ?? '') }}</textarea>
                        <p class="form-helper mt-1 text-ink/60">Maksimal 5000 karakter</p>
                        @error('description')
                            <p class="form-helper error mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group mb-6">
                        @if ($isResubmission)
                            <x-label for="thumbnail">Thumbnail Karya <span class="font-normal text-xs text-ink/60">(opsional
                                    saat edit, max 2MB)</span></x-label>
                        @else
                            <x-label for="thumbnail" required>Thumbnail Karya <span
                                    class="font-normal text-xs text-ink/60">(max
                                    2MB)</span></x-label>
                        @endif
                        <input type="file" name="thumbnail" id="thumbnail" accept="image/jpeg,image/png,image/webp"
                            {{ $isResubmission ? '' : 'required' }}
                            @change="preview = URL.createObjectURL($event.target.files[0])"
                            class="w-full border-2 border-ink p-1 bg-surface text-sm {{ $errors->has('thumbnail') ? 'border-primary-red' : '' }} file:bg-ink file:text-surface file:border-2 file:border-ink file:px-4 file:py-2 file:mr-4 file:font-bold file:cursor-pointer hover:file:bg-ink/80 focus:outline-none focus:shadow-[4px_4px_0px_0px_var(--color-ink)] transition-shadow">
                        @if ($isResubmission)
                            <p class="form-helper mt-1 text-ink/60">Kosongkan input jika tidak ingin mengganti thumbnail.
                            </p>
                        @endif
                        <img x-show="preview" :src="preview"
                            class="mt-4 max-h-48 border-2 border-ink shadow-[4px_4px_0px_0px_var(--color-ink)] object-cover bg-canvas"
                            x-cloak alt="Preview thumbnail karya">
                        @error('thumbnail')
                            <p class="form-helper error mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group mb-6">
                        <x-label for="screenshots">Screenshot Tambahan <span class="font-normal text-xs text-ink/60">(max 5
                                file, max 2MB/file)</span></x-label>
                        <input type="file" name="screenshots[]" id="screenshots" accept="image/jpeg,image/png,image/webp"
                            multiple x-ref="screenshotInput" @change="handleScreenshots"
                            class="w-full border-2 border-ink p-1 bg-surface text-sm {{ $errors->has('screenshots') || $errors->has('screenshots.*') ? 'border-primary-red' : '' }} file:bg-canvas file:text-ink file:border-2 file:border-ink file:px-4 file:py-1 file:mr-4 file:font-bold file:cursor-pointer hover:file:bg-muted focus:outline-none focus:shadow-[4px_4px_0px_0px_var(--color-ink)] transition-shadow">

                        @if ($isResubmission && $submission->screenshots->isNotEmpty())
                            <p class="form-helper mt-2 text-ink/70">Screenshot saat ini. Jika Anda upload screenshot baru,
                                seluruh screenshot lama akan diganti.</p>
                            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mt-3">
                                @foreach ($submission->screenshots as $existingScreenshot)
                                    @php
                                        $existingScreenshotUrl = \Illuminate\Support\Str::startsWith(
                                            $existingScreenshot->image_path,
                                            'images/',
                                        )
                                            ? asset($existingScreenshot->image_path)
                                            : \Illuminate\Support\Facades\Storage::url($existingScreenshot->image_path);
                                    @endphp
                                    <div
                                        class="border-2 border-ink shadow-[4px_4px_0px_0px_var(--color-ink)] aspect-[4/3] bg-canvas overflow-hidden">
                                        <img src="{{ $existingScreenshotUrl }}" class="object-cover w-full h-full"
                                            alt="Screenshot lama karya {{ $submission->title }}">
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mt-4" x-show="screenshotFiles.length > 0" x-cloak>
                            <template x-for="(fileObj, index) in screenshotFiles" :key="index">
                                <div
                                    class="relative border-2 border-ink shadow-[4px_4px_0px_0px_var(--color-ink)] group aspect-[4/3] bg-canvas overflow-hidden">
                                    <img :src="fileObj.url"
                                        class="object-cover w-full h-full cursor-pointer transition-transform duration-300 group-hover:scale-105"
                                        @click="previewModal = fileObj.url" alt="Screenshot Tambahan">
                                    <button type="button" @click.stop="confirmRemoveScreenshot(index)"
                                        class="absolute top-2 right-2 bg-primary-red text-surface border-2 border-ink w-8 h-8 flex items-center justify-center font-bold font-display opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-700 shadow-[2px_2px_0px_0px_var(--color-ink)]"
                                        title="Hapus gambar ini">X</button>
                                </div>
                            </template>
                        </div>

                        @error('screenshots')
                            <p class="form-helper error mt-1">{{ $message }}</p>
                        @enderror
                        @error('screenshots.*')
                            <p class="form-helper error mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="form-group">
                            <x-label for="demo_url">Link Demo/Live <span
                                    class="font-normal text-xs text-ink/60">(opsional)</span></x-label>
                            <x-input type="url" name="demo_url" id="demo_url"
                                value="{{ old('demo_url', $submission->demo_url ?? '') }}" placeholder="https://..."
                                class="{{ $errors->has('demo_url') ? 'error' : '' }}" />
                            @error('demo_url')
                                <p class="form-helper error mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="form-group">
                            <x-label for="github_url">Link Repository <span
                                    class="font-normal text-xs text-ink/60">(opsional)</span></x-label>
                            <x-input type="url" name="github_url" id="github_url"
                                value="{{ old('github_url', $submission->github_url ?? '') }}"
                                placeholder="https://github.com/..."
                                class="{{ $errors->has('github_url') ? 'error' : '' }}" />
                            @error('github_url')
                                <p class="form-helper error mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-8">
                    <x-button type="submit" variant="primary" class="w-full justify-center py-4 text-lg">
                        {{ $isResubmission ? 'Update & Kirim Ulang Karya' : 'Submit Final Karya' }}
                    </x-button>
                </div>
                {{-- Main Form Content Ended --}}
            </form>

            {{-- Fullscreen Image Preview Modal --}}
            <div x-show="previewModal !== null" style="display: none;"
                class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-ink/90 backdrop-blur-sm"
                @click="previewModal = null" @keydown.escape.window="previewModal = null">
                <div class="relative max-w-5xl w-full flex flex-col items-center">
                    <button @click="previewModal = null"
                        class="absolute -top-12 right-0 text-surface font-display font-black text-xl hover:text-primary-yellow">TUTUP
                        X</button>
                    <img :src="previewModal"
                        class="border-4 border-surface shadow-[8px_8px_0px_0px_var(--color-primary-yellow)] max-h-[80vh] object-contain bg-canvas"
                        alt="Preview Gambar Layar Penuh" @click.stop>
                </div>
            </div>

            {{-- Remove Screenshot Confirmation Modal --}}
            <div x-show="screenshotRemoveIndex !== null" style="display: none;"
                class="fixed inset-0 z-[60] flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-ink/50 backdrop-blur-sm" @click="screenshotRemoveIndex = null"></div>

                <div
                    class="bg-surface border-4 border-ink p-6 max-w-sm w-full shadow-[8px_8px_0px_0px_var(--color-ink)] z-10 relative text-center">
                    <div class="icon-container mb-4 mx-auto bg-primary-red text-surface border-2 border-ink">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="square" stroke-linejoin="miter">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </div>
                    <h3 class="font-display font-black text-xl mb-2 text-ink uppercase">Hapus Screenshot?</h3>
                    <p class="font-body text-sm text-ink/80 mb-6">Screenshot yang dipilih akan dihapus dari daftar upload
                        ini.</p>

                    <div class="flex justify-center gap-3">
                        <x-button type="button" variant="outline" @click="screenshotRemoveIndex = null">Batal</x-button>
                        <x-button type="button" variant="danger" @click="removeScreenshot()">Ya, Hapus</x-button>
                    </div>
                </div>
            </div>

            {{-- Submit Form Confirmation Modal --}}
            <div x-show="confirmOpen" style="display: none;"
                class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-ink/50 backdrop-blur-sm" @click="confirmOpen = false"></div>

                <div
                    class="bg-surface border-4 border-ink p-6 md:p-8 max-w-md w-full shadow-[8px_8px_0px_0px_var(--color-ink)] z-10 relative">
                    <div class="icon-container diamond mb-6 bg-primary-yellow text-ink border-2 border-ink">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="square" stroke-linejoin="miter">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                    <h3 class="font-display font-black text-2xl mb-3 text-ink uppercase leading-tight">
                        {{ $isResubmission ? 'Konfirmasi Kirim Ulang Karya' : 'Konfirmasi Submit Karya' }}
                    </h3>
                    <p class="font-body text-ink/80 mb-8 leading-relaxed">
                        @if ($isResubmission)
                            Apakah Anda yakin revisi karya sudah sesuai? Karya akan dikirim ulang dan masuk antrean review
                            admin.
                        @else
                            Apakah Anda yakin data dan karya yang diunggah sudah final? Data tidak dapat diubah kembali
                            setelah disubmit.
                        @endif
                    </p>

                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-3">
                        <x-button type="button" variant="outline" @click="confirmOpen = false" class="justify-center">
                            Periksa Kembali
                        </x-button>
                        <x-button type="button" variant="primary" @click="submitForm" class="justify-center">
                            {{ $isResubmission ? 'Ya, Kirim Ulang' : 'Ya, Kirim Final' }}
                        </x-button>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t-2 border-ink text-center pb-12">
            <p class="font-body text-sm font-bold text-ink">
                Ingin melihat status karya? <a href="{{ route('voting.submit.status', [$event->slug]) }}"
                    class="text-primary-blue hover:underline underline-offset-4 decoration-2">Cek di sini →</a>
            </p>
        </div>
    </div>
@endsection
