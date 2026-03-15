@extends('voting.layouts.app')
@section('title', 'Submit Karya — ' . $event->title)

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="mb-8">
            <h1 class="section-title mb-2">Submit Karya</h1>
            <p class="section-subtitle">{{ $event->title }}</p>
        </div>

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
                preview: null,
                submitForm() { 
                    $refs.form.submit(); 
                } 
            }">
            
            <form x-ref="form" method="POST" action="{{ route('voting.submit.store', $event->slug) }}" enctype="multipart/form-data"
                class="card bg-surface p-6 shadow-md" @submit.prevent="confirmOpen = true">
                @csrf

                {{-- Data Author --}}
                <div class="mb-8">
                    <h2 class="font-display font-black text-xl mb-4 pl-3 border-l-4 border-primary-blue uppercase">Informasi Tim/Author</h2>
                    <div class="card bg-canvas/50 p-4 border-ink shadow-sm">
                        <p class="font-body text-sm text-ink mb-1"><strong>Nama:</strong> {{ auth()->user()->name }}</p>
                        <p class="font-body text-sm text-ink"><strong>Email:</strong> {{ auth()->user()->email }}</p>
                    </div>
                </div>

                <div class="mb-8 form-group">
                    <x-label required>Konsentrasi</x-label>
                    <div class="flex flex-wrap gap-4 mt-2">
                        @foreach (['website' => 'Website', 'design' => 'Desain', 'mobile' => 'Mobile'] as $val => $label)
                            <label class="custom-radio flex items-center gap-2 cursor-pointer font-body text-sm font-medium {{ old('concentration') === $val ? 'checked' : '' }}">
                                <input type="radio" name="concentration" value="{{ $val }}"
                                    {{ old('concentration') === $val ? 'checked' : '' }} required class="sr-only"
                                    onchange="document.querySelectorAll('input[name=concentration]').forEach(el => el.parentElement.classList.remove('checked')); this.parentElement.classList.add('checked');">
                                {{ $label }}
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
                    <h2 class="font-display font-black text-xl mb-4 pl-3 border-l-4 border-primary-red uppercase">Data Karya</h2>

                    <div class="form-group mb-6">
                        <x-label for="title" required>Judul Karya</x-label>
                        <x-input type="text" name="title" id="title" value="{{ old('title') }}" required 
                                 placeholder="Masukkan judul karya"
                                 class="{{ $errors->has('title') ? 'error' : '' }}" />
                        @error('title')
                            <p class="form-helper error mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group mb-6">
                        <x-label for="description" required>Deskripsi Karya</x-label>
                        <textarea name="description" id="description" rows="5" required
                            class="form-input w-full {{ $errors->has('description') ? 'error' : '' }}"
                            placeholder="Jelaskan karya kamu: apa yang dibuat, teknologi yang dipakai, dsb...">{{ old('description') }}</textarea>
                        <p class="form-helper mt-1 text-ink/60">Maksimal 5000 karakter</p>
                        @error('description')
                            <p class="form-helper error mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group mb-6">
                        <x-label for="thumbnail" required>Thumbnail Karya <span class="font-normal text-xs text-ink/60">(max 2MB)</span></x-label>
                        <input type="file" name="thumbnail" id="thumbnail" accept="image/jpeg,image/png,image/webp" required
                            @change="preview = URL.createObjectURL($event.target.files[0])"
                            class="form-input w-full text-sm {{ $errors->has('thumbnail') ? 'error' : '' }} file:bg-ink file:text-surface file:border-0 file:px-4 file:py-2 file:mr-4 file:font-bold file:cursor-pointer hover:file:bg-ink/80">
                        <img x-show="preview" :src="preview" class="mt-4 max-h-48 border-2 border-ink shadow-[4px_4px_0px_0px_var(--color-ink)]" x-cloak
                            alt="Preview thumbnail karya">
                        @error('thumbnail')
                            <p class="form-helper error mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group mb-6">
                        <x-label for="screenshots">Screenshot Tambahan <span class="font-normal text-xs text-ink/60">(max 5 file, max 2MB/file)</span></x-label>
                        <input type="file" name="screenshots[]" id="screenshots" accept="image/jpeg,image/png,image/webp" multiple
                            class="form-input w-full text-sm {{ $errors->has('screenshots') || $errors->has('screenshots.*') ? 'error' : '' }} file:bg-canvas file:text-ink file:border-2 file:border-ink file:px-4 file:py-1 file:mr-4 file:font-bold file:cursor-pointer hover:file:bg-muted">
                        @error('screenshots')
                            <p class="form-helper error mt-1">{{ $message }}</p>
                        @enderror
                        @error('screenshots.*')
                            <p class="form-helper error mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="form-group">
                            <x-label for="demo_url">Link Demo/Live <span class="font-normal text-xs text-ink/60">(opsional)</span></x-label>
                            <x-input type="url" name="demo_url" id="demo_url" value="{{ old('demo_url') }}" placeholder="https://..."
                                class="{{ $errors->has('demo_url') ? 'error' : '' }}" />
                            @error('demo_url')
                                <p class="form-helper error mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="form-group">
                            <x-label for="github_url">Link Repository <span class="font-normal text-xs text-ink/60">(opsional)</span></x-label>
                            <x-input type="url" name="github_url" id="github_url" value="{{ old('github_url') }}" placeholder="https://github.com/..."
                                class="{{ $errors->has('github_url') ? 'error' : '' }}" />
                            @error('github_url')
                                <p class="form-helper error mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-8">
                    <x-button type="submit" variant="primary" class="w-full justify-center py-4 text-lg">
                        Submit Final Karya
                    </x-button>
                </div>
            </form>

            {{-- Confirmation Modal --}}
            <div x-show="confirmOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-ink/50 backdrop-blur-sm" @click="confirmOpen = false"></div>

                <div class="bg-surface border-4 border-ink p-6 md:p-8 max-w-md w-full shadow-[8px_8px_0px_0px_var(--color-ink)] z-10 relative">
                    <div class="icon-container diamond mb-6 bg-primary-yellow text-ink border-2 border-ink">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="square" stroke-linejoin="miter">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                    <h3 class="font-display font-black text-2xl mb-3 text-ink uppercase leading-tight">Konfirmasi Submit Karya</h3>
                    <p class="font-body text-ink/80 mb-8 leading-relaxed">Apakah Anda yakin data dan karya yang diunggah sudah final? Data tidak dapat diubah kembali setelah disubmit.</p>

                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-3">
                        <x-button type="button" variant="outline" @click="confirmOpen = false" class="justify-center">
                            Periksa Kembali
                        </x-button>
                        <x-button type="button" variant="primary" @click="submitForm" class="justify-center">
                            Ya, Kirim Final
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
