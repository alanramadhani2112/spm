# Super Admin Flow Coverage Audit

Tanggal audit: 2026-06-14
Baseline branch: `main`  
Tujuan: memastikan Super Admin menjadi pusat kendali seluruh proses bisnis akreditasi sebelum pengembangan fitur lanjutan.

## Ringkasan Eksekutif

Super Admin saat ini sudah memiliki coverage besar atas workflow akreditasi end-to-end, master data, settings, audit log, user/role management, dan skeleton SSO Muhammadiyah ID. Dari sisi route, Super Admin sudah bisa masuk ke hampir semua aksi lintas role: Pesantren, Admin, Ketua Asesor, Anggota Asesor, hingga validasi akhir dan banding.

Gap utama bukan lagi akses route dasar, tetapi governance dan operasional:

1. Permission matrix sudah punya enforcement pada aksi sensitif awal dan sebagian workflow utama; akses area besar tetap berbasis role.
2. Settings sudah punya UI, key alignment terpusat, dan enforcement untuk deadline, correction cycles, limit action, banding eligibility, NV override, document requirement, dan `nv_reason_mode`.
3. Audit log non-akreditasi sudah diperluas, dan audit export CSV sudah tersedia; SSO failure/unlink/reset masih belum lengkap.
4. Operational board dan Notification Center sudah tersedia; assignment warning lanjutan dan export operasional lintas domain masih perlu dikembangkan.
5. User lifecycle SSO sudah disiapkan, tetapi live integration menunggu credential dan belum ada reset/unlink/detail SSO.
6. Beberapa flow memakai view milik Admin/Asesor dengan route override; fungsional, tetapi perlu QA UI konsistensi dan wording Super Admin.

## Coverage Matrix Utama

Legend:

- **Done**: route/controller/view/test utama sudah ada dan bisa dipakai.
- **Partial**: fitur ada tetapi masih ada gap governance, audit, enforcement, UX, atau test detail.
- **Missing**: belum tersedia sebagai fitur Super Admin yang jelas.

| Area | Capability | Route / Entry Point | Controller | View | Tests | Status | Gap / Catatan |
|---|---|---|---|---|---|---|---|
| Dashboard | Ringkasan nasional akreditasi | `superadmin.dashboard` | `DashboardController@index` | `superadmin.dashboard.index` | `DashboardExportTest` | Done | Sudah dilengkapi operational board; export masih perlu diperluas. |
| Dashboard | Export CSV dashboard | `superadmin.dashboard.export` | `DashboardController@export` | N/A | `DashboardExportTest` | Done | Export masih ringkasan dashboard, belum laporan lengkap per domain. |
| Akreditasi Console | List semua akreditasi | `superadmin.akreditasi.index` | `AkreditasiController@index` | `superadmin.akreditasi.index` | `AkreditasiConsoleTest` | Done | Operational board sudah link ke console terfilter. |
| Akreditasi Console | Export console CSV | `superadmin.akreditasi.export` | `AkreditasiController@export` | N/A | `AkreditasiConsoleTest` | Done | Perlu export tambahan: nilai, asesor workload, audit, dokumen. |
| Akreditasi Detail | Detail pengajuan lengkap | `superadmin.akreditasi.show` | `AkreditasiController@show` | `superadmin.akreditasi.show` | `AkreditasiConsoleTest` | Done | Perlu action audit completeness dan UI QA semua tab. |
| Pengajuan | Buat pengajuan untuk pesantren | `superadmin.akreditasi.pengajuan`, `submit-pengajuan` | `pengajuanForm`, `submitPengajuan` | `superadmin.akreditasi.pengajuan` | `AkreditasiConsoleTest` | Done | Perlu validasi duplicate/eligibility lebih jelas di UI. |
| Review Awal | Terima/tolak pengajuan | `review-awal`, `terima-pengajuan`, `tolak-pengajuan` | `reviewAwal`, `terimaPengajuan`, `tolakPengajuan` | `admin.akreditasi.review-awal` dengan route Super Admin | `AkreditasiConsoleTest` | Done | Aksi terima/tolak sudah dilindungi `permission:akreditasi.review_awal`; view masih share Admin. |
| Assessment | Buka assessment + deadline | `buka-assessment` | `bukaAssessment` | `admin.akreditasi.buka-assessment` | Partial coverage | Partial | Aksi sudah dilindungi `permission:akreditasi.review_awal`; method mengisi `assessment_deadline`, tetapi tidak memakai default setting `assessment_deadline` otomatis. |
| Assessment | Upload kartu kendali | `upload-kk` | `uploadKartuKendali` | N/A action endpoint | `SettingsTest`, workflow tests | Partial | Requirement sudah enforce sesuai `kartu_kendali_wajib_before`; perlu form/CTA eksplisit di detail jika Super Admin acting as Pesantren. |
| Review Tahap 1 | Review, minta perbaikan, approve | `review-tahap1`, `minta-perbaikan-tahap1`, `approve-tahap1` | `reviewTahap1`, `mintaPerbaikanTahap1`, `approveTahap1` | `admin.akreditasi.review-tahap1` | `AkreditasiConsoleTest` | Done | Aksi keputusan sudah dilindungi `permission:akreditasi.stage1_review`; perlu audit UX untuk sections dan reason required/optional. |
| Koreksi Tahap 1 Limit | Keputusan saat batas koreksi | `handle-limit-review` | `handleLimitReview` | Action endpoint | `SettingsTest`, `AkreditasiConsoleTest` | Done | `action_on_limit` sudah mengontrol default decision dan action dilindungi `permission:akreditasi.stage1_review`. |
| Asesor Assignment | Assign asesor | `assign-asesor` | `assignAsesor` | `admin.akreditasi.assign-asesor` | `AkreditasiConsoleTest` | Done | Sudah dilindungi `permission:akreditasi.assign_asesor`; dashboard sudah menampilkan workload; perlu warning overload saat assign. |
| Asesor Assignment | Reassign asesor | `reassign-asesor` | `reassignAsesor` | `admin.akreditasi.reassign-asesor` | Partial coverage | Done | Sudah dilindungi `permission:akreditasi.assign_asesor`; perlu audit reason tersimpan dan riwayat reassignment terlihat. |
| Review Tahap 2 | Ketua Asesor review | `review-tahap2`, `layak-visitasi`, `minta-perbaikan-tahap2` | `reviewTahap2`, `nyatakanLayakVisitasi`, `mintaPerbaikanTahap2` | `asesor.ketua.review-tahap2` | `AkreditasiConsoleTest` | Done | Shared asesor view; perlu wording Super Admin/acting as. |
| Visitasi | Jadwalkan visitasi | `jadwalkan-visitasi` | `jadwalkanVisitasi` | `asesor.ketua.jadwalkan-visitasi` | Route tests | Done | Perlu calendar/list schedule overview. |
| Visitasi | Tandai visitasi selesai | `tandai-visitasi-selesai` | `tandaiVisitasiSelesai` | Action endpoint | Indirect | Done | Perlu confirmation/audit visibility di detail. |
| Scoring | Input NA1 | `input-na1` | `inputNA1` | `asesor.ketua.input-na1` | Route-aware tests | Done | Perlu guard agar Super Admin sadar sedang override/acting as asesor. |
| Scoring | Input NA2 | `input-na2` | `inputNA2` | `asesor.anggota.input-na2` | Route-aware tests | Done | Perlu audit label actor Super Admin. |
| Scoring | Input NK | `input-nk` | `inputNK` | `asesor.ketua.input-nk` | Route-aware tests | Done | Perlu validation completeness test. |
| Laporan Visitasi | Upload laporan individu/kelompok | `upload-laporan` | `uploadLaporan` | `asesor.ketua.upload-laporan` | Route-aware tests | Done | Perlu document-category rule integration. |
| Laporan Visitasi | Submit hasil visitasi | `submit-hasil-visitasi` | `submitHasilVisitasi` | Action endpoint | `SettingsTest` | Done | Requirement laporan sudah enforce sesuai `laporan_wajib_before`; perlu CTA state visibility di detail. |
| Validasi Akhir | Validasi akhir | `validasi-akhir`, `approve-final`, `tolak-final` | `validasiAkhir`, `approveFinal`, `tolakFinal` | `admin.akreditasi.validasi-akhir` | `SettingsTest` | Done | `approve-final` sudah dilindungi `permission:akreditasi.final.approve`; `nv_override_allowed` dan `nv_reason_mode` sudah enforce. |
| SK | Terbitkan SK | `terbitkan-sk` | `terbitkanSK` | shared admin flow/action | Indirect | Done | Sudah dilindungi `permission:sk.publish`; perlu template/nomor SK management dan export/download SK. |
| Banding | Lihat, terima, tolak banding | `banding`, `superadmin.banding.terima`, `superadmin.banding.tolak` | `banding`, `terimaBanding`, `tolakBanding` | `admin.akreditasi.banding` | `AkreditasiConsoleTest`, `SettingsTest` | Done | Terima/tolak sudah dilindungi `permission:akreditasi.proses_banding`; `banding_eligibility=disabled` sudah memblokir pengajuan banding. |
| Master Data | Dashboard master data | `superadmin.master-data.index` | `MasterDataController@index` | `superadmin.master-data.index` | `MasterDataTest` | Done | Good. |
| Master Data EDPM | CRUD komponen & butir | `master-data.edpm.*` | `edpm`, `store/update/destroy Komponen/Butir` | `superadmin.master-data.edpm.index` | `MasterDataTest` | Done | Perlu audit log perubahan master instrumen. |
| Document Categories | CRUD/toggle kategori dokumen | `master-data.document-categories.*` | `documentCategories`, `store/update/toggle/destroy` | `superadmin.master-data.document-categories.index` | `MasterDataTest` | Done | Perlu audit log; rules belum sepenuhnya terhubung ke workflow upload/visibility. |
| Data Pesantren Control | List readiness + lock/unlock profil pesantren | `master-data.pesantren.*` | `pesantren`, `togglePesantrenLock` | `superadmin.master-data.pesantren.index` | `MasterDataTest` | Done | Sudah ada readiness filter dan audit reason lock/unlock; edit/override detail data pesantren masih gap lanjutan. |
| Role & Permission | Matrix read-only + modal edit | `master-data.roles.index`, `roles.permissions.update` | `roles`, `updateRolePermissions` | `superadmin.master-data.roles.index` | `MasterDataTest` | Done | Update permission sudah dilindungi `permission:role.permissions.update` dan punya audit diff. |
| User Management | List/filter user | `master-data.users.index` | `users` | `superadmin.master-data.users.index` | `MasterDataTest` | Done | Perlu detail user page. |
| User Management | Invite/pre-register SSO user | `master-data.users.store` | `storeUser` | Modal users page | `MasterDataTest` | Done | Perlu email invite, resend invite, bulk import. |
| User Management | Edit role/status | `master-data.users.update` | `updateUser` | Modal users page | `MasterDataTest` | Partial | Sudah dilindungi `permission:user.access.update` dan reason perubahan; perlu protection lebih eksplisit untuk role Super Admin. |
| SSO | Muhammadiyah ID env + skeleton | `/auth/muhammadiyah/*` | `Auth\MuhammadiyahIdController` | Login button | `MuhammadiyahIdSsoTest` | Partial | Live credential belum ada; unlink/reset SSO belum ada. |
| Settings | Settings dashboard | `superadmin.settings.index` | `SettingsController@index` | `superadmin.settings.index` | `SettingsTest` | Done | Good UI, but enforcement coverage perlu audit. |
| Settings | Deadline, correction, dokumen, NV, notifikasi, banding | `superadmin.settings.*` | `deadline/correction/dokumen/nv/notifikasi/banding/update` | settings pages | `SettingsTest` | Done | `settings.update` sudah dilindungi permission; limit action, banding eligibility, NV override, document requirement, dan `nv_reason_mode` sudah enforce. |
| Audit Log | List/detail/export audit trail | `superadmin.audit.index/show/export` | `AuditController@index/show/export` | `superadmin.audit.*` | `AuditExportTest`, `SettingsTest` smoke | Done | Export CSV sudah tersedia; perlu SSO failure/unlink/reset bila fitur itu ditambahkan. |
| Notification Center | Inbox/pusat notifikasi | `superadmin.notifications.*` | `NotificationCenterController` | `superadmin.notifications.index` | `NotificationCenterTest` | Done | Inbox, filter, mark read, dan mark all read sudah tersedia; perlu integrasi notifikasi operasional lanjutan bila event baru ditambah. |
| Operational Board | Bottleneck/SLA/workload board | `superadmin.dashboard` | `DashboardController@index` | `superadmin.dashboard.index` | `DashboardExportTest` | Done | Sudah ada board status, SLA breach, urgent queue, workload asesor; perlu export dan assignment warning lanjutan. |

## Route Coverage Super Admin

Route Super Admin berada di `routes/web.php` dengan middleware:

```php
Route::middleware(['auth', 'role:super_admin'])->prefix('superadmin')->name('superadmin.')->group(...)
```

Total area route:

1. Dashboard: `superadmin.dashboard`, `superadmin.dashboard.export`.
2. Akreditasi workflow: index, export, pengajuan, detail, review awal, assessment, review tahap 1, assign/reassign asesor, review tahap 2, visitasi, scoring, laporan, validasi akhir, SK, banding.
3. Master data: EDPM, document categories, data pesantren, roles, users.
4. Settings: index, update, deadline, correction, dokumen, NV, notifikasi, banding.
5. Audit: index, show, export.
6. Notification Center: index, mark read, mark all read.

Total route Super Admin saat audit ini: 69.

Kesimpulan route: coverage route end-to-end sudah luas, tetapi completeness masih perlu dinilai dari action coverage, permission granular, audit, dan test - bukan dari jumlah route saja.

## Test Coverage Super Admin

Test yang ada:

- `tests/Feature/SuperAdmin/DashboardExportTest.php`
- `tests/Feature/SuperAdmin/AkreditasiConsoleTest.php`
- `tests/Feature/SuperAdmin/MasterDataTest.php`
- `tests/Feature/SuperAdmin/SettingsTest.php`
- `tests/Feature/SuperAdmin/NotificationCenterTest.php`
- `tests/Feature/SuperAdmin/AuditExportTest.php`
- `tests/Feature/MuhammadiyahIdSsoTest.php`

Coverage yang kuat:

- Dashboard render/export.
- Akreditasi console render/detail/export.
- Pengajuan oleh Super Admin.
- Banding route/action Super Admin.
- Route-aware shared views agar form submit ke route Super Admin.
- Master data CRUD dasar.
- Data Pesantren readiness list/filter dan lock/unlock dengan audit reason.
- User/role management UI dan update dasar.
- Settings smoke/update.
- Notification Center render/filter/mark read.
- Audit export CSV.
- SSO mocked redirect/callback.

Gap test:

1. Belum ada test granular untuk semua action Super Admin end-to-end per status.
2. Audit log user/role/master/settings/export sudah mulai tercakup; masih perlu SSO failure/unlink/reset bila fitur tersedia.
3. Permission enforcement sudah punya forbidden-path tests untuk settings, role permission, user access, final approval, SK publish, review awal, tahap 1, assign asesor, dan proses banding; perlu diperluas ke destructive master data/export.
4. Settings enforcement tests sudah mencakup deadline, max correction cycles, action on limit, banding disabled, NV override disabled, document requirement, dan `nv_reason_mode`.
5. Belum ada test SSO live contract; wajar karena credential belum approved.

## Audit Logging Coverage

Sudah tercatat:

- Banyak transisi akreditasi melalui `AkreditasiWorkflowService` menggunakan `AuditTrailService`.
- Banding submit/process menggunakan `BandingService`.
- Settings update menggunakan `SettingsController@update` dengan `setting_changed`.

Belum/kurang tercatat:

- SSO login failure, unlink, reset, dan administrasi SSO detail bila fitur tersedia.
- SSO export/reset/unlink activity bila fitur itu tersedia.
- Reason policy yang konsisten untuk seluruh aksi destructive/sensitif.

Rekomendasi: lanjutkan audit layer untuk SSO/export dan permission destructive master data/export.

## Settings Enforcement Audit

Settings UI sudah punya kategori:

- Deadline: `review_awal_deadline`, `assessment_deadline`, `review_tahap1_deadline`, `correction_tahap1_deadline`, `review_tahap2_deadline`, `correction_tahap2_deadline`, `scoring_deadline`, `banding_deadline`.
- Correction: `max_siklus_tahap1`, `max_siklus_tahap2`, `action_on_limit`.
- Document: `kartu_kendali_wajib_before`, `laporan_wajib_before`.
- NV: `nv_override_allowed`, `nv_reason_mode`.
- Notification: `superadmin_receives_admin_notif`, `reminder_days`.
- Banding: `banding_eligibility`.

Temuan penting:

- `AkreditasiWorkflowService` sudah memakai `SuperAdminSettings::MAX_SIKLUS_TAHAP1` dan `MAX_SIKLUS_TAHAP2`.
- `DeadlineService` dan operational board sudah memakai `SuperAdminSettings::deadlineKeyForPhase()` agar phase deadline memakai key UI yang sama.
- `AkreditasiWorkflowService` memakai `action_on_limit` untuk default limit decision.
- `BandingService` memakai `banding_deadline` dan memblokir banding saat `banding_eligibility=disabled`.
- `nv_override_allowed` sudah menjadi gate di validasi akhir.
- `DocumentService` dan workflow memakai `kartu_kendali_wajib_before` serta `laporan_wajib_before` sebagai gate fase dokumen.
- `nv_reason_mode=collective` mewajibkan alasan umum, sedangkan `nv_reason_mode=per_butir` mewajibkan alasan per butir dan menyimpannya di audit log `nv_changed`.
- Document-category `required_for_phase` sudah diperiksa bersama requirement global; visibility rules tetap dipakai di `DocumentService::getVisibleDocuments()`.

Rekomendasi: lanjutkan permission enforcement expansion dan audit SSO/export.

## Permission Enforcement Audit

Saat ini:

- Role dan Permission model serta pivot sudah ada.
- Super Admin UI untuk permission matrix sudah ada dan aman.
- Route protection area besar masih berbasis role middleware, contoh: `role:super_admin`, `role:admin`, `role:asesor`, `role:pesantren`.
- Middleware `permission` sudah tersedia untuk action-level enforcement.

Sudah diterapkan:

- `permission:settings.update`
- `permission:role.permissions.update`
- `permission:user.access.update`
- `permission:akreditasi.final.approve`
- `permission:sk.publish`
- `permission:akreditasi.review_awal`
- `permission:akreditasi.stage1_review`
- `permission:akreditasi.assign_asesor`
- `permission:akreditasi.proses_banding`

Gap:

- Belum semua action workflow sensitif memakai permission granular.
- Belum semua destructive master-data action punya permission granular yang spesifik.
- Belum ada permission untuk export activity dan SSO management lanjutan.

Rekomendasi: lanjutkan enforcement bertahap ke destructive master data dan export activity, sambil menambahkan forbidden-path tests per route.

## UI/UX Coverage

Sudah dipolish:

- Super Admin dashboard.
- Operational board dashboard.
- Akreditasi console/detail.
- Audit center.
- Settings forms.
- Master data center.
- Document categories.
- Data Pesantren control page.
- Role & Permission safe edit modal.
- User management + SSO pre-registration.
- Notification Center.

Partial:

- Beberapa action flow Super Admin masih memakai view Admin/Asesor/Pesantren dengan route override. Ini efisien dan fungsional, tetapi perlu review UX agar pengguna sadar sedang bekerja sebagai Super Admin.
- Action endpoints tanpa halaman tersendiri perlu dipastikan punya CTA/confirmation yang jelas di Action Center.

Missing:

- User detail/SSO detail page.
- Asesor workload page dan assignment overload warning.
- Edit/override detail data pesantren.

## Recommended Next Implementation Order

### P0 — Stabilization / Governance

1. **Permission enforcement expansion**
   - Terapkan permission granular ke action destructive master data dan export berikutnya.
   - Tambah tests permission denied per route.

2. **Audit log SSO/export**
   - Log SSO failure/unlink/reset bila fitur tersedia.
   - Log CSV exports penting.

### P1 — Operational Control

3. **Asesor Workload & Assignment Intelligence**
   - Lihat beban asesor aktif.
   - Jumlah assignment ketua/anggota.
   - Assignment overdue.
   - Warning saat assign asesor overload.

### P2 — Data & Reporting

4. **User detail + SSO management**
   - Detail user, SSO profile, last login.
   - Reset/unlink SSO.
   - Resend invite.
   - Bulk import pre-registration.

5. **Data Pesantren Control lanjutan**
   - Detail pesantren.
   - Edit/override data with audit.

6. **Reporting/export suite**
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

1. Jangan menambah fitur besar sebelum permission destructive master data/export berikutnya beres.
2. Pertahankan `main` sebagai baseline tunggal; buat branch baru per topik.
3. Untuk task berikutnya, gunakan branch baru: `feature/superadmin-governance-audit`.
4. Implementasi berikutnya yang paling bernilai: permission enforcement dan audit trail untuk action destructive master data/export berikutnya.
