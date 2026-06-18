# Super Admin Flow Coverage Audit

Tanggal audit: 2026-06-19
Baseline branch: `main`  
Tujuan: memastikan Super Admin menjadi pusat kendali seluruh proses bisnis akreditasi sebelum pengembangan fitur lanjutan.

## Ringkasan Eksekutif

Super Admin saat ini sudah memiliki coverage besar atas workflow akreditasi end-to-end, master data, settings, audit log, user/role management, dan skeleton SSO Muhammadiyah ID. Dari sisi route, Super Admin sudah bisa masuk ke hampir semua aksi lintas role: Pesantren, Admin, Ketua Asesor, Anggota Asesor, hingga validasi akhir dan banding.

Gap utama bukan lagi akses route dasar, tetapi governance dan operasional:

1. Permission matrix sudah punya enforcement pada aksi sensitif awal dan sebagian workflow utama; akses area besar tetap berbasis role.
2. Settings sudah punya UI, key alignment terpusat, dan enforcement untuk deadline, correction cycles, limit action, banding eligibility, NV override, document requirement, dan `nv_reason_mode`.
3. Audit log non-akreditasi sudah diperluas, audit export CSV tersedia, dan reset/unlink SSO user sudah tercatat; live SSO credential/contract masih perlu verifikasi eksternal.
4. Operational board, Notification Center, Workload Asesor Center, SK Management, export workload asesor, export user/role/nilai/dokumen/SK, dan konfirmasi assignment overload sudah tersedia.
5. User lifecycle SSO sudah punya pre-registration, detail user, resend invite, update identitas SSO, dan reset/unlink SSO; live integration tetap menunggu credential.
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
| Akreditasi Console | Export console CSV | `superadmin.akreditasi.export`, `superadmin.akreditasi.export-scores`, `superadmin.akreditasi.export-documents` | `AkreditasiController@export/exportScores/exportDocumentStatus` | N/A | `AkreditasiConsoleTest` | Done | Export console, nilai/peringkat, status dokumen, workload asesor, dan audit sudah tersedia. |
| Akreditasi Detail | Detail pengajuan lengkap | `superadmin.akreditasi.show` | `AkreditasiController@show` | `superadmin.akreditasi.show` | `AkreditasiConsoleTest` | Done | Perlu action audit completeness dan UI QA semua tab. |
| Pengajuan | Buat pengajuan untuk pesantren | `superadmin.akreditasi.pengajuan`, `submit-pengajuan` | `pengajuanForm`, `submitPengajuan` | `superadmin.akreditasi.pengajuan` | `AkreditasiConsoleTest` | Done | Perlu validasi duplicate/eligibility lebih jelas di UI. |
| Review Awal | Terima/tolak pengajuan | `review-awal`, `terima-pengajuan`, `tolak-pengajuan` | `reviewAwal`, `terimaPengajuan`, `tolakPengajuan` | `admin.akreditasi.review-awal` dengan route Super Admin | `AkreditasiConsoleTest` | Done | Aksi terima/tolak sudah dilindungi `permission:akreditasi.review_awal`; shared view menampilkan banner Super Admin Mode. |
| Assessment | Buka assessment + deadline | `buka-assessment` | `bukaAssessment` | `admin.akreditasi.buka-assessment` | `AkreditasiConsoleTest` | Done | Aksi sudah dilindungi `permission:akreditasi.review_awal`; deadline kosong otomatis memakai setting `assessment_deadline`. |
| Assessment | Upload kartu kendali | `upload-kk` | `uploadKartuKendali` | Action Center detail Super Admin | `SettingsTest`, `AkreditasiConsoleTest` | Done | Requirement sudah enforce sesuai `kartu_kendali_wajib_before`, action dilindungi `permission:akreditasi.document.upload`, dan CTA upload eksplisit tersedia di detail. |
| Review Tahap 1 | Review, minta perbaikan, approve | `review-tahap1`, `minta-perbaikan-tahap1`, `approve-tahap1` | `reviewTahap1`, `mintaPerbaikanTahap1`, `approveTahap1` | `admin.akreditasi.review-tahap1` | `AkreditasiConsoleTest` | Done | Aksi keputusan sudah dilindungi `permission:akreditasi.stage1_review`; perlu audit UX untuk sections dan reason required/optional. |
| Koreksi Tahap 1 Limit | Keputusan saat batas koreksi | `handle-limit-review` | `handleLimitReview` | Action endpoint | `SettingsTest`, `AkreditasiConsoleTest` | Done | `action_on_limit` sudah mengontrol default decision dan action dilindungi `permission:akreditasi.stage1_review`. |
| Asesor Assignment | Assign asesor | `assign-asesor` | `assignAsesor` | `admin.akreditasi.assign-asesor` | `AkreditasiConsoleTest` | Done | Sudah dilindungi `permission:akreditasi.assign_asesor`; form memakai workload aktif terpusat dan mewajibkan konfirmasi + alasan jika pilihan mencapai overload. |
| Asesor Assignment | Reassign asesor | `reassign-asesor` | `reassignAsesor` | `admin.akreditasi.reassign-asesor` | `AkreditasiConsoleTest` | Done | Sudah dilindungi `permission:akreditasi.assign_asesor`; reason, asesor sebelumnya, asesor baru, dan overload metadata tampil di riwayat assignment detail. |
| Asesor Assignment | Workload center asesor | `superadmin.asesor-workload.index`, `superadmin.asesor-workload.show`, `superadmin.asesor-workload.export` | `AssessorWorkloadController@index/show/export` | `superadmin.asesor-workload.index`, `superadmin.asesor-workload.show` | `AssessorWorkloadTest` | Done | Menampilkan total asesor, normal/medium/overload, assignment aktif, overdue, distribusi ketua/anggota, status aktif, drill-down histori assignment/reassignment per asesor, link detail akreditasi, dan export CSV terfilter. |
| Review Tahap 2 | Ketua Asesor review | `review-tahap2`, `layak-visitasi`, `minta-perbaikan-tahap2` | `reviewTahap2`, `nyatakanLayakVisitasi`, `mintaPerbaikanTahap2` | `asesor.ketua.review-tahap2` | `AkreditasiConsoleTest` | Done | Action dilindungi `permission:akreditasi.stage2_review`; shared asesor view menampilkan banner Super Admin Mode. |
| Visitasi | Jadwalkan visitasi + schedule overview | `superadmin.visitasi.index`, `jadwalkan-visitasi` | `DashboardController@visitasiOverview`, `jadwalkanVisitasi` | `superadmin.visitasi.index`, `asesor.ketua.jadwalkan-visitasi` | `DashboardExportTest`, `AkreditasiConsoleTest` | Done | Action dilindungi `permission:akreditasi.visitasi.manage`; Super Admin kini punya board operasional untuk kesiapan jadwal, overdue, scoring, dan validasi akhir visitasi. |
| Visitasi | Tandai visitasi selesai | `tandai-visitasi-selesai` | `tandaiVisitasiSelesai` | Action Center detail Super Admin | `AkreditasiConsoleTest` | Done | Action dilindungi `permission:akreditasi.visitasi.manage`; CTA langsung dan konfirmasi tersedia di detail. |
| Scoring | Input NA1 | `input-na1` | `inputNA1` | `asesor.ketua.input-na1` | `AkreditasiConsoleTest` | Done | Action dilindungi `permission:akreditasi.scoring.manage`; shared asesor view menampilkan banner Super Admin Mode. |
| Scoring | Input NA2 | `input-na2` | `inputNA2` | `asesor.anggota.input-na2` | `AkreditasiConsoleTest` | Done | Action dilindungi `permission:akreditasi.scoring.manage`; shared asesor view menampilkan banner Super Admin Mode. |
| Scoring | Input NK | `input-nk` | `inputNK` | `asesor.ketua.input-nk` | `AkreditasiConsoleTest` | Done | Action dilindungi `permission:akreditasi.scoring.manage`; validasi rentang nilai dan prasyarat NA1/NA2 final sudah dites. |
| Laporan Visitasi | Upload laporan individu/kelompok | `upload-laporan` | `uploadLaporan` | `asesor.ketua.upload-laporan` | `AkreditasiConsoleTest` | Done | Action dilindungi `permission:akreditasi.laporan.manage`; perlu document-category rule integration. |
| Laporan Visitasi | Submit hasil visitasi | `submit-hasil-visitasi` | `submitHasilVisitasi` | Action Center detail Super Admin | `SettingsTest`, `AkreditasiConsoleTest` | Done | Requirement laporan sudah enforce sesuai `laporan_wajib_before`, action dilindungi `permission:akreditasi.laporan.manage`, dan CTA submit tersedia di detail. |
| Validasi Akhir | Validasi akhir | `validasi-akhir`, `approve-final`, `tolak-final` | `validasiAkhir`, `approveFinal`, `tolakFinal` | `admin.akreditasi.validasi-akhir` | `SettingsTest`, `AkreditasiConsoleTest` | Done | `approve-final` dilindungi `permission:akreditasi.final.approve`, `tolak-final` dilindungi `permission:akreditasi.final.reject`; `nv_override_allowed` dan `nv_reason_mode` sudah enforce. |
| SK | SK Management, terbitkan SK + sertifikat digital | `superadmin.sk.index`, `superadmin.sk.export`, `form-terbitkan-sk`, `terbitkan-sk`, `akreditasi.sertifikat.download` | `skIndex`, `skExport`, `formTerbitkanSK`, `terbitkanSK`, `AkreditasiCertificateController@download` | `superadmin.sk.index`, `admin.akreditasi.terbitkan-sk` (shared Super Admin/Admin), hasil akhir pesantren, detail Super Admin | `AkreditasiConsoleTest`, `AkreditasiFlowTest`, `EndToEndWorkflowTest` | Done | SK Management sudah tersedia dengan filter, statistik, export CSV + audit, link publish, detail, dan unduh sertifikat; publish SK dilindungi `permission:sk.publish`. |
| Banding | Lihat, terima, tolak banding | `banding`, `superadmin.banding.terima`, `superadmin.banding.tolak` | `banding`, `terimaBanding`, `tolakBanding` | `admin.akreditasi.banding` | `AkreditasiConsoleTest`, `SettingsTest` | Done | Terima/tolak sudah dilindungi `permission:akreditasi.proses_banding`; `banding_eligibility=disabled` sudah memblokir pengajuan banding. |
| Master Data | Dashboard master data | `superadmin.master-data.index` | `MasterDataController@index` | `superadmin.master-data.index` | `MasterDataTest` | Done | Good. |
| Master Data EDPM | CRUD komponen & butir | `master-data.edpm.*` | `edpm`, `store/update/destroy Komponen/Butir` | `superadmin.master-data.edpm.index` | `MasterDataTest` | Done | Perlu audit log perubahan master instrumen. |
| Document Categories | CRUD/toggle kategori dokumen | `master-data.document-categories.*` | `documentCategories`, `store/update/toggle/destroy` | `superadmin.master-data.document-categories.index` | `MasterDataTest` | Done | Perlu audit log; rules belum sepenuhnya terhubung ke workflow upload/visibility. |
| Data Pesantren Control | List/detail readiness, lock/unlock, override profil/unit, dokumen, IPM, SDM, dan EDPM pesantren | `master-data.pesantren.*` | `pesantren`, `showPesantren`, `updatePesantren`, `updatePesantrenDocuments`, `updatePesantrenIpm`, `updatePesantrenSdm`, `updatePesantrenEdpm`, `togglePesantrenLock` | `superadmin.master-data.pesantren.*` | `MasterDataTest` | Done | Override profil/unit/dokumen/IPM/SDM/EDPM sudah dilindungi `permission:user.access.update` dan audit reason. |
| Role & Permission | Matrix read-only + modal edit/export | `master-data.roles.index`, `roles.export`, `roles.permissions.update` | `roles`, `exportRoles`, `updateRolePermissions` | `superadmin.master-data.roles.index` | `MasterDataTest` | Done | Update permission sudah dilindungi `permission:role.permissions.update`, punya audit diff, dan export matrix permission. |
| User Management | List/filter/detail/export user | `master-data.users.index/show/export` | `users`, `showUser`, `exportUsers` | `superadmin.master-data.users.*` | `MasterDataTest` | Done | Detail user sudah menampilkan role/status, profil SSO, statistik akreditasi, audit terbaru, dan export CSV terfilter. |
| User Management | Invite/pre-register SSO user | `master-data.users.store`, `master-data.users.import`, `master-data.users.invite.resend` | `storeUser`, `importUsers`, `resendUserInvite` | Modal users page + detail page | `MasterDataTest` | Done | Pre-registration, bulk import CSV, dan resend invite sudah dilindungi `permission:user.access.update` dengan audit reason. |
| User Management | Edit role/status | `master-data.users.update` | `updateUser` | Modal users page + detail page | `MasterDataTest` | Done | Sudah dilindungi `permission:user.access.update`, reason perubahan, dan guard self-lockout/last active Super Admin. |
| SSO | Muhammadiyah ID env + detail/reset management | `/auth/muhammadiyah/*`, `master-data.users.sso-*` | `Auth\MuhammadiyahIdController`, `MasterDataController` | Login button, user detail | `MuhammadiyahIdSsoTest`, `MasterDataTest` | Partial | Live credential belum ada; update M-ID/NBM dan reset/unlink SSO sudah tersedia dengan audit. |
| Settings | Settings dashboard | `superadmin.settings.index` | `SettingsController@index` | `superadmin.settings.index` | `SettingsTest` | Done | Good UI, but enforcement coverage perlu audit. |
| Settings | Deadline, correction, dokumen, NV, notifikasi, banding | `superadmin.settings.*` | `deadline/correction/dokumen/nv/notifikasi/banding/update` | settings pages | `SettingsTest` | Done | `settings.update` sudah dilindungi permission; limit action, banding eligibility, NV override, document requirement, dan `nv_reason_mode` sudah enforce. |
| Audit Log | List/detail/export audit trail | `superadmin.audit.index/show/export` | `AuditController@index/show/export` | `superadmin.audit.*` | `AuditExportTest`, `SettingsTest` smoke | Done | Export CSV sudah tersedia; perlu SSO failure/unlink/reset bila fitur itu ditambahkan. |
| Notification Center | Inbox/pusat notifikasi | `superadmin.notifications.*` | `NotificationCenterController` | `superadmin.notifications.index` | `NotificationCenterTest` | Done | Inbox, filter, mark read, dan mark all read sudah tersedia; perlu integrasi notifikasi operasional lanjutan bila event baru ditambah. |
| Operational Board | Bottleneck/SLA/workload board | `superadmin.dashboard` | `DashboardController@index` | `superadmin.dashboard.index` | `DashboardExportTest` | Done | Sudah ada board status, SLA breach, urgent queue, workload asesor, dan link ke Workload Asesor Center; perlu export operasional lanjutan. |

## Route Coverage Super Admin

Route Super Admin berada di `routes/web.php` dengan middleware:

```php
Route::middleware(['auth', 'role:super_admin'])->prefix('superadmin')->name('superadmin.')->group(...)
```

Total area route:

1. Dashboard: `superadmin.dashboard`, `superadmin.dashboard.export`, `superadmin.asesor-workload.index`, `superadmin.sk.index`.
2. Akreditasi workflow: index, export, pengajuan, detail, review awal, assessment, review tahap 1, assign/reassign asesor, review tahap 2, visitasi, scoring, laporan, validasi akhir, SK, banding.
3. Master data: EDPM, document categories, data pesantren, roles, users.
4. Settings: index, update, deadline, correction, dokumen, NV, notifikasi, banding.
5. Audit: index, show, export.
6. Notification Center: index, mark read, mark all read.

Total route Super Admin saat audit ini: 77.

Kesimpulan route: coverage route end-to-end sudah luas, tetapi completeness masih perlu dinilai dari action coverage, permission granular, audit, dan test - bukan dari jumlah route saja.

## Test Coverage Super Admin

Test yang ada:

- `tests/Feature/SuperAdmin/DashboardExportTest.php`
- `tests/Feature/SuperAdmin/AssessorWorkloadTest.php`
- `tests/Feature/SuperAdmin/AkreditasiConsoleTest.php`
- `tests/Feature/SuperAdmin/MasterDataTest.php`
- `tests/Feature/SuperAdmin/SettingsTest.php`
- `tests/Feature/SuperAdmin/NotificationCenterTest.php`
- `tests/Feature/SuperAdmin/AuditExportTest.php`
- `tests/Feature/MuhammadiyahIdSsoTest.php`

Coverage yang kuat:

- Dashboard render/export.
- Workload Asesor Center render, overload filter, export CSV, audit export, permission export, dan eksklusi assignment terminal.
- Akreditasi console render/detail/export, termasuk Action Center CTA langsung.
- Pengajuan oleh Super Admin.
- Banding route/action Super Admin.
- Route-aware shared views agar form submit ke route Super Admin.
- Assignment overload warning, confirmation, reason requirement, dan audit metadata.
- Master data CRUD dasar.
- Data Pesantren readiness list/filter, detail, lock/unlock, dan override profil/unit dengan audit reason.
- User/role management UI dan update dasar.
- User detail, SSO pre-registration identity update, dan reset/unlink SSO dengan audit reason.
- Settings smoke/update.
- Notification Center render/filter/mark read.
- SK Management list/filter/export audit.
- Scoring NA1/NA2/NK untuk Super Admin, validasi rentang nilai, dan prasyarat NK.
- Audit export CSV.
- SSO mocked redirect/callback.

Gap test:

1. Test granular sudah bertambah untuk action utama; masih perlu audit berkala bila workflow/status baru ditambahkan.
2. Audit log user/role/master/settings/export/SSO reset sudah mulai tercakup; masih perlu live SSO failure contract bila credential tersedia.
3. Permission enforcement sudah punya forbidden-path tests untuk settings, role permission, user access, final approval/reject, SK publish, export, review awal, tahap 1, assign asesor, visitasi, scoring, laporan, dokumen, dan proses banding; perlu disinkronkan bila action baru ditambahkan.
4. Settings enforcement tests sudah mencakup deadline, max correction cycles, action on limit, banding disabled, NV override disabled, document requirement, dan `nv_reason_mode`.
5. Belum ada test SSO live contract; wajar karena credential belum approved.

## Audit Logging Coverage

Sudah tercatat:

- Banyak transisi akreditasi melalui `AkreditasiWorkflowService` menggunakan `AuditTrailService`.
- Banding submit/process menggunakan `BandingService`.
- Settings update menggunakan `SettingsController@update` dengan `setting_changed`.
- Export dashboard, audit, akreditasi, nilai, dokumen, workload asesor, user/role, dan SK Management memakai `superadmin_exported`.

Belum/kurang tercatat:

- Live SSO failure contract bila credential tersedia.
- Reason policy yang konsisten untuk seluruh aksi destructive/sensitif.

Rekomendasi: lanjutkan audit layer saat action baru ditambahkan dan tunggu credential untuk live SSO contract.

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
- `DeadlineService`, operational board, dan buka assessment sudah memakai key `assessment_deadline`/deadline Super Admin agar SLA memakai setting yang sama.
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
- `permission:akreditasi.stage2_review`
- `permission:akreditasi.visitasi.manage`
- `permission:akreditasi.scoring.manage`
- `permission:akreditasi.laporan.manage`
- `permission:akreditasi.final.reject`
- `permission:akreditasi.document.upload`

Gap:

- Destructive master-data dan export utama sudah punya permission granular; perlu audit ulang bila ada action baru.
- Permission baru perlu disinkronkan setiap kali workflow baru ditambahkan.

Rekomendasi: lanjutkan enforcement bertahap untuk action baru sambil menambahkan forbidden-path tests per route.

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
- User detail + SSO reset/unlink management.
- Notification Center.
- SK Management.

Partial:

- Beberapa action flow Super Admin masih memakai view Admin/Asesor/Pesantren dengan route override. Ini efisien dan fungsional, tetapi perlu review UX agar pengguna sadar sedang bekerja sebagai Super Admin.
- Action endpoints tanpa halaman tersendiri kini mengikuti pola CTA yang eksplisit: primary CTA, aksi langsung, action tambahan di kebab menu, dan state tanpa aksi ditampilkan jelas di Action Center.

Missing:

- Live SSO contract test menunggu credential Muhammadiyah ID.

## Recommended Next Implementation Order

### P0 — Stabilization / Governance

1. **QA shared Admin/Asesor views**
   - Audit ulang seluruh shared view agar banner Super Admin Mode, wording, dan form route override tetap konsisten.
   - Tambah test bila ditemukan view yang belum route-aware.

2. **Regression suite berkala**
   - Jalankan `tests/Feature/SuperAdmin` setelah perubahan workflow/permission.
   - Sinkronkan permission dan forbidden-path test setiap action baru ditambahkan.

### P1 — External Dependency

3. **Live SSO contract**
   - Verifikasi redirect/callback/failure contract setelah credential Muhammadiyah ID tersedia.
   - Tambah test failure path live bila credential sudah approved.

### P2 — Data & Reporting

4. **Reporting/export suite lanjutan**
   - Export users/roles/nilai/dokumen/SK/workload sudah tersedia.
   - Perluasan berikutnya hanya bila ada domain laporan baru.

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

1. Jangan menambah action baru tanpa permission middleware, audit reason/metadata, dan forbidden-path test.
2. Pertahankan `main` sebagai baseline tunggal; buat branch baru per topik.
3. Untuk task berikutnya, gunakan branch kecil sesuai topik QA/regression.
4. Implementasi berikutnya yang paling bernilai: QA shared view Super Admin dan live SSO contract saat credential tersedia.
