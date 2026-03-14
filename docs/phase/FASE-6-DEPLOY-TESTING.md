# FASE 6: Deploy & Testing

**Produk:** Inready VOTES  
**Estimasi:** 1-2 hari  
**Prasyarat:** FASE 5 checklist 100% centang  
**Output:** Voting system live di production, tested oleh 5-10 orang internal, siap untuk event

---

## Tujuan Fase Ini

Sistem dipindah dari lokal ke production server, ditest dengan user nyata, bug di-fix, dan admin di-brief cara pakai. Setelah fase ini selesai: SIAP EVENT.

---

## Step 1: Pre-deploy Checklist

Sebelum push ke server, pastikan di lokal:

```bash
# Clear semua cache dulu
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Pastikan tidak ada error
php artisan route:list --path=vote
# → Semua route voting harus muncul, tidak ada error

# Cek migration status
php artisan migrate:status
# → Semua ran

# Test manual: jalankan full flow 1 kali
# Admin buat event → peserta submit → admin approve → member vote → tutup → hasil
```

---

## Step 2: Production Config

### .env Production — tambahkan/verifikasi:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://inready.id

# Database — server terpisah
DB_HOST=[IP_SERVER_DB_4GB]
DB_PORT=3306
DB_DATABASE=inready_main
DB_USERNAME=inready_app
DB_PASSWORD=[PASSWORD_KUAT]

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Upload limit
INREADY_MAX_UPLOAD_SIZE=2048
```

### php.ini — cek upload limits:
```bash
# Di server production, cek:
php -i | grep upload_max_filesize    # harus >= 2M
php -i | grep post_max_size          # harus >= 10M
php -i | grep max_file_uploads       # harus >= 6 (1 thumbnail + 5 screenshot)
```

Jika terlalu kecil, edit di PHP-FPM config:
```bash
# Biasanya di /etc/php/8.3/fpm/php.ini
upload_max_filesize = 5M
post_max_size = 25M
max_file_uploads = 10
```

Restart PHP-FPM: `sudo systemctl restart php8.3-fpm`

### Nginx — cek client_max_body_size:
```nginx
# Di /etc/nginx/nginx.conf atau site config
client_max_body_size 25M;
```

Restart Nginx: `sudo systemctl restart nginx`

---

## Step 3: Deploy

```bash
# Di server production:

# 1. Pull latest code
cd /var/www/inready  # atau path project kamu
git pull origin main

# 2. Install dependencies (production mode)
composer install --no-dev --optimize-autoloader

# 3. Run migrations
php artisan migrate --force

# 4. Storage link (jika belum)
php artisan storage:link

# 5. Buat folder upload
mkdir -p storage/app/public/voting/thumbnails
mkdir -p storage/app/public/voting/screenshots
chmod -R 775 storage/app/public/voting

# 6. Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Restart PHP-FPM untuk pick up changes
sudo systemctl restart php8.3-fpm
```

---

## Step 4: Smoke Test Production

**Lakukan test ini langsung di production URL. Semua harus pass.**

```
TEST 1 — HALAMAN PUBLIK:
[ ] Buka https://inready.id/vote/ → landing page tampil
[ ] Buka https://inready.id/ → company profile masih jalan normal
[ ] Buka https://inready.id/vote/event/[slug] → gallery tampil
[ ] Buka detail karya → gambar load, deskripsi tampil

TEST 2 — ADMIN:
[ ] Login di /vote/login dengan akun admin → berhasil
[ ] Buat event test → berhasil
[ ] Ubah status ke submission_open → berhasil
[ ] Copy link submit → buka di incognito

TEST 3 — SUBMIT KARYA:
[ ] Buka link submit di incognito → form tampil
[ ] Isi form + upload gambar → submit berhasil
[ ] Cek status via email → status "Menunggu Review"

TEST 4 — REVIEW + VOTE:
[ ] Admin: approve submission → status berubah
[ ] Admin: ubah event ke voting_open
[ ] Login sebagai member → vote → berhasil
[ ] Cek my votes → tampil

TEST 5 — HASIL:
[ ] Admin: tutup voting (status → closed)
[ ] Buka /vote/event/[slug]/hasil → ranking tampil
[ ] Vote count benar

TEST 6 — EDGE CASES:
[ ] Buka URL yang tidak valid → error page rapi (bukan Laravel stack trace)
[ ] Upload file > 2MB → error message jelas
[ ] Double submit form (klik cepat 2x) → tidak ada duplikasi
```

---

## Step 5: Internal Testing (5-10 orang)

### Setup:
1. Admin buat event voting ASLI (bukan test) atau buat event "Trial Run"
2. Admin input 5-10 member (nama, email, password) → catat di spreadsheet
3. Admin share password ke member via chat privat
4. Minta 2-3 orang berperan sebagai peserta → submit karya test
5. Admin approve karya
6. Admin buka voting
7. Semua member vote

### Yang diminta ke tester:
- Vote dari HP (bukan laptop)
- Screenshot jika ada error atau tampilan aneh
- Catat: halaman mana yang lambat, tombol mana yang confusing, teks mana yang tidak jelas

### Bug tracking (simple):
Buat 1 chat group atau 1 Google Doc. Setiap bug:
```
[SIAPA] [DEVICE] [HALAMAN] [MASALAH]
Contoh: Andi — iPhone 12 — Gallery — Gambar crop aneh di card
```

---

## Step 6: Bug Fixing

Prioritas fix:
1. **Blocker:** tidak bisa vote, tidak bisa submit, data hilang → FIX HARI INI
2. **Major:** tampilan rusak di HP, error message tidak muncul → FIX SEBELUM EVENT
3. **Minor:** typo, warna kurang pas, spacing aneh → FIX KALAU SEMPAT

Setelah fix:
```bash
git add . && git commit -m "fix: [deskripsi bug]"
git push origin main

# Di server:
cd /var/www/inready
git pull origin main
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl restart php8.3-fpm
```

---

## Step 7: Brief Admin

Duduk bareng admin (15-30 menit). Demo:
1. Cara buat event baru
2. Cara share link submit ke peserta
3. Cara review + approve/reject submission
4. Cara buka voting
5. Cara tutup voting → lihat hasil
6. Apa yang dilakukan kalau ada error (hubungi siapa)

**Buat cheat sheet 1 halaman untuk admin:**
```
CHEAT SHEET ADMIN — Inready VOTES
==================================
Login: https://inready.id/vote/login

SEBELUM EVENT:
1. Buat event → /vote/admin/events → + Buat Event
2. Isi judul, deskripsi → Simpan
3. Ubah status ke "Buka Submission"
4. Copy link submit → share ke peserta

SAAT PESERTA SUBMIT:
5. Cek submissions → Approve/Reject satu-satu

SAAT EVENT VOTING:
6. Ubah status ke "Buka Voting"
7. Share link gallery ke anggota: https://inready.id/vote/event/[slug]
8. Anggota login → vote

SETELAH VOTING:
9. Ubah status ke "Tutup Voting"
10. Hasil otomatis tampil di: https://inready.id/vote/event/[slug]/hasil

KALAU ADA MASALAH:
Hubungi [nama developer] di [nomor/chat]
```

---

## Step 8: README Minimal

Buat `docs/VOTING-README.md` di repo:
```markdown
# Inready VOTES — Voting On Talent Excellence & Showcase

## Quick Start (Development)
1. Clone repo
2. `composer install && npm install`
3. `cp .env.example .env` → isi DB credentials
4. `php artisan key:generate`
5. `php artisan migrate`
6. `php artisan db:seed --class=VotingSeeder`
7. `php artisan storage:link`
8. `php artisan serve` → buka `localhost:8000/vote/`

## Akun Test
- Admin: admin@inready.id / password
- Member: member1@inready.id sampai member5@inready.id / password

## Deploy ke Production
1. `git pull origin main`
2. `composer install --no-dev --optimize-autoloader`
3. `php artisan migrate --force`
4. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
5. `sudo systemctl restart php8.3-fpm`

## Struktur
- Routes: `routes/voting.php`
- Controllers: `app/Http/Controllers/Voting/`
- Views: `resources/views/voting/`
- Models: VotingEvent, Submission, SubmissionScreenshot, Vote

## Stack
- Backend: Laravel 11 (Blade + Alpine.js) — BUKAN React/Inertia
- CSS: Tailwind (CDN dev / build prod)
- DB: MySQL 8 (server terpisah)
```

---

## ✅ CHECKLIST FINAL — SIAP EVENT

### Deployment
- [ ] Code deployed ke production server
- [ ] Migrations berhasil jalan
- [ ] Storage link aktif, folder upload ada dan writable
- [ ] Cache (config, route, view) generated
- [ ] PHP-FPM restarted
- [ ] APP_DEBUG = false di production

### Server
- [ ] Upload limit cukup (php.ini + nginx)
- [ ] Disk space cukup untuk upload gambar (cek `df -h`)
- [ ] Koneksi DB remote stabil (test: `php artisan tinker` → `DB::select('SELECT 1')`)
- [ ] SSL aktif (https)

### Smoke Test Production
- [ ] Landing page OK
- [ ] Company profile masih OK
- [ ] Full flow: buat event → submit → approve → vote → tutup → hasil — semua OK
- [ ] Upload gambar di production OK
- [ ] Mobile responsive di production OK

### Internal Testing
- [ ] Minimal 5 orang sudah test
- [ ] Semua blocker bug sudah fix
- [ ] Major bugs sudah fix
- [ ] Bug list documented

### Admin Readiness
- [ ] Admin sudah di-brief cara pakai
- [ ] Admin cheat sheet sudah dibagikan
- [ ] Admin sudah test sendiri: buat event → full flow
- [ ] Emergency contact (developer) sudah jelas

### Documentation
- [ ] README minimal ada (cara run, cara deploy, akun test)
- [ ] Admin guide / cheat sheet ada

### Plan B
- [ ] Google Form sudah disiapkan sebagai fallback
- [ ] Tahu cara matikan company profile sementara jika server kewalahan
- [ ] Tahu cara rollback jika ada critical bug hari event

---

## HARI EVENT — Monitoring

```bash
# Jalankan di terminal terpisah:

# 1. Monitor RAM (refresh tiap 5 detik)
watch -n 5 'free -m'

# 2. Monitor error log
tail -f storage/logs/laravel.log

# 3. Monitor access log (lihat traffic)
tail -f /var/log/nginx/access.log

# 4. Cek PHP-FPM workers
ps aux | grep php-fpm | wc -l
```

**Jika server lambat:**
- Cek RAM → jika < 100MB free, ada masalah
- Cek PHP-FPM workers → jika semua busy, naikkan pm.max_children atau kurangi beban
- Opsi darurat: matikan company profile sementara (disable route di nginx)

**Jika ada bug kritis:**
- Fix langsung di production (git commit + push + pull)
- Atau: aktifkan Plan B (Google Form), umumkan ke peserta

---

**Selesai. Kamu sudah punya voting system yang live dan siap dipakai.**

*Good luck untuk event pertama. Ini produk pertama yang berhasil di-ship Inready sejak 2014. Itu sudah sebuah pencapaian.*
