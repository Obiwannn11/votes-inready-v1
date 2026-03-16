# Revision Logs - Inready VOTES

Dokumen ini digunakan untuk mencatat setiap perubahan, perbaikan, atau penambahan fitur yang dilakukan pada sistem voting selama tahap development, testing, dan post-deployment. Log ini membantu melacak *apa* yang diubah, *mengapa* diubah, dan *siapa* atau *bagaimana* dampaknya terhadap sistem keseluruhan.

---

## 📝 Format Penulisan (Template)

Gunakan template di bawah ini setiap kali mencatat revisi baru. Tambahkan entri terbaru di bagian **paling atas** (di bawah garis pemisah laporan log).

```markdown
### [Versi/Update] - [Tanggal]
**Author:** [Nama/Developer]
**Fase Terkait:** [Fase 0 - Fase 6]

**Detail Perubahan:**
- [Added] Menambahkan [fitur/logic baru] di file `[nama file]`.
- [Fixed] Memperbaiki bug [nama bug] yang menyebabkan [efek bug].
- [Changed] Mengubah [logic/UI lama] menjadi [logic/UI baru] karena [alasan].
- [Removed] Menghapus [kode/fitur] karena [alasan].

**Dampak/Catatan Khusus:**
- *Berikan penjelasan singkat jika revisi ini membutuhkan command artisian baru (ex: `php artisan migrate` atau `route:clear`)*
```

---

## 📜 Daftar Revisi

### v2.3.0 - 16 Maret 2026
**Author:** AI Assistant
**Fase Terkait:** Fase 2 (Submit Karya) & Fase 1 (Admin Review)

**Detail Perubahan:**
- [Changed] Mengubah alur submit karya di `app/Http/Controllers/Voting/SubmitKaryaController.php` menjadi *single submission flow* per event: user hanya bisa membuat 1 submission, dan hanya bisa edit/kirim ulang saat status `rejected`.
- [Changed] Menambahkan proteksi database pada `database/migrations/2026_03_13_135552_create_submissions_table.php` dengan unique key `voting_event_id + submitter_id` untuk mencegah upload ganda lintas concentration (website/design/mobile) dalam event yang sama.
- [Changed] Memperbarui validasi `app/Http/Requests/Voting/SubmitKaryaRequest.php` agar thumbnail wajib saat submit pertama, namun opsional saat edit karya yang `rejected`.
- [Changed] Menyelaraskan UI member di `resources/views/voting/submit/index.blade.php`, `form.blade.php`, dan `status.blade.php`: tombol submit baru tidak muncul untuk status `pending/approved`, mode edit hanya untuk `rejected`, tampil alasan reject admin, dan tampil pesan selamat saat karya `approved`.
- [Changed] Memperbarui review admin di `app/Http/Requests/Voting/Admin/ReviewSubmissionRequest.php`, `app/Http/Controllers/Voting/Admin/SubmissionController.php`, `resources/views/voting/admin/submissions/show.blade.php`, dan `index.blade.php` agar aksi reject wajib menyertakan alasan (`admin_notes`).
- [Changed] Menyesuaikan idempotensi seeder pada `database/seeders/VotingSeeder.php` agar `updateOrCreate` submission mengikuti unique key baru (`event + submitter`).
- [Added] Menambah dan menyesuaikan test pada `tests/Feature/Voting/SubmitKaryaTest.php`, `tests/Feature/Voting/AdminSubmissionReviewTest.php`, `tests/Feature/Voting/VoteMechanismTest.php`, dan `tests/Feature/Voting/ResultsPageTest.php` untuk meng-cover aturan baru.

**Dampak/Catatan Khusus:**
- Karena ada perubahan constraint migration submissions, environment development disarankan menjalankan `php artisan migrate:fresh --seed`.
- Validasi regresi yang sudah diverifikasi: `php artisan test tests/Feature/Voting/SubmitKaryaTest.php tests/Feature/Voting/AdminSubmissionReviewTest.php tests/Feature/Voting/VoteMechanismTest.php tests/Feature/Voting/ResultsPageTest.php` (30 passed).

### v2.2.0 - 16 Maret 2026
**Author:** AI Assistant
**Fase Terkait:** Fase 2 & Polish Design System

**Detail Perubahan:**
- [Fixed] Menghilangkan bug *double underline* pada layout Navbar saat kursor `hover` atau link `active` di `resources/views/voting/layouts/app.blade.php`.
- [Changed] Menyembunyikan menu navigasi "Submission" secara keseluruhan bagi Guest (belum login) untuk mengikuti flow eksklusif auth (sebelumnya menampilkan logo gembok).
- [Added] Menambahkan halaman Daftar Event Terbuka di route `/submit` untuk mewadahi semua event yang berstatus `submission_open` pada `resources/views/voting/submit/index.blade.php`.
- [Added] Memperbarui Controller `SubmitKaryaController@index` untuk memfilter event submissions berdasarkan status aktif dengan route name `voting.submit.index`.
- [Changed] Merombak tampilan Form Kumpul Karya (`resources/views/voting/submit/form.blade.php`) menggunakan token *InReady Bauhaus* (solid border, brutalist input, dan sharp shadow).
- [Added] Menambahkan utilitas *Pop-up Confirmation* / *Modal Alert* sebelum mengirim final data form menggunakan layer `Alpine.js` agar peserta meninjau kembali kumpulannya selaras dengan konfirmasi "tidak bisa diubah" dari aturan kompetisi.
- [Changed] Memperbarui `resources/views/voting/partials/error-page.blade.php` agar layout status error 404/403/500 selaras dengan *Design System v2.0* (kartu solid, elemen dekorasi SVG).
- [Removed] Menghapus direct URL route submission dari yang semula spesifik ke parameter slug form langsung tanpa melalui halaman index pilihan.

**Dampak/Catatan Khusus:**
- View Submission sekarang dilalui dengan mengakses route baru `/submit` (daftar event), lalu diarahkan ke form `/submit/{event}` masing-masing.

### v2.1.0 - 16 Maret 2026
**Author:** AI Assistant
**Fase Terkait:** Front Door (Landing Page) & Navigation

**Detail Perubahan:**
- [Added] Menambahkan link navigasi "Event" di Navbar pada `resources/views/voting/layouts/app.blade.php`.
- [Changed] Mengubah link logo "Inready VOTES" pada Navbar agar mengarah ke root landing page `/` alih-alih `voting.landing`.
- [Changed] Memperbarui `routes/web.php` untuk mengambil *query* `$submissionEvents` yang masih *open* saat memuat route `/` dan memparsingnya ke compact view.
- [Added] Mengimplementasikan desain ulang `resources/views/voting/home.blade.php` sebagai landing page statis (extend layout) dengan gaya Bauhaus yang dinamis, mencakup section kondisional "Submit Karya" ketika event submission dibuka beserta penempatan ilustrasi logo inready.

**Dampak/Catatan Khusus:**
- Navigasi navbar di update agar pengunjung pertama kali dapat masuk dari root ke gallery (Events).

### v2.0.0 - 15 Maret 2026
**Author:** AI Assistant
**Fase Terkait:** Polish & Design System Restructuring

**Detail Perubahan:**
- [Added] Konfigurasi token Theming pada `resources/css/app.css` berdasarkan "InReady Bauhaus Design System v2.0". Memasukkan custom variable warna (`--color-primary-yellow`, dll.), bayangan kontras tinggi (`--shadow-*`), dan font system (`Outfit` & `Poppins`).
- [Added] Membuat sub-komponen Blade modular di `resources/views/components/`: `button.blade.php`, `card.blade.php`, `input.blade.php`, `label.blade.php`, `badge.blade.php`.
- [Changed] Menyesuaikan root layout pada `resources/views/voting/layouts/app.blade.php` dan `admin.blade.php` untuk menampung import font dan mengatur grid/container menggunakan `max-w-[1224px]`.
- [Changed] Refaktor masif UI publik agar selaras dengan pola *Neo-brutalism/Bauhaus* (bayangan tajam, warna murni blok kontras, komponen bold). Mengubah format `auth/login`, `landing.blade.php`, `gallery/index`, `gallery/show`, `vote/my-votes`, dan `results/index`.
- [Fixed] Menyempurnakan pewarnaan teks "Total Voter / Vote" yang sebelumnya tidak nampak pada hasil di `voting/results/index.blade.php` karena isu CSS specificy inheritance.
- [Changed] Memodifikasi dot aksen abstrak di komponen `<x-card>` (`circle`, `circle-success`, `circle-muted`) menyesuaikan status dinamis event (aktif = circle-success, tutup = circle-muted) di halaman depan.
- [Added] Memastikan integrasi komponen visual baru tetap menjaga reaktivitas AlpineJS dan arsitektur route yang ada di fase sebelumnya.

**Dampak/Catatan Khusus:**
- Transisi desain dari utilitas biasa ke full design system "InReady Bauhaus".
- Implementasi menggunakan flexibilitas dari standardisasi Tailwind v4 yang menyematkan utility langsung di layer theme.

### v1.0.10 - 15 Maret 2026
**Author:** AI Assistant
**Fase Terkait:** Fase 5 (Hasil Voting & Polish)

**Detail Perubahan:**
- [Changed] Menyesuaikan skema event voting dengan pendekatan non-production: kolom `voting_opened_at` dan `voting_closed_at` ditempatkan langsung di migration utama `database/migrations/2026_03_13_135551_create_voting_events_table.php`.
- [Changed] Memperbarui `database/seeders/VotingSeeder.php` agar event seed otomatis mengisi default timestamp voting open/closed.
- [Added] Menambahkan seed event `closed` (`inready-hasil-2026`) beserta sample submission + vote agar halaman hasil voting bisa divalidasi langsung setelah `migrate:fresh --seed`.
- [Changed] Merapikan UI Fase 5 pada `resources/views/voting/results/index.blade.php` dengan empty state saat belum ada hasil.
- [Changed] Merapikan responsivitas navbar publik pada `resources/views/voting/layouts/app.blade.php` untuk mencegah overflow nama user/link di layar kecil.
- [Changed] Merapikan sidebar mobile admin pada `resources/views/voting/layouts/admin.blade.php` (state default tertutup, close on escape, auto-close setelah klik menu).
- [Changed] Merapikan kontrol transisi status event di `resources/views/voting/admin/events/show.blade.php` agar status yang tidak valid tampil nonaktif.
- [Changed] Menyempurnakan aksesibilitas gambar dan lazy loading di `resources/views/voting/admin/submissions/index.blade.php`, `resources/views/voting/admin/submissions/show.blade.php`, `resources/views/voting/submit/form.blade.php`, dan `resources/views/voting/vote/my-votes.blade.php`.
- [Changed] Memperbarui dokumentasi seed event di `README.md` agar sesuai data default terbaru.

**Dampak/Catatan Khusus:**
- Untuk melihat data hasil voting contoh, jalankan `php artisan migrate:fresh --seed`.
- Setelah seed, event closed yang siap diuji ada di slug `inready-hasil-2026`.

### v1.0.9 - 15 Maret 2026
**Author:** AI Assistant
**Fase Terkait:** Fase 5 (Hasil Voting & Polish)

**Detail Perubahan:**
- [Added] Menambahkan `ResultController` di `app/Http/Controllers/Voting/ResultController.php` untuk hasil voting per event dengan guard akses hanya saat status event `closed/archived`.
- [Added] Menambahkan route hasil voting `GET /vote/event/{slug}/hasil` (`voting.results`) di `routes/voting.php`.
- [Added] Menambahkan view hasil voting `resources/views/voting/results/index.blade.php` berisi ranking per konsentrasi, highlight juara, total voter unik, total vote, dan link kembali ke gallery.
- [Changed] Menambahkan dukungan kolom `voting_opened_at` dan `voting_closed_at` pada skema `voting_events` untuk kebutuhan hasil voting.
- [Changed] Memperbarui `app/Models/VotingEvent.php` dengan casts timestamp voting dan helper validasi transisi status `canTransitionTo()`.
- [Changed] Memperbarui `app/Http/Controllers/Voting/Admin/EventController.php` agar perubahan status event tervalidasi sesuai alur transisi dan otomatis mengisi timestamp buka/tutup voting.
- [Changed] Menambahkan tombol `Lihat Hasil Voting` pada gallery event closed di `resources/views/voting/gallery/index.blade.php`.
- [Added] Menambahkan custom error page voting `resources/views/voting/partials/error-page.blade.php` dan exception renderer khusus route `/vote/*` di `bootstrap/app.php`.
- [Changed] Melakukan polish UI di `resources/views/voting/layouts/app.blade.php` dan `resources/views/voting/layouts/admin.blade.php`: flash auto-dismiss, peningkatan responsif, serta perbaikan kegunaan mobile pada panel admin.
- [Changed] Menambahkan kontrol status `archived` dan info waktu buka/tutup voting pada `resources/views/voting/admin/events/show.blade.php`.
- [Added] Menambahkan test Fase 5 di `tests/Feature/Voting/ResultsPageTest.php` untuk guard hasil, ranking/statistik, link hasil di gallery, dan render custom 404 voting.
- [Changed] Mengganti `README.md` default Laravel menjadi dokumentasi proyek Inready VOTES berbahasa Indonesia yang merangkum fitur hingga fase berjalan.

**Dampak/Catatan Khusus:**
- Jalankan migrasi terbaru sebelum test: `php artisan migrate` (atau `php artisan migrate:fresh --seed` untuk reset environment lokal).
- Validasi regresi disarankan dengan: `php artisan test tests/Feature/Voting/SubmitKaryaTest.php tests/Feature/Voting/VoteMechanismTest.php tests/Feature/Voting/ResultsPageTest.php`.

### v1.0.8 - 14 Maret 2026
**Author:** AI Assistant
**Fase Terkait:** Fase 4 (Voting Mechanism)

**Detail Perubahan:**
- [Added] Menambahkan `VoteController` khusus voting di `app/Http/Controllers/Voting/VoteController.php` dengan guard event status, guard submission approved+event match, guard user aktif, batas 1 vote per konsentrasi, dan batas total 3 vote per event.
- [Added] Menambahkan endpoint voting di `routes/voting.php`: `POST /vote/event/{slug}/vote/{submission}` (`voting.vote`, throttle `30/menit`) dan `GET /vote/event/{slug}/my-votes` (`voting.my-votes`).
- [Added] Menambahkan halaman `resources/views/voting/vote/my-votes.blade.php` untuk menampilkan daftar vote user per event beserta counter `x/3`.
- [Changed] Mengaktifkan tombol vote + modal konfirmasi di `resources/views/voting/gallery/show.blade.php` (menggantikan placeholder Fase 3).
- [Changed] Mengupdate navbar `resources/views/voting/layouts/app.blade.php` dengan link `Vote Saya` saat berada di route event (berparameter `slug`) dan menambahkan dukungan `x-cloak`.
- [Changed] Mengupdate `app/Http/Controllers/Voting/Auth/LoginController.php`: menolak login `is_active = false`, menjaga default admin ke admin panel, serta redirect `intended` untuk flow setelah login.
- [Changed] Memperketat constraint votes di `database/migrations/2026_03_13_135554_create_votes_table.php`: `concentration` wajib isi (non-null) dan tambahan unique key `voting_event_id + voter_id + submission_id`.
- [Added] Menambahkan test Fase 4 di `tests/Feature/Voting/VoteMechanismTest.php` meliputi auth guard, inactive login guard, happy path voting, constraint konsentrasi, batas total 3 vote, guard 404, my-votes visibility, dan verifikasi middleware throttle.

**Dampak/Catatan Khusus:**
- Karena ada perubahan migration votes, jalankan ulang database dengan `php artisan migrate:fresh --seed` pada environment development.
- Validasi test berjalan hijau: `php artisan test tests/Feature/Voting/VoteMechanismTest.php tests/Feature/Voting/SubmitKaryaTest.php` (19 passed).

### v1.0.7 - 14 Maret 2026
**Author:** AI Assistant
**Fase Terkait:** Fase 3 (Gallery & Detail Karya)

**Detail Perubahan:**
- [Added] Menambahkan `GalleryController` untuk alur landing event, gallery per event, dan detail karya publik di `app/Http/Controllers/Voting/GalleryController.php`.
- [Added] Menambahkan route publik Fase 3 di `routes/voting.php`: `/vote/`, `/vote/event/{slug}`, dan `/vote/event/{slug}/karya/{id}`.
- [Added] Menambahkan view `resources/views/voting/gallery/index.blade.php` dan `resources/views/voting/gallery/show.blade.php` dengan fitur filter konsentrasi, badge status voting, indikator vote user login, serta placeholder tombol vote untuk persiapan Fase 4.
- [Changed] Mengubah `resources/views/voting/landing.blade.php` agar menampilkan daftar event status `voting_open/closed` dan redirect otomatis ke gallery jika hanya ada satu event yang tampil.
- [Changed] Menyesuaikan kontrak model: penggunaan relasi `submitter` untuk nama peserta, penggunaan field screenshot `image_path`, dan penambahan helper status pada `app/Models/VotingEvent.php` (`approvedSubmissions`, `isVotingOpen`, `isClosed`, `isPublishedForGallery`).
- [Changed] Memperluas enum status event dengan `archived` pada migration `database/migrations/2026_03_13_135551_create_voting_events_table.php` serta validasi status admin di `app/Http/Controllers/Voting/Admin/EventController.php`.
- [Changed] Merevisi `database/seeders/VotingSeeder.php` agar seed data thumbnail/screenshot langsung mengarah ke `public/images/placeholder-ss.png` melalui path `images/placeholder-ss.png` untuk kebutuhan development `migrate:fresh --seed`.
- [Changed] Menyesuaikan view admin submission agar kompatibel dengan path gambar publik (`images/...`) maupun path storage (`storage/...`) di `resources/views/voting/admin/submissions/index.blade.php` dan `resources/views/voting/admin/submissions/show.blade.php`.

**Dampak/Catatan Khusus:**
- Setelah update migration enum, jalankan ulang database dengan `php artisan migrate:fresh --seed`.
- Checklist Fase 3 kini siap divalidasi end-to-end sebelum lanjut ke Fase 4 (Voting Mechanism).

### v1.0.6 - 14 Maret 2026
**Author:** AI Assistant
**Fase Terkait:** Fase 2 (Submit Karya)

**Detail Perubahan:**
- [Added] Menambahkan Feature Test end-to-end untuk alur Submit Karya berbasis auth member di `tests/Feature/Voting/SubmitKaryaTest.php`.
- [Added] Skenario test meliputi: guard guest redirect login, akses form hanya saat submission open, larangan submit untuk admin, validasi thumbnail wajib, simpan submission + screenshot, dan isolasi data status per member.
- [Fixed] Menyinkronkan environment test melalui `composer install` agar plugin/dependency Pest konsisten sebelum menjalankan suite test.

**Dampak/Catatan Khusus:**
- Jalankan test fokus fase ini dengan command: `php artisan test tests/Feature/Voting/SubmitKaryaTest.php`.
- Status terakhir: 8 test passed.

### v1.0.5 - 14 Maret 2026
**Author:** AI Assistant
**Fase Terkait:** Fase 2 (Refactor Migration & Seeder)

**Detail Perubahan:**
- [Changed] Memecah migration voting yang sebelumnya monolitik menjadi migration terpisah per tabel: `voting_events`, `submissions`, `submission_screenshots`, dan `votes` dengan urutan timestamp berurutan.
- [Removed] Menghapus migration patch tambahan (`adjust_voting_tables_for_phase2`) dan menyatukan kolom final (`slug`, `concentration`, `admin_notes`) langsung ke migration create table masing-masing.
- [Changed] Merevisi `VotingSeeder` menjadi skenario data yang sinkron dengan skema terbaru serta idempotent melalui `updateOrCreate`.
- [Changed] Merevisi `DatabaseSeeder` agar langsung memanggil `VotingSeeder` sebagai sumber seed utama proyek.

**Dampak/Catatan Khusus:**
- Setup database kini siap dijalankan ulang dari nol menggunakan `php artisan migrate:fresh --seed`.
- Seeder menyediakan akun admin/member serta event `submission_open` dan `voting_open` untuk kebutuhan pengujian fase berjalan.

### v1.0.4 - 13 Maret 2026
**Author:** AI Assistant
**Fase Terkait:** Fase 2 (Submit Karya)

**Detail Perubahan:**
- [Added] Menambahkan skenario "Auth" untuk Submit Karya (Skenario A) di mana hanya member yang login yang dapat men-submit form pendaftaran.
- [Added] Menambahkan field migrations: kolom `slug` di `voting_events` untuk vanity/Route URL, serta `concentration` dan `admin_notes` pada tabel `submissions`.
- [Added] Konfigurasi *Route Model Binding* dengan method `getRouteKeyName` di dalam model `VotingEvent` untuk menggunakan kolom `slug`.
- [Changed] Standardisasi rule SubmitKaryaRequest agar mengambil relasi identitas dari `Auth::id()` tanpa menerima *raw input* email untuk menghindari pemalsuan pendaftaran orang lain.
- [Added] Pembuatan view `voting.submit.form` dan `voting.submit.status` menggunakan arsitektur relasi submission berdasarkan akun pengguna (Vite + Tailwind).

**Dampak/Catatan Khusus:**
- Jalankan `php artisan migrate` jika belum.
- URL untuk submit menjadi `/submit/{event_slug}`, namun pastikan testing dengan akun *member* karena hanya role `member` yang valid lolos Form Request authorize().

### v1.0.3 - 13 Maret 2026
**Author:** AI Assistant
**Fase Terkait:** Fase 0 & Fase 1

**Detail Perubahan:**
- [Added] Menggunakan instalasi **Tailwind CSS + Vite** (seperti environment Inertia) ketimbang CDN untuk View Admin & Public. Menghilangkan masalah *load delay* dan *network dependency*.
- [Added] Semua sistem routing backend untuk aplikasi voting dikumpulkan dalam sebuah *Route Group* dengan prefix `/vote/` di dalam satu file tersentralisasi `routes/voting.php`.
- [Added] Konfigurasi relasi *Views*, *Controllers*, beserta relasi Eloquent (`VotingEvent`, `Submission`, `Vote`).
- [Fixed] Penggunaan *Facade* yang konsisten di semua Controller, Middleware, & Routes (misal, `View::make()`, `Redirect::route()`, dan `Auth::check()`).

**Dampak/Catatan Khusus:**
- Anda bisa jalankan `npm run dev` dan `php artisan serve` untuk live reload Vite untuk views *voting*.

### v1.0.2 - 13 Maret 2026
**Author:** AI Assistant
**Fase Terkait:** Fase 0 & Fase 1

**Detail Perubahan:**
- [Changed] Standardisasi penulisan *codebase*: diwajibkan menggunakan **Facade** (contoh: `Auth::check()`, `View::make()`) ketimbang global helper functions (seperti `auth()->check()`) untuk mencegah error pada IDE dan meningkatkan *readability*.
- [Added] Struktur Controller dan View dipisahkan secara tegas antara `Admin` dan *Public/Member*.
- [Added] Mengimplementasikan `FormRequest` untuk menangani logika validasi di controller agar controller tetap bersih dan memisahkan *concern*.

**Dampak/Catatan Khusus:**
- Middleware autentikasi diupdate menggunakan pendekatan OOP Class (`Auth::check()`).
- Semua controller `store`, `update`, dan aksi formulir lainnya menggunakan *Custom Request*.

### v1.0.1 - 13 Maret 2026
**Author:** AI Assistant
**Fase Terkait:** Fase 0 & Fase 1

**Detail Perubahan:**
- [Changed] Mengubah arsitektur dasar yang semula tergabung dalam repo Inertia *Company Profile*, menjadi sistem *standalone* dengan repo terpisah (Full-Blade).
- [Added] Menambahkan kolom `role` (enum: 'admin', 'member') dan `is_active` (boolean) di tabel `users` (file `database/migrations/0001_01_01_000000_create_users_table.php`).
- [Added] Menambahkan `role` dan `is_active` ke `$fillable` array dan melakukan casting `is_active` sebagai boolean pada `app/Models/User.php`.
- [Changed] Memilih menggunakan metode Autentikasi Manual ketimbang Breeze untuk kemudahan interoperabilitas jika ke depan sistem loginnya ingin dipusatkan (SSO/Unified).

**Dampak/Catatan Khusus:**
- Kita tidak perlu lagi menyesuaikan *Middleware* Inertia di Fase 0.
- Jalankan perintah *migrate:fresh* untuk memperbarui tabel: `php artisan migrate:fresh`

### v1.0.0 - 13 Maret 2026
**Author:** AI Assistant
**Fase Terkait:** Inisiasi Dokumen

**Detail Perubahan:**
- [Added] Membuat file `REVISION-LOGS.md` untuk tracking perubahan TRD dan codebase voting ke depannya.
- [Added] Membuat template standardisasi penulisan log revisi.

**Dampak/Catatan Khusus:**
- Merupakan dokumen dasar sebelum eksekusi FASE 0 dimulai.
