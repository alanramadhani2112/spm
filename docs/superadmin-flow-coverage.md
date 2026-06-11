# Super Admin Flow Coverage Audit

Tanggal audit: 2026-06-11  
Baseline branch: `main`  
Tujuan: memastikan Super Admin menjadi pusat kendali seluruh proses bisnis akreditasi sebelum pengembangan fitur lanjutan.

## Ringkasan Eksekutif

Super Admin saat ini sudah memiliki coverage besar atas workflow akreditasi end-to-end, master data, settings, audit log, user/role management, dan skeleton SSO Muhammadiyah ID. Dari sisi route, Super Admin sudah bisa masuk ke hampir semua aksi lintas role: Pesantren, Admin, Ketua Asesor, Anggota Asesor, hingga validasi akhir dan banding.

Gap utama bukan lagi akses route dasar, tetapi governance dan operasional:

1. Audit log belum mencakup semua aksi non-akreditasi seperti user invite, role permission update, master data update, dan perubahan kategori dokumen.
2. Permission matrix sudah ada, tetapi belum menjadi enforcement layer utama di route/action; akses masih dominan berbasis role.
3. Settings sudah punya UI dan sebagian dipakai service, tetapi masih ada mismatch key dan belum semua setting terbukti enforce di flow.
4. Super Admin belum punya operational board untuk bottleneck, overdue lintas tahap, workload asesor, pending SK, dan pending banding.
5. User lifecycle SSO sudah disiapkan, tetapi live integration menunggu credential dan belum ada reset/unlink/detail SSO.
6. Beberapa flow memakai view milik Admin/Asesor dengan route override; fungsional, tetapi perlu QA UI konsistensi dan wording Super Admin.

## Coverage Matrix Utama

Legend:

- **Done**: route/controller/view/test utama sudah ada dan bisa dipakai.
- **Partial**: fitur ada tetapi masih ada gap governance, audit, enforcement, UX, atau test detail.
- **Missing**: belum tersedia sebagai fitur Super Admin yang jelas.

| Area | Capability | Route / Entry Point | Controller | View | Tests | Status | Gap / Catatan |
|---|---|---|---|---|---|---|---|
| Dashboard | Ringkasan nasional akreditasi | `superadmin.dashboard` | `DashboardController@index` | `superadmin.dashboard.index` | `DashboardExportTest` | Done | Perlu operational drill-down lebih kaya. |
| Dashboard | Export CSV dashboard | `superadmin.dashboard.export` | `DashboardController@export` | N/A | `DashboardExportTest` | Done | Export masih ringkasan dashboard, belum laporan lengkap per domain. |
| Akreditasi Console | List semua akreditasi | `superadmin.akreditasi.index` | `AkreditasiController@index` | `superadmin.akreditasi.index` | `AkreditasiConsoleTest` | Done | Perlu board bottleneck/SLA. |
| Akreditasi Console | Export console CSV | `superadmin.akreditasi.export` | `AkreditasiController@export` | N/A | `AkreditasiConsoleTest` | Done | Perlu export tambahan: nilai, asesor workload, audit, dokumen. |
| Akreditasi Detail | Detail pengajuan lengkap | `superadmin.akreditasi.show` | `AkreditasiController@show` | `superadmin.akreditasi.show` | `AkreditasiConsoleTest` | Done | Perlu action audit completeness dan UI QA semua tab. |
| Pengajuan | Buat pengajuan untuk pesantren | `superadmin.akreditasi.pengajuan`, `submit-pengajuan` | `pengajuanForm`, `submitPengajuan` | `superadmin.akreditasi.pengajuan` | `AkreditasiConsoleTest` | Done | Perlu validasi duplicate/eligibility lebih jelas di UI. |
| Review Awal | Terima/tolak pengajuan | `review-awal`, `terima-pengajuan`, `tolak-pengajuan` | `reviewAwal`, `terimaPengajuan`, `tolakPengajuan` | `admin.akreditasi.review-awal` dengan route Super Admin | `AkreditasiConsoleTest` | Done | View masih share Admin; pastikan copy dan affordance Super Admin konsisten. |
| Assessment | Buka assessment + deadline | `buka-assessment` | `bukaAssessment` | `admin.akreditasi.buka-assessment` | Partial coverage | Partial | Method mengisi `assessment_deadline`, tetapi tidak memakai default setting `assessment_deadline` otomatis. |
| Assessment | Upload kartu kendali | `upload-kk` | `uploadKartuKendali` | N/A action endpoint | Indirect via workflow tests | Partial | Perlu form/CTA eksplisit di detail jika Super Admin acting as Pesantren; validasi file perlu test khusus. |
| Review Tahap 1 | Review, minta perbaikan, approve | `review-tahap1`, `minta-perbaikan-tahap1`, `approve-tahap1` | `reviewTahap1`, `mintaPerbaikanTahap1`, `approveTahap1` | `admin.akreditasi.review-tahap1` | Partial coverage | Done | Perlu audit UX untuk sections dan reason required/optional. |
| Koreksi Tahap 1 Limit | Keputusan saat batas koreksi | `handle-limit-review` | `handleLimitReview` | Action endpoint | Indirect | Partial | Setting key mismatch perlu diperbaiki: settings pakai `max_siklus_tahap1`, service mencari `max_stage_1_correction_cycles`. |
| Asesor Assignment | Assign asesor | `assign-asesor` | `assignAsesor` | `admin.akreditasi.assign-asesor` | Workflow route tests | Done | Perlu workload visibility sebelum assign. |
| Asesor Assignment | Reassign asesor | `reassign-asesor` | `reassignAsesor` | `admin.akreditasi.reassign-asesor` | Partial coverage | Done | Perlu audit reason tersimpan dan riwayat reassignment terlihat. |
| Review Tahap 2 | Ketua Asesor review | `review-tahap2`, `layak-visitasi`, `minta-perbaikan-tahap2` | `reviewTahap2`, `nyatakanLayakVisitasi`, `mintaPerbaikanTahap2` | `asesor.ketua.review-tahap2` | `AkreditasiConsoleTest` | Done | Shared asesor view; perlu wording Super Admin/acting as. |
| Visitasi | Jadwalkan visitasi | `jadwalkan-visitasi` | `jadwalkanVisitasi` | `asesor.ketua.jadwalkan-visitasi` | Route tests | Done | Perlu calendar/list schedule overview. |
| Visitasi | Tandai visitasi selesai | `tandai-visitasi-selesai` | `tandaiVisitasiSelesai` | Action endpoint | Indirect | Done | Perlu confirmation/audit visibility di detail. |
| Scoring | Input NA1 | `input-na1` | `inputNA1` | `asesor.ketua.input-na1` | Route-aware tests | Done | Perlu guard agar Super Admin sadar sedang override/acting as asesor. |
| Scoring | Input NA2 | `input-na2` | `inputNA2` | `asesor.anggota.input-na2` | Route-aware tests | Done | Perlu audit label actor Super Admin. |
| Scoring | Input NK | `input-nk` | `inputNK` | `asesor.ketua.input-nk` | Route-aware tests | Done | Perlu validation completeness test. |
| Laporan Visitasi | Upload laporan individu/kelompok | `upload-laporan` | `uploadLaporan` | `asesor.ketua.upload-laporan` | Route-aware tests | Done | Perlu document-category rule integration. |
| Laporan Visitasi | Submit hasil visitasi | `submit-hasil-visitasi` | `submitHasilVisitasi` | Action endpoint | Indirect | Done | Perlu CTA state visibility di detail. |
| Validasi Akhir | Validasi akhir | `validasi-akhir`, `approve-final`, `tolak-final` | `validasiAkhir`, `approveFinal`, `tolakFinal` | `admin.akreditasi.validasi-akhir` | Partial | Done | Setting `nv_override_allowed` belum jelas enforce; reason mode belum lengkap. |
| SK | Terbitkan SK | `terbitkan-sk` | `terbitkanSK` | shared admin flow/action | Indirect | Done | Perlu template/nomor SK management dan export/download SK. |
| Banding | Lihat, terima, tolak banding | `banding`, `superadmin.banding.terima`, `superadmin.banding.tolak` | `banding`, `terimaBanding`, `tolakBanding` | `admin.akreditasi.banding` | `AkreditasiConsoleTest` | Done | Setting `banding_eligibility` perlu enforcement eksplisit. |
| Master Data | Dashboard master data | `superadmin.master-data.index` | `MasterDataController@index` | `superadmin.master-data.index` | `MasterDataTest` | Done | Good. |
| Master Data EDPM | CRUD komponen & butir | `master-data.edpm.*` | `edpm`, `store/update/destroy Komponen/Butir` | `superadmin.master-data.edpm.index` | `MasterDataTest` | Done | Perlu audit log perubahan master instrumen. |
| Document Categories | CRUD/toggle kategori dokumen | `master-data.document-categories.*` | `documentCategories`, `store/update/toggle/destroy` | `superadmin.master-data.document-categories.index` | `MasterDataTest` | Done | Perlu audit log; rules belum sepenuhnya terhubung ke workflow upload/visibility. |
| Role & Permission | Matrix read-only + modal edit | `master-data.roles.index`, `roles.permissions.update` | `roles`, `updateRolePermissions` | `superadmin.master-data.roles.index` | `MasterDataTest` | Partial | UI aman sudah ada; permission belum menjadi enforcement route/action. Perlu audit log diff permission. |
| User Management | List/filter user | `master-data.users.index` | `users` | `superadmin.master-data.users.index` | `MasterDataTest` | Done | Perlu detail user page. |
| User Management | Invite/pre-register SSO user | `master-data.users.store` | `storeUser` | Modal users page | `MasterDataTest` | Done | Perlu email invite, resend invite, bulk import. |
| User Management | Edit role/status | `master-data.users.update` | `updateUser` | Modal users page | `MasterDataTest` | Partial | Perlu audit log, protection untuk role Super Admin, dan reason perubahan. |
| SSO | Muhammadiyah ID env + skeleton | `/auth/muhammadiyah/*` | `Auth\MuhammadiyahIdController` | Login button | `MuhammadiyahIdSsoTest` | Partial | Live credential belum ada; unlink/reset SSO belum ada. |
| Settings | Settings dashboard | `superadmin.settings.index` | `SettingsController@index` | `superadmin.settings.index` | `SettingsTest` | Done | Good UI, but enforcement coverage perlu audit. |
| Settings | Deadline, correction, dokumen, NV, notifikasi, banding | `superadmin.settings.*` | `deadline/correction/dokumen/nv/notifikasi/banding/update` | settings pages | `SettingsTest` | Partial | Beberapa key tidak sinkron dengan service; sebagian settings belum enforce penuh. |
| Audit Log | List/detail audit trail | `superadmin.audit.index/show` | `AuditController@index/show` | `superadmin.audit.*` | `SettingsTest` smoke | Partial | Audit data mostly workflow; belum semua aksi Super Admin non-workflow tercatat. |
| Notification Center | Inbox/pusat notifikasi | N/A | N/A | N/A | N/A | Missing | Setting notifikasi ada, tapi UI pusat notifikasi Super Admin belum ada. |
| Operational Board | Bottleneck/SLA/workload board | N/A | N/A | N/A | N/A | Missing | Prioritas tinggi untuk memastikan proses bisnis terpantau. |
| Data Pesantren Control | Super Admin manage profil/data pesantren | Partial via detail akreditasi | `AkreditasiController@show` | detail tab | Partial | Partial | Belum ada dedicated Super Admin page untuk edit/lock/unlock data pesantren. |

## Route Coverage Super Admin

Route Super Admin berada di `routes/web.php` dengan middleware:

```php
Route::middleware(['auth', 'role:super_admin'])->prefix('superadmin')->name('superadmin.')->group(...)
```

Total area route:

1. Dashboard: `superadmin.dashboard`, `superadmin.dashboard.export`.
2. Akreditasi workflow: index, export, pengajuan, detail, review awal, assessment, review tahap 1, assign/reassign asesor, review tahap 2, visitasi, scoring, laporan, validasi akhir, SK, banding.
3. Master data: EDPM, document categories, roles, users.
4. Settings: index, update, deadline, correction, dokumen, NV, notifikasi, banding.
5. Audit: index, show.

Kesimpulan route: coverage route end-to-end sudah luas dan cukup lengkap.

## Test Coverage Super Admin

Test yang ada:

- `tests/Feature/SuperAdmin/DashboardExportTest.php`
- `tests/Feature/SuperAdmin/AkreditasiConsoleTest.php`
- `tests/Feature/SuperAdmin/MasterDataTest.php`
- `tests/Feature/SuperAdmin/SettingsTest.php`
- `tests/Feature/MuhammadiyahIdSsoTest.php`

Coverage yang kuat:

- Dashboard render/export.
- Akreditasi console render/detail/export.
- Pengajuan oleh Super Admin.
- Banding route/action Super Admin.
- Route-aware shared views agar form submit ke route Super Admin.
- Master data CRUD dasar.
- User/role management UI dan update dasar.
- Settings smoke/update.
- SSO mocked redirect/callback.

Gap test:

1. Belum ada test granular untuk semua action Super Admin end-to-end per status.
2. Belum ada test audit log untuk user/role/master/settings changes selain settings.
3. Belum ada test permission enforcement berdasarkan permission matrix.
4. Belum ada test settings enforcement untuk deadline/koreksi/dokumen/NV/banding.
5. Belum ada test SSO live contract; wajar karena credential belum approved.

## Audit Logging Coverage

Sudah tercatat:

- Banyak transisi akreditasi melalui `AkreditasiWorkflowService` menggunakan `AuditTrailService`.
- Banding submit/process menggunakan `BandingService`.
- Settings update menggunakan `SettingsController@update` dengan `setting_changed`.

Belum/kurang tercatat:

- `MasterDataController@storeKomponen`, `updateKomponen`, `destroyKomponen`.
- `storeButir`, `updateButir`, `destroyButir`.
- Document category store/update/toggle/destroy.
- Role permission update dengan diff old/new.
- User invite/pre-register.
- User role/status update dengan reason.
- SSO login/link/unlink/failure.
- CSV exports.

Rekomendasi: buat audit layer non-akreditasi sebelum menambah fitur besar lain.

## Settings Enforcement Audit

Settings UI sudah punya kategori:

- Deadline: `review_awal_deadline`, `assessment_deadline`, `review_tahap1_deadline`, `correction_tahap1_deadline`, `review_tahap2_deadline`, `correction_tahap2_deadline`, `scoring_deadline`, `banding_deadline`.
- Correction: `max_siklus_tahap1`, `max_siklus_tahap2`, `action_on_limit`.
- Document: `kartu_kendali_wajib_before`, `laporan_wajib_before`.
- NV: `nv_override_allowed`, `nv_reason_mode`.
- Notification: `superadmin_receives_admin_notif`, `reminder_days`.
- Banding: `banding_eligibility`.

Temuan penting:

- `AkreditasiWorkflowService` mencari `max_stage_1_correction_cycles` dan `max_stage_2_correction_cycles`, sedangkan Settings UI menyimpan `max_siklus_tahap1` dan `max_siklus_tahap2`. Ini kemungkinan mismatch yang harus diprioritaskan.
- `DeadlineService` memakai pola key `deadline_{phase}`, sedangkan Settings UI memakai key seperti `review_awal_deadline`. Perlu mapping terpusat.
- `BandingService` memakai `banding_deadline`, ini sesuai kategori settings.
- `nv_override_allowed` dan `nv_reason_mode` belum terlihat jelas sebagai gate di validasi akhir.
- Document settings dan document-category rules perlu dipastikan benar-benar dipakai di upload/visibility workflow.

Rekomendasi: sebelum operational board, lakukan refactor kecil untuk centralize settings key mapping dan test enforcement.

## Permission Enforcement Audit

Saat ini:

- Role dan Permission model serta pivot sudah ada.
- Super Admin UI untuk permission matrix sudah ada dan aman.
- Namun route protection utama masih berbasis role middleware, contoh: `role:super_admin`, `role:admin`, `role:asesor`, `role:pesantren`.

Gap:

- Belum ada middleware permission seperti `permission:akreditasi.review_awal`.
- Belum ada helper authorization di controller/action berdasarkan permission matrix.
- Belum ada tests yang membuktikan mencabut permission benar-benar memblokir route/action.

Rekomendasi: setelah audit log non-akreditasi, implement permission enforcement secara bertahap mulai dari action paling sensitif.

## UI/UX Coverage

Sudah dipolish:

- Super Admin dashboard.
- Akreditasi console/detail.
- Audit center.
- Settings forms.
- Master data center.
- Document categories.
- Role & Permission safe edit modal.
- User management + SSO pre-registration.

Partial:

- Beberapa action flow Super Admin masih memakai view Admin/Asesor/Pesantren dengan route override. Ini efisien dan fungsional, tetapi perlu review UX agar pengguna sadar sedang bekerja sebagai Super Admin.
- Action endpoints tanpa halaman tersendiri perlu dipastikan punya CTA/confirmation yang jelas di Action Center.

Missing:

- Dedicated operational board.
- Notification center.
- User detail/SSO detail page.
- Asesor workload page.
- Data Pesantren control page.

## Recommended Next Implementation Order

### P0 — Stabilization / Governance

1. **Audit log non-akreditasi Super Admin**
   - Log user invite, update user role/status, role permission diff, master EDPM CRUD, document category changes, settings change detail, SSO link/login failure.
   - Tambahkan reason field untuk aksi sensitif: role update, status update, permission update.

2. **Settings key alignment + enforcement tests**
   - Buat central settings service atau constants agar UI key sama dengan service key.
   - Fix mismatch correction cycle keys.
   - Tambah tests untuk max correction cycles, banding deadline, NV override, document requirements.

3. **Permission enforcement foundation**
   - Tambahkan middleware/helper permission.
   - Terapkan pada beberapa action sensitif dulu: settings update, role permissions update, user update, final approval, SK publish.
   - Tambah tests permission denied.

### P1 — Operational Control

4. **Super Admin Operational Board**
   - Board pending action: review awal, tahap 1, assessor assignment, visitasi, scoring, validasi akhir, SK, banding.
   - Board overdue/SLA breach berdasarkan deadline.
   - Bottleneck count per status.
   - Link cepat ke filtered workflow console.

5. **Asesor Workload & Assignment Intelligence**
   - Lihat beban asesor aktif.
   - Jumlah assignment ketua/anggota.
   - Assignment overdue.
   - Warning saat assign asesor overload.

6. **Notification Center**
   - Inbox Super Admin.
   - Notifikasi deadline, overdue, banding, SK pending, failed notifications.
   - Mark as read dan filter.

### P2 — Data & Reporting

7. **User detail + SSO management**
   - Detail user, SSO profile, last login.
   - Reset/unlink SSO.
   - Resend invite.
   - Bulk import pre-registration.

8. **Data Pesantren Control**
   - Dedicated list/detail pesantren.
   - Kelengkapan profil/data/dokumen.
   - Lock/unlock data.
   - Edit/override data with audit.

9. **Reporting/export suite**
   - Export audit log.
   - Export users/roles.
   - Export nilai/peringkat.
   - Export asesor workload.
   - Export dokumen status.

## Definition of Done untuk Super Admin

Super Admin bisa dianggap lengkap bila setiap flow penting memenuhi checklist ini:

- Route Super Admin tersedia.
- UI action tersedia dari dashboard/console/detail.
- Validasi input jelas.
- SweetAlert/confirmation untuk aksi sensitif.
- Audit log mencatat actor, action, old/new value, reason.
- Permission enforcement bukan hanya role check.
- Test feature mencakup success dan forbidden/error path.
- Empty state dan error feedback tersedia.
- Export/report tersedia untuk data operasional penting.

## Keputusan Teknis yang Direkomendasikan

1. Jangan menambah fitur besar sebelum audit logging non-akreditasi dan settings key alignment beres.
2. Pertahankan `main` sebagai baseline tunggal; buat branch baru per topik.
3. Untuk task berikutnya, gunakan branch baru: `feature/superadmin-governance-audit`.
4. Implementasi pertama yang paling bernilai: audit log non-akreditasi + reason field untuk role/user/permission changes.
