# SPM Project Update - Super Admin Governance Audit

Tanggal dokumentasi: 2026-06-12
Repo: https://github.com/alanramadhani2112/spm
Branch lokal: `main`
Remote: `origin/main`

## Ringkasan

Project SPM sedang masuk fase stabilisasi dan governance untuk area Super Admin. Update terakhir di remote adalah commit `23c26ec` dengan judul `Add Super Admin permission enforcement`, dibuat pada 2026-06-12. Setelah commit tersebut, working tree lokal menambahkan settings enforcement lanjutan.

Commit remote terakhir mengeksekusi rekomendasi P0 dari audit coverage Super Admin: middleware permission granular untuk beberapa aksi sensitif. Update lokal terbaru memperkuat enforcement settings untuk limit action, banding eligibility, dan NV override.

## Commit Terbaru

- Commit: `23c26ec`
- Judul: `Add Super Admin permission enforcement`
- Link: https://github.com/alanramadhani2112/spm/commit/23c26ec
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

### Settings Enforcement Lanjutan

File utama:

- `app/Services/AkreditasiWorkflowService.php`
- `app/Services/BandingService.php`
- `app/Support/SuperAdminSettings.php`
- `resources/views/superadmin/settings/banding.blade.php`
- `tests/Feature/SuperAdmin/SettingsTest.php`

Perubahan:

- `action_on_limit` menjadi default keputusan limit review bila action eksplisit tidak dikirim.
- `action_on_limit=auto_approve` dipetakan ke `approve_by_exception`.
- `action_on_limit=freeze` menghentikan default decision dengan error eksplisit.
- `banding_eligibility=disabled` memblokir pengajuan banding.
- `banding_deadline` dibaca melalui `SuperAdminSettings` tanpa cache ganda.
- `nv_override_allowed=0` memblokir override NV manual.
- UI settings banding menyediakan opsi aktif/nonaktif.

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
- `SuperAdmin\\SettingsTest`: passed, 15 tests, 21 assertions setelah settings enforcement lanjutan.
- `SuperAdmin`: passed, 41 tests, 132 assertions setelah settings enforcement lanjutan.
- Full test suite terbaru: passed, 112 tests, 316 assertions.

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

- `23c26ec` - Add Super Admin permission enforcement.
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

Settings key alignment dan sebagian enforcement sudah dirapikan melalui `SuperAdminSettings`:

- `AkreditasiWorkflowService` memakai `MAX_SIKLUS_TAHAP1` dan `MAX_SIKLUS_TAHAP2`.
- `DeadlineService` memakai `deadlineKeyForPhase()` untuk mapping phase ke key UI.
- Operational board memakai mapping deadline yang sama untuk SLA breach.
- `AkreditasiWorkflowService` memakai `ACTION_ON_LIMIT` untuk default limit decision.
- `BandingService` memakai `BANDING_ELIGIBILITY` dan `BANDING_DEADLINE`.
- `AkreditasiWorkflowService` memakai `NV_OVERRIDE_ALLOWED` untuk blokir override NV.

Gap tersisa adalah memperluas test enforcement untuk document requirement dan `nv_reason_mode`.

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

1. Tambahkan enforcement tests untuk document requirement dan `nv_reason_mode`.
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

Task berikut yang paling masuk akal adalah memperluas document requirement enforcement.

Target implementasi:

- Tambahkan tests untuk document requirement.
- Pastikan `kartu_kendali_wajib_before` dan `laporan_wajib_before` benar-benar memblokir workflow sesuai fase.
- Tambahkan tests untuk `nv_reason_mode`.

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
