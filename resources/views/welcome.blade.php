@extends('voting.layouts.app')

@section('title', 'Inready VOTES — Voting On Talent Excellence & Showcase')

@section('content')
    <!-- HERO SECTION -->
    <div class="hero rounded-sm mb-12">
        <div class="hero-inner">
            <div class="hero-content">
                <div class="hero-version">Product Requirements Document — v2.1 Final</div>
                <h1>Inready<br>VOTES</h1>
                <p class="mb-6 text-lg font-body max-w-lg">
                    Sistem voting berbasis web untuk memilih karya terbaik calon anggota Inready Workgroup saat event Pameran Karya tahunan.
                </p>
                <div class="flex gap-4">
                    <x-button href="/vote" variant="primary" size="lg">
                        Mulai Voting
                    </x-button>
                </div>
            </div>
            
            <div class="hero-visual hidden md:flex">
                <div class="geo-circle"></div>
                <div class="geo-square"></div>
                <div class="geo-triangle"></div>
            </div>
        </div>
    </div>

    <!-- WHAT & WHY SECTION -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
        <x-card shadow="md">
            <h2 class="font-display font-black text-2xl uppercase tracking-tight mb-4 border-b-4 border-ink pb-2 inline-block">1.1 Apa Ini</h2>
            <p class="font-body text-ink leading-relaxed">
                Inready VOTES adalah sistem voting berbasis web untuk memilih karya terbaik calon anggota Inready Workgroup saat event Pameran Karya tahunan. Peserta menampilkan karya mereka, anggota aktif memberikan vote, dan sistem menghitung serta menampilkan hasil secara transparan.
            </p>
            <p class="font-body text-grey mt-4 text-sm italic border-l-4 border-primary-yellow pl-3">
                Ini adalah produk pertama yang berhasil di-ship oleh Inready setelah 12 tahun website organisasi tidak pernah sampai production.
            </p>
        </x-card>

        <x-card shadow="md">
            <h2 class="font-display font-black text-2xl uppercase tracking-tight mb-4 border-b-4 border-ink pb-2 inline-block">1.2 Mengapa Dibutuhkan</h2>
            <p class="font-body text-ink leading-relaxed">
                Saat ini pemilihan karya terbaik dilakukan secara manual — tidak tercatat, tidak transparan, dan tidak bisa di-reproduce. Sistem ini menyelesaikan masalah itu sekaligus menjadi fondasi ekosistem digital Inready ke depan.
            </p>
        </x-card>
    </div>

    <!-- WHEN & WHO SECTION -->
    <div class="mb-12">
        <x-card shadow="lg" border="thick" accent="square">
            <div class="mb-8">
                <h2 class="font-display font-black text-2xl uppercase tracking-tight mb-3">1.3 Kapan Digunakan</h2>
                <p class="font-body text-ink leading-relaxed">
                    Sistem aktif 1-3 hari per tahun saat event Pameran Karya (rekrutmen tahunan). Periode voting aktif biasanya 1-3 jam per hari event. Di luar event, sistem dalam kondisi archived/dormant.
                </p>
            </div>

            <div>
                <h2 class="font-display font-black text-2xl uppercase tracking-tight mb-4">1.4 Siapa yang Menggunakan</h2>
                <div class="overflow-x-auto border-4 border-ink shadow-[6px_6px_0px_0px_var(--color-ink)] bg-surface">
                    <table class="w-full text-left font-body min-w-[600px]">
                        <thead>
                            <tr class="bg-ink text-surface">
                                <th class="p-4 font-display font-bold uppercase tracking-widest text-sm border-r-2 border-surface/20 border-b-2 border-surface/20">User</th>
                                <th class="p-4 font-display font-bold uppercase tracking-widest text-sm border-r-2 border-surface/20 border-b-2 border-surface/20">Deskripsi</th>
                                <th class="p-4 font-display font-bold uppercase tracking-widest text-sm border-b-2 border-surface/20">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b-2 border-ink hover:bg-canvas transition-colors">
                                <td class="p-4 font-bold border-r-2 border-ink"><x-badge type="website">Admin</x-badge></td>
                                <td class="p-4 border-r-2 border-ink">Pengurus Inready yang mengelola event dan voting</td>
                                <td class="p-4 text-grey">2-3 orang</td>
                            </tr>
                            <tr class="border-b-2 border-ink hover:bg-canvas transition-colors">
                                <td class="p-4 font-bold border-r-2 border-ink"><x-badge type="design">Peserta</x-badge></td>
                                <td class="p-4 border-r-2 border-ink">Calon anggota yang submit karya untuk dinilai</td>
                                <td class="p-4 text-grey">15-50 per event</td>
                            </tr>
                            <tr class="border-b-2 border-ink hover:bg-canvas transition-colors">
                                <td class="p-4 font-bold border-r-2 border-ink"><x-badge type="mobile">Voter</x-badge></td>
                                <td class="p-4 border-r-2 border-ink">Anggota aktif yang memberikan vote</td>
                                <td class="p-4 text-grey">40-100 per event</td>
                            </tr>
                            <tr class="hover:bg-canvas transition-colors">
                                <td class="p-4 font-bold border-r-2 border-ink"><x-badge type="default">Publik</x-badge></td>
                                <td class="p-4 border-r-2 border-ink">Siapa saja yang melihat gallery karya tanpa vote</td>
                                <td class="p-4 text-grey">Tidak terbatas</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </x-card>
    </div>
@endsection
