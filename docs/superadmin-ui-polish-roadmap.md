# Super Admin UI Polish Roadmap

## Tujuan
Merapikan seluruh surface Super Admin agar terasa lebih clean, konsisten, mudah dipindai, dan lebih action-oriented tanpa redesign total. Fokus utama roadmap ini adalah memperbaiki hierarchy, kejelasan status, pola aksi tabel, empty state, dan command-center quality pada halaman operasional utama.

## Prinsip Global

1. Status harus selalu berpasangan dengan **apa langkah berikutnya**.
2. Setiap halaman hanya punya **satu primary action** yang benar-benar dominan.
3. Gunakan pola **summary dulu, detail setelahnya**.
4. Kurangi scan horizontal pada tabel operasional.
5. Detail page harus berfungsi sebagai **command center**, bukan dump data.
6. Empty state wajib punya recovery path.
7. Severity visual harus tegas: info, warning, urgent, success, archived.
8. Terminologi dan CTA harus konsisten di seluruh surface.

## Pola Aksi Tabel

### 1. Full dropdown action-menu
Dipakai untuk tabel yang sifatnya administratif / inspeksi data.

Contoh surface:
- Dashboard tables
- Workload Asesor
- SK Management
- Notification Center
- Audit Log
- Master Data / Referensi Sistem

Pola:
- 1 tombol kebab / dots vertical
- semua aksi di dropdown
- `Lihat Detail` ditempatkan paling atas
- destructive / secondary action dipisah separator bila perlu

### 2. Primary action + dropdown
Dipakai untuk tabel yang sifatnya operational queue dan perlu aksi cepat.

Contoh surface:
- Semua Akreditasi

Pola:
- 1 primary CTA inline
- 1 kebab menu untuk aksi lain
- primary action hanya untuk aksi paling penting pada row tersebut

## Sprint Plan

### Sprint 1 — Selesai
Surface:
1. Dashboard
2. Semua Akreditasi
3. Detail Akreditasi

Status:
- sudah dipoles
- sudah diverifikasi visual
- sudah lolos `tests/Feature/SuperAdmin`
- sudah dicommit

Commit terkait:
- `9e00a35` — Polish Super Admin sprint 1 surfaces

### Sprint 2 — Selesai
Surface:
1. Workload Asesor
2. Audit Log + detail
3. Notification Center

Status:
- sudah dipoles
- sudah diverifikasi visual
- sudah lolos `tests/Feature/SuperAdmin`
- sudah dicommit

Commit terkait:
- `63dc06d` — Polish Super Admin sprint 2 surfaces

Catatan penting:
- Pada visual review Sprint 2 ditemukan defect runtime di audit page karena `action_type` bisa null.
- Perbaikan dilakukan pada `app/Models/AkreditasiAuditLog.php` dengan membuat `getActionTypeLabel(?string $actionType)` dan default ke `status_changed`.

### Sprint 3 — Berjalan
Surface:
1. SK Management refinement — **selesai**
2. Pengajuan Baru — **berikutnya**
3. Referensi Sistem / Settings / Master Data — **belum dikerjakan**

Status SK Management:
- sudah dipoles sebagai command center penerbitan SK
- sudah punya hero dengan satu primary CTA: `Fokus Siap Terbit`
- summary cards sudah dibuat action-oriented: siap terbit, SK terbit, sertifikat belum lengkap, kedaluwarsa
- command-center callout sudah memprioritaskan ready queue / expired / missing certificate / stable state
- row table sudah memasangkan status dengan next step seperti `Terbitkan SK sekarang`, `Lengkapi sertifikat digital`, `Dokumen SK lengkap`
- detail Akreditasi card `Status SK` sudah diberi helper konsisten
- sudah diverifikasi visual via browser runtime
- sudah lolos targeted SK test dan full `tests/Feature/SuperAdmin`
- sudah dicommit dan dipush

Commit terkait:
- `1264ab6` — Refine Super Admin SK Management

Fokus Sprint 3 yang tersisa:
- menjadikan Pengajuan Baru lebih guided / wizard-like
- merapikan information architecture halaman referensi sistem dan settings

### Sprint 4 — Consistency Pass
1. Detail/action page consistency
2. Copy & terminology pass
3. Severity / semantic pass
4. Full regression project

## Rekomendasi Urutan Lanjutan

### Next session — lanjut Sprint 3: Pengajuan Baru
Mulai dari surface **Pengajuan Baru** karena SK Management sudah selesai.

Target polish:
1. Audit alur `Pengajuan Baru` saat ini.
2. Ubah framing dari form biasa menjadi guided flow / wizard-like.
3. Pastikan user tahu:
   - sedang memilih pesantren untuk apa
   - prasyarat data apa yang harus lengkap
   - konsekuensi setelah submit
   - next step setelah pengajuan dibuat
4. Terapkan prinsip satu primary action.
5. Tambahkan helper / warning / empty state bila tidak ada pesantren eligible.
6. Pastikan validation dan error state punya recovery path.
7. Jaga route, permission, dan workflow service existing tetap stabil.
8. Tambahkan/adjust feature assertions secukupnya.
9. Jalankan targeted test terkait pengajuan dan full `tests/Feature/SuperAdmin`.
10. Lakukan visual review browser sebelum commit.

File yang kemungkinan relevan:
- `app/Http/Controllers/SuperAdmin/AkreditasiController.php`
- `resources/views/superadmin/akreditasi/pengajuan.blade.php`
- `tests/Feature/SuperAdmin/AkreditasiConsoleTest.php`
- `resources/views/superadmin/akreditasi/index.blade.php` bila entry point perlu copy consistency

### Setelah Pengajuan Baru
Lanjut ke **Referensi Sistem / Settings / Master Data**.

Target polish:
1. Inventaris surface referensi/settings/master data Super Admin.
2. Kelompokkan informasi agar lebih mudah dipindai.
3. Terapkan pola full dropdown action-menu untuk tabel administratif.
4. Rapikan summary cards, filter chips, empty states, dan severity badges.
5. Pastikan copy/CTA konsisten dengan sprint sebelumnya.

### Setelah Sprint 3
Lanjut ke consistency pass lintas seluruh surface agar:
- CTA konsisten
- wording konsisten
- empty state konsisten
- severity visual konsisten
- detail pages terasa satu sistem

## Verification Checklist

### Setelah tiap surface
- jalankan targeted tests untuk surface terkait
- jalankan `php artisan test tests/Feature/SuperAdmin`
- lakukan visual review pada surface yang dipoles
- commit hanya jika visual + regression aman

### Untuk visual review
Cek:
- hierarchy halaman
- primary action visibility
- scanability tabel
- empty state
- consistency button action
- readability detail page
- severity badge / warning / overdue / pending

## File Referensi Utama
- `docs/superadmin-flow-coverage.md`
- `PRODUCT.md`
- `DESIGN.md`
- `docs/superadmin-ui-polish-roadmap.md`

## Cara Pakai Lagi Nanti
Saat ingin lanjut polish, panggil roadmap ini lalu pilih surface berikutnya.

Prompt pengingat untuk lanjut dari kondisi terbaru:

```text
Lanjutkan roadmap UI polish Super Admin dari docs/superadmin-ui-polish-roadmap.md, mulai Sprint 3 bagian Pengajuan Baru.
```

Atau untuk consistency pass setelah Sprint 3 selesai:

```text
Buka docs/superadmin-ui-polish-roadmap.md dan lanjutkan consistency pass lintas semua surface Super Admin.
```
