# Inready VOTES

Sistem voting internal Inready Workgroup berbasis Laravel + Blade untuk alur:

- manajemen event voting oleh admin,
- submit karya oleh member,
- kurasi karya oleh admin,
- voting karya oleh member,
- publikasi hasil voting per konsentrasi.

Seluruh modul voting berjalan di prefix route `/vote` dan dipisahkan dari route utama aplikasi.

## Status Pengembangan

- Fase 0: Foundation - selesai
- Fase 1: Admin Panel - selesai
- Fase 2: Submit Karya - selesai
- Fase 3: Gallery & Detail - selesai
- Fase 4: Voting Mechanism - selesai
- Fase 5: Hasil Voting & Polish - berjalan (fitur inti sudah terpasang)
- Fase 6: Deploy & Testing - belum dimulai

## Fitur Utama Saat Ini

### Public & Member

- Landing voting event aktif/selesai.
- Gallery karya approved per event dengan filter konsentrasi.
- Detail karya dengan screenshot, deskripsi, dan link demo.
- Login khusus voter/member.
- Vote karya dengan guard bisnis:
	- event harus `voting_open`,
	- 1 vote per konsentrasi per event,
	- total maksimal 3 vote per user per event,
	- akun nonaktif tidak bisa voting.
- Halaman "Vote Saya" per event.
- Halaman hasil voting (`/vote/event/{slug}/hasil`) ketika event `closed` atau `archived`:
	- ranking per konsentrasi,
	- highlight juara #1,
	- total voter unik,
	- total vote,
	- informasi waktu penutupan voting.

### Admin

- CRUD event voting.
- Kontrol status event dengan validasi transisi:
	- `draft -> submission_open`
	- `submission_open -> voting_open | draft`
	- `voting_open -> closed`
	- `closed -> archived`
- Timestamp status voting otomatis:
	- `voting_opened_at`
	- `voting_closed_at`
- Review submission (approve/reject).
- Manajemen member voting.

### UX & Error Handling

- Flash message auto-dismiss (dengan opsi tutup manual).
- Layout publik dan admin lebih responsif.
- Error page khusus area voting (`/vote/*`) untuk 403/404/419/429/500.

## Stack

- PHP 8.2+
- Laravel Framework (`laravel/framework` ^12)
- Blade + Alpine.js
- Tailwind CSS v4 + Vite
- MySQL

## Struktur Kode Penting

- Routes voting: `routes/voting.php`
- Controller voting: `app/Http/Controllers/Voting/`
- View voting: `resources/views/voting/`
- Model domain voting:
	- `app/Models/VotingEvent.php`
	- `app/Models/Submission.php`
	- `app/Models/SubmissionScreenshot.php`
	- `app/Models/Vote.php`
- Seeder voting: `database/seeders/VotingSeeder.php`
- Dokumen requirement:
	- `docs/TRD-voting-system-v1.md`
	- `docs/phase/`
	- `docs/REVISION-LOGS.md`

## Cara Menjalankan Lokal

1. Install dependency:

	 ```bash
	 composer install
	 npm install
	 ```

2. Siapkan environment:

	 ```bash
	 cp .env.example .env
	 php artisan key:generate
	 ```

3. Setup database + seed:

	 ```bash
	 php artisan migrate:fresh --seed
	 php artisan storage:link
	 ```

4. Jalankan aplikasi:

	 ```bash
	 npm run dev
	 php artisan serve
	 ```

5. Buka:

	 - Home Laravel: `http://127.0.0.1:8000/`
	 - Voting app: `http://127.0.0.1:8000/vote/`

## Akun Seed Development

- Admin:
	- email: `admin@inready.com`
	- password: `password`
- Member:
	- email: `member1@inready.com` s.d. `member5@inready.com`
	- password: `password`

## Event Seed Development

- `inready-showcase-2026` (status: `submission_open`)
	- `voting_opened_at`: null
	- `voting_closed_at`: null
- `inready-voting-2026` (status: `voting_open`)
	- `voting_opened_at`: terisi default waktu seed
	- `voting_closed_at`: null
- `inready-hasil-2026` (status: `closed`)
	- `voting_opened_at`: terisi default waktu seed
	- `voting_closed_at`: terisi default waktu seed
	- berisi sample submission + vote untuk validasi halaman hasil

## Testing

Jalankan seluruh test:

```bash
php artisan test
```

Jalankan test voting terkait fase saat ini:

```bash
php artisan test tests/Feature/Voting/SubmitKaryaTest.php tests/Feature/Voting/VoteMechanismTest.php tests/Feature/Voting/ResultsPageTest.php
```

## Catatan Tambahan

- Setelah perubahan skema terbaru (termasuk timestamp voting), pastikan menjalankan migrasi terbaru sebelum pengujian.
- Log revisi development dicatat di `docs/REVISION-LOGS.md` (entri terbaru di bagian paling atas).
