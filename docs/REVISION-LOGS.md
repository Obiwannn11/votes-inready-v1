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
