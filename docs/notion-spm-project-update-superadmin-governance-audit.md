# SPM Project Update - Super Admin Governance Audit

Tanggal dokumentasi: 2026-06-12
Repo: https://github.com/alanramadhani2112/spm
Branch lokal: `main`
Remote: `origin/main`

## Ringkasan

Project SPM sedang masuk fase stabilisasi dan governance untuk area Super Admin. Update terakhir di remote adalah commit `f1bfa4e` dengan judul `Add Super Admin operational board`, dibuat pada 2026-06-12 03:23 WIB. Setelah commit tersebut, working tree lokal menambahkan permission enforcement foundation untuk aksi sensitif Super Admin.

Commit remote terakhir mengeksekusi rekomendasi P1 dari audit coverage Super Admin: menambahkan operational board untuk bottleneck lintas status, SLA breach, workload asesor, dan antrian paling mendesak. Update lokal terbaru mengeksekusi rekomendasi P0 berikutnya: middleware permission granular untuk beberapa aksi sensitif.

## Commit Terbaru

- Commit: `f1bfa4e31bdcb4bc161ab732a99067fef904424f`
- Judul: `Add Super Admin operational board`
- Link: https://github.com/alanramadhani2112/spm/commit/f1bfa4e31bdcb4bc161ab732a99067fef904424f
- Author: Alan Ramadhani
- Co-author: Claude

## Perubahan Utama

### Operational Board Super Admin

File utama:

- `app/Http/Controllers/SuperAdmin/DashboardController.php`
- `resources/views/superadmin/dashboard/index.blade.php`
- `tests/Feature/SuperAdmin/DashboardExportTest.php`

Perubahan:

- Menambahkan board antrian lintas status untuk Review Awal, Tahap 1, Assign Asesor, Tahap 2, Visitasi, Scoring, Validasi Akhir, SK, dan Banding.
- Menambahkan panel SLA breach berbasis deadline di `SuperAdminSettings`.
- Menambahkan daftar antrian paling mendesak berdasarkan umur status.
- Menambahkan ringkasan workload asesor aktif, termasuk pembagian ketua dan anggota.
- Menambahkan link cepat dari board ke workflow console terfilter.

### Permission Enforcement Foundation

File utama:

- `app/Http/Middleware/EnsureUserHasPermission.php`
- `app/Models/User.php`
- `bootstrap/app.php`
- `routes/web.php`
- `database/migrations/2026_06_12_000008_add_sensitive_superadmin_permissions.php`
- `database/seeders/PermissionSeeder.php`

Perubahan:

- Menambahkan helper `User::hasPermission()`.
- Menambahkan middleware route `permission`.
- Menambahkan permission sensitif: `settings.update`, `role.permissions.update`, `user.access.update`, `akreditasi.final.approve`, dan `sk.publish`.
- Menambahkan migrasi agar database existing ikut mendapat permission baru dan role Admin/Super Admin tetap memiliki akses awal.
- Memasang middleware permission pada settings update, role permission update, user role/status update, final approval, dan publish SK.
- Menambahkan forbidden-path tests saat permission spesifik dicabut dari role Super Admin.

### Audit Trail Super Admin

File utama:

- `app/Http/Controllers/SuperAdmin/MasterDataController.php`
- `app/Services/AuditTrailService.php`
- `app/Models/AkreditasiAuditLog.php`
- `database/migrations/2026_06_11_000007_allow_global_superadmin_audit_logs.php`

Perubahan:

- Menambahkan dependency `AuditTrailService` ke `MasterDataController`.
- Mencatat audit untuk CRUD komponen EDPM.
- Mencatat audit untuk CRUD butir EDPM.
- Mencatat audit untuk create, update, toggle, dan delete kategori dokumen.
- Mencatat audit untuk update permission role, termasuk old/new permission IDs, added IDs, dan removed IDs.
- Mencatat audit untuk invite/pre-register user.
- Mencatat audit untuk perubahan role/status user.
- Menambahkan label action type baru di `AkreditasiAuditLog`.

### Reason Field untuk Aksi Sensitif

Aksi berikut sekarang membutuhkan alasan perubahan:

- Update permission role.
- Update role/status user.

Alasan disimpan ke audit log melalui field `reason`.

### Audit Log Global

`AuditTrailService::log()` sekarang menerima `?int $akreditasiId`, sehingga audit log tidak harus selalu terkait ke satu pengajuan akreditasi.

Migration baru membuat `akreditasi_id` nullable dan mengubah foreign key menjadi `nullOnDelete`.

### Muhammadiyah ID SSO

File utama:

- `app/Http/Controllers/Auth/MuhammadiyahIdController.php`
- `app/Services/MuhammadiyahIdService.php`
- `docs/muhammadiyah-id-sso.md`

Perubahan terakhir menambahkan audit ketika user pertama kali ditautkan ke SSO Muhammadiyah ID melalui action `sso_user_linked`.

SSO sudah dalam mode skeleton/ready environment, tetapi live integration masih menunggu credential OAuth resmi.

## Hasil Verifikasi Lokal

Perintah yang dijalankan:

```bash
php artisan test --filter=SuperAdmin\\MasterDataTest
php artisan test --filter=MuhammadiyahIdSsoTest
php artisan test
```

Hasil:

- `SuperAdmin\\MasterDataTest`: passed, 10 tests, 53 assertions.
- `MuhammadiyahIdSsoTest`: passed, 4 tests, 24 assertions.
- Full test suite: passed, 98 tests, 288 assertions.
- `SuperAdmin\\DashboardExportTest`: passed, 2 tests, 16 assertions.
- `SuperAdmin`: passed, 32 tests, 120 assertions.
- `SuperAdmin`: passed, 37 tests, 125 assertions setelah permission enforcement.
- Full test suite terbaru: passed, 108 tests, 309 assertions.

## Status Working Tree

Status lokal:

- `main` sejajar dengan `origin/main`.
- Ada folder untracked `.agents/`.

Isi `.agents/`:

- `.agents/skills/laravel-development/SKILL.md`
- `.agents/skills/ui-ux-pro-max/SKILL.md`
- `.agents/skills/ui-ux-pro-max/data`
- `.agents/skills/ui-ux-pro-max/scripts`

Catatan: folder `.agents/` adalah konteks lokal, belum termasuk commit remote.

## Konteks Pengerjaan Sebelumnya

Beberapa commit terakhir:

- `f1bfa4e` - Add Super Admin operational board.
- `55ffdb6` - Align Super Admin settings enforcement.
- `4a2b1be` - Audit Super Admin governance actions.
- `c231b56` - Document Super Admin flow coverage.
- `309d632` - Prepare Muhammadiyah ID SSO environment.

Dokumen audit utama:

- `docs/superadmin-flow-coverage.md`
- `docs/muhammadiyah-id-sso.md`

## Gap yang Masih Tercatat

### Settings Enforcement Coverage

Settings key alignment sudah dirapikan melalui `SuperAdminSettings`:

- `AkreditasiWorkflowService` memakai `MAX_SIKLUS_TAHAP1` dan `MAX_SIKLUS_TAHAP2`.
- `DeadlineService` memakai `deadlineKeyForPhase()` untuk mapping phase ke key UI.
- Operational board memakai mapping deadline yang sama untuk SLA breach.

Gap tersisa adalah memperluas test enforcement untuk document requirement, NV override/reason mode, banding eligibility, dan action on correction limit.

### Permission Enforcement

Role dan permission matrix sudah tersedia. Enforcement utama masih berbasis role middleware untuk akses area besar, tetapi foundation permission granular sudah aktif pada aksi sensitif awal.

Sudah ada enforcement permission granular untuk:

- `permission:settings.update`
- `permission:role.permissions.update`
- `permission:user.access.update`
- `permission:akreditasi.final.approve`
- `permission:sk.publish`

Gap tersisa:

- Perlu ekspansi bertahap ke aksi workflow sensitif lain.
- Perlu kebijakan permission untuk export activity, destructive master data, dan SSO management ketika fitur reset/unlink tersedia.

### Operational Board

Operational board sudah tersedia di dashboard Super Admin. Gap lanjutan:

- Tambahkan export/report khusus workload asesor.
- Tambahkan warning overload saat assign asesor.
- Tambahkan notification center untuk overdue, banding, SK pending, dan failed notification.

## Rekomendasi Urutan Lanjut

### P0 - Stabilization / Governance

1. Tambahkan enforcement tests untuk setting yang belum terbukti penuh.
2. Tambahkan audit coverage tambahan untuk SSO failure/unlink/reset bila fitur tersedia.
3. Perluas permission enforcement ke aksi workflow/destructive berikutnya.

### P1 - Operational Control

1. Asesor workload dan assignment intelligence lanjutan.
2. Notification center.
3. Export/report operasional.

### P2 - Data dan Reporting

1. User detail dan SSO management.
2. Data Pesantren control.
3. Reporting/export suite.

## Next Best Task

Task berikut yang paling masuk akal adalah memperluas settings enforcement tests.

Target implementasi:

- Tambahkan tests untuk document requirement.
- Tambahkan tests untuk NV override dan reason mode.
- Tambahkan tests untuk banding eligibility.
- Tambahkan tests untuk action on correction limit.

## Notion Import Notes

Halaman ini disiapkan sebagai Markdown agar bisa langsung di-import ke Notion.

Judul rekomendasi halaman Notion:

`SPM Project Update - Super Admin Governance Audit`

Tag rekomendasi:

- `SPM`
- `Laravel`
- `Super Admin`
- `Governance`
- `Audit Trail`
- `Muhammadiyah ID SSO`

Status rekomendasi:

`Ready for next implementation`
