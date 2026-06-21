# Software Requirements Specification (SRS)
# PesantrenMu — Sistem Akreditasi Pesantren Muhammadiyah

**Versi:** 1.0  
**Tanggal:** 2026-06-21  
**Repo:** github.com/alanramadhani2112/spm  
**Stack:** Laravel 12 + PHP 8.x + MySQL + Blade (Metronic UI)

---

## Daftar Isi

1. [Pendahuluan](#1-pendahuluan)
2. [Deskripsi Umum](#2-deskripsi-umum)
3. [Arsitektur Sistem](#3-arsitektur-sistem)
4. [Aktor & Role](#4-aktor--role)
5. [Proses Bisnis Akreditasi](#5-proses-bisnis-akreditasi)
6. [State Machine & Workflow](#6-state-machine--workflow)
7. [Formula Penilaian & Scoring](#7-formula-penilaian--scoring)
8. [Model Data](#8-model-data)
9. [Modul & Fitur](#9-modul--fitur)
10. [Permission & Otorisasi](#10-permission--otorisasi)
11. [Notifikasi](#11-notifikasi)
12. [Audit Trail](#12-audit-trail)
13. [Dokumen & Persyaratan](#13-dokumen--persyaratan)
14. [SSO Muhammadiyah ID](#14-sso-muhammadiyah-id)
15. [Konfigurasi Super Admin](#15-konfigurasi-super-admin)
16. [Non-Functional Requirements](#16-non-functional-requirements)

---

## 1. Pendahuluan

### 1.1 Tujuan

PesantrenMu adalah sistem informasi akreditasi pesantren Muhammadiyah berbasis web. Sistem ini menjadi pusat kendali proses akreditasi end-to-end mulai dari pengajuan, assessment, review, visitasi, penilaian, penerbitan SK/sertifikat, hingga banding agar seluruh proses berjalan transparan, tertib, dan terlacak.

### 1.2 Ruang Lingkup

- Pendaftaran dan manajemen data pesantren (profil, IPM, SDM, EDPM)
- Pengajuan akreditasi oleh pesantren
- Review administratif oleh Admin
- Asesmen mandiri (self-assessment) oleh pesantren
- Penugasan dan review oleh Asesor (Ketua & Anggota)
- Visitasi dan penilaian pasca-visitasi
- Validasi akhir dan penerbitan SK/Sertifikat
- Proses banding (appeal)
- Manajemen master data, pengguna, role, permission
- Audit trail untuk seluruh transisi status
- Notifikasi event workflow
- Dashboard operasional dengan SLA monitoring
- Integrasi SSO Muhammadiyah ID (OAuth 2.0)

### 1.3 Definisi & Singkatan

| Istilah | Kepanjangan / Arti |
|---|---|
| EDPM | Evaluasi Diri Pesantren Muhammadiyah |
| IPM | Instrumen Pemetaan Mutu |
| SDM | Sumber Daya Manusia (pesantren) |
| IK | Instrumen Kinerja |
| IPR | Instrumen Penilaian Rapor |
| NA | Nilai Akhir |
| NK | Nilai Komponen |
| NV | Nilai Visitasi |
| SK | Surat Keputusan |
| NSPP | Nomor Statistik Pesantren Pemerintah |
| SSO | Single Sign-On |
| SLA | Service Level Agreement (deadline) |

---

## 2. Deskripsi Umum

### 2.1 Brand & Karakter

- **Nama Produk:** PesantrenMu
- **Kreatif North Star:** The Operational Courtyard
- **Karakter Visual:** Dashboard operasional familiar, sidebar gelap, content terang, kartu statistik, tabel administratif, status badge berwarna
- **Personality:** Ramah, jelas, aman
- **Prioritas:** Keterbacaan, kecepatan orientasi, rasa aman untuk keputusan akreditasi sensitif

### 2.2 Prinsip Desain

1. Tunjukkan langkah berikutnya dengan jelas pada setiap status workflow
2. Jadikan aksi sensitif terasa aman melalui konteks, konfirmasi, permission, dan audit visibility
3. Pertahankan rasa ramah pesantren tanpa mengorbankan kepadatan data operasional
4. Gunakan pola UI familiar agar pengguna fokus pada tugas, bukan mempelajari interface
5. Beri empty state, error, dan validasi yang membantu pengguna memperbaiki data

### 2.3 Target Aksesibilitas

WCAG 2.2 AA - kontras teks, navigasi keyboard, label form jelas, fokus affordance, reduced motion, tidak mengandalkan warna saja untuk status penting.

---

## 3. Arsitektur Sistem

### 3.1 Technology Stack

| Lapisan | Teknologi |
|---|---|
| Backend Framework | Laravel 12 (PHP 8.x) |
| Database | MySQL |
| Frontend | Blade template engine + Metronic Admin UI |
| CSS Framework | Bootstrap 5 + custom Metronic components |
| Autentikasi | Laravel Session + Muhammadiyah ID OAuth 2.0 SSO |
| Otorisasi | Role-based + Permission-based (custom RBAC) |
| State Machine | Custom AkreditasiStateMachine service |
| File Storage | Local disk (Laravel Storage) |
| Cache | Laravel Cache (file/redis) |

### 3.2 Struktur Direktori Utama

```
app/
├── Http/Controllers/
│   ├── Admin/AkreditasiController.php
│   ├── Asesor/KetuaAsesorController.php
│   ├── Asesor/AnggotaAsesorController.php
│   ├── Pesantren/AkreditasiController.php
│   ├── Pesantren/DataController.php
│   ├── SuperAdmin/AkreditasiController.php
│   ├── SuperAdmin/AssessorWorkloadController.php
│   ├── SuperAdmin/AuditController.php
│   ├── SuperAdmin/DashboardController.php
│   ├── SuperAdmin/MasterDataController.php
│   ├── SuperAdmin/NotificationCenterController.php
│   ├── SuperAdmin/SettingsController.php
│   └── Auth/MuhammadiyahIdController.php
├── Models/          (30 model)
├── Services/        (12 service)
├── Support/         (SuperAdminSettings)
├── Events/
├── Exceptions/
└── Providers/
```

### 3.3 Route Structure

```
/login                          → auth.login (Muhammadiyah ID SSO + local)
/auth/muhammadiyah/redirect     → SSO redirect
/auth/muhammadiyah/callback     → SSO callback
/logout → logout

/pesantren/data/*               → Manajemen data pesantren
/pesantren/akreditasi/*         → Workflow akreditasi sisi pesantren

/admin/akreditasi/*             → Workflow admin

/asesor/ketua/*                 → Workflow ketua asesor
/asesor/anggota/*               → Workflow anggota asesor

/superadmin/                    → God-mode control plane
  dashboard/ → Dashboard + operational board
  akreditasi/* → Console
  master-data/* → Users, roles, permissions, pesantren, EDPM
  settings/* → Konfigurasi sistem
  visitasi/ → Manajemen visitasi
  sk/ → Manajemen SK
  assessor-workload/ → Workload asesor
  audit/ → Audit trail center
  notifications/ → Notification center
```

---

## 4. Aktor & Role

### 4.1 Daftar Role

| # | Role | Parameter | Deskripsi |
|---|---|---|---|
| 1 | Super Admin | super_admin | Pusat kendali penuh. Akses ke seluruh sistem, settings, master data, dan workflow lintas role. |
| 2 | Admin | admin | Mengelola workflow akreditasi: review awal, review tahap 1, validasi akhir, SK. |
| 3 | Asesor | asesor | Melakukan review tahap 2, visitasi, dan penilaian. Terbagi menjadi Ketua dan Anggota. |
| 4 | Pesantren | pesantren | Institusi yang mengajukan akreditasi, mengisi data, self-assessment, mengajukan banding. |

### 4.2 Sub-role Asesor

| Sub-role | Atribut | Tanggung Jawab |
|---|---|---|
| Ketua Asesor | is_ketua = true | Review tahap 2, jadwal visitasi, input NA1, input NK, upload laporan kelompok, submit hasil visitasi |
| Anggota Asesor | is_ketua = false | Input NA2 (penilaian individu), upload laporan individu |

### 4.3 Hirarki Akses

```
Super Admin (god mode - seluruh sistem)
  ├── Admin (workflow akreditasi) → Akses ke seluruh pengajuan pesantren
  ├── Asesor (review & visitasi)
  │     ├── Ketua (koordinasi + penilaian utama)
  │     └── Anggota (penilaian pendukung)
  └── Pesantren (data + pengajuan sendiri)
```

---

## 5. Proses Bisnis Akreditasi

### 5.1 Gambaran Umum

Proses akreditasi pesantren terdiri dari beberapa fase utama:

```
PENGAJUAN → REVIEW AWAL → ASSESSMENT → REVIEW TAHAP 1 → PENUGASAN ASESOR
→ REVIEW TAHAP 2 → VISITASI → SCORING → VALIDASI AKHIR → SK/SERTIFIKAT
```

### 5.2 Alur Detail

#### Fase 1: Persiapan & Pengajuan (Pesantren)

1. **Isi Data Pesantren** - Pesantren mengisi: profil, IPM, SDM, EDPM
2. **Profil Terkunci** - Setelah pengajuan disubmit, data profil dikunci otomatis
3. **Submit Pengajuan** - Status: `initial_submitted`

#### Fase 2: Review Awal (Admin)

4. **Admin Review** - Admin memeriksa kelengkapan data pengajuan
5. **Terima** → Status: `assessment_open` (lanjut ke asesmen)
6. **Tolak** → Status: `initial_rejected` (pesantren bisa submit ulang)

#### Fase 3: Asesmen Mandiri (Pesantren)

7. **Self-Assessment** - Pesantren mengisi nilai EDPM per butir (skala 1-4)
8. **Upload Kartu Kendali** - Sesuai requirement fase
9. **Submit Assessment** → Status: `admin_stage_1_review`

#### Fase 4: Review Tahap 1 (Admin)

10. **Admin Review Tahap 1** - Memeriksa hasil asesmen, dokumen
11. **Approve** → Status: `assessor_assignment`
12. **Minta Perbaikan** → Status: `admin_stage_1_correction` (koreksi oleh pesantren)
13. **Tolak Administratif** → Status: `administrative_rejected`

**Siklus Koreksi:** Maksimal sesuai setting `max_siklus_tahap1` (default: 2). Jika limit tercapai, keputusan otomatis mengikuti `action_on_limit` (default: reject).

#### Fase 5: Penugasan Asesor (Admin/Super Admin)

14. **Assign Asesor** - Admin menunjuk 1 Ketua + minimal 1 Anggota Asesor
15. **Overload Check** - Sistem memeriksa beban kerja asesor sebelum assignment
16. **Reassign** - Admin dapat mengganti asesor yang sudah ditugaskan
17. Status: `assessor_assignment`

#### Fase 6: Review Tahap 2 (Asesor)

18. **Ketua Asesor Review** - Review dokumen dan hasil assessment
19. **Approve** → Status: `visitasi_scheduled`
20. **Minta Perbaikan** → Status: `assessor_stage_2_correction`
21. **Tolak Administratif** → Status: `administrative_rejected`

**Siklus Koreksi:** Maksimal sesuai setting `max_siklus_tahap2` (default: 2).

#### Fase 7: Visitasi (Asesor)

22. **Jadwalkan Visitasi** - Ketua menetapkan tanggal & catatan visitasi
23. **Visitasi Selesai** - Konfirmasi visitasi telah dilaksanakan → Status: `visitasi_completed`

#### Fase 8: Penilaian Pasca-Visitasi (Asesor)

24. **Input NA1** (Ketua) - Nilai Akhir 1 (berdasarkan IK + IPR)
25. **Input NA2** (Anggota) - Nilai Akhir 2 dari masing-masing anggota
26. **Input NK** (Ketua) - Nilai Komponen per komponen EDPM
27. **Input/NV** - Nilai Visitasi (default = NK, bisa di-override)
28. **Upload Laporan** - Laporan individu + laporan kelompok
29. **Submit Hasil** → Status: `visitasi_result_submitted`

#### Fase 9: Validasi Akhir (Admin)

30. **Admin Validasi Akhir** - Review hasil visitasi dan penilaian
31. **Setujui** → Status: `final_approved`
32. **Tolak** → Status: `final_rejected`
33. **NV Override** - Admin dapat mengubah NV bila `nv_override_allowed = true`

#### Fase 10: Penerbitan SK (Admin)

34. **Terbitkan SK** - Admin memasukkan nomor SK, masa berlaku, upload sertifikat
35. Status: `completed` (terminal). Sertifikat dapat diunduh oleh pesantren.

#### Fase 11: Banding / Appeal (Pesantren)

36. **Ajukan Banding** - Pesantren mengajukan banding setelah final rejected
37. Banding dibatasi deadline (`banding_deadline`, default 7 hari)
38. Banding bisa dinonaktifkan via setting (`banding_eligibility = disabled`)
39. **Admin Proses Banding:**
    - **Terima** → Status kembali ke `admin_final_validation`
    - **Tolak** → Status tetap `final_rejected`

---

## 6. State Machine & Workflow

### 6.1 Daftar Status

| # | Konstanta | Label | Terminal |
|---|---|---|---|
| 1 | draft_profile | Draft Profil | Tidak |
| 2 | initial_submitted | Pengajuan Awal | Tidak |
| 3 | initial_rejected | Ditolak Tahap 1 | Tidak |
| 4 | assessment_open | Asesmen Terbuka | Tidak |
| 5 | admin_stage_1_review | Review Admin Tahap 1 | Tidak |
| 6 | admin_stage_1_correction | Koreksi Tahap 1 | Tidak |
| 7 | admin_stage_1_limit_review | Review Batas Tahap 1 | Tidak |
| 8 | assessor_assignment | Penugasan Asesor | Tidak |
| 9 | assessor_stage_2_review | Review Asesor Tahap 2 | Tidak |
| 10 | assessor_stage_2_correction | Koreksi Tahap 2 | Tidak |
| 11 | assessor_stage_2_limit_review | Review Batas Tahap 2 | Tidak |
| 12 | visitasi_scheduled | Visitasi Terjadwal | Tidak |
| 13 | visitasi_completed | Visitasi Selesai | Tidak |
| 14 | post_visitasi_scoring | Penilaian Pasca Visitasi | Tidak |
| 15 | visitasi_result_submitted | Hasil Visitasi Diserahkan | Tidak |
| 16 | admin_final_validation | Validasi Akhir Admin | Tidak |
| 17 | administrative_rejected | Ditolak Administratif | Tidak |
| 18 | final_rejected | Final Ditolak | Tidak |
| 19 | appeal_submitted | Banding Diajukan | Tidak |
| 20 | final_approved | Final Disetujui | Semi |
| 21 | completed | Selesai | Ya |

### 6.2 Diagram Transisi

```
draft_profile → initial_submitted
initial_submitted → initial_rejected | assessment_open
initial_rejected → initial_submitted (resubmit)
assessment_open → admin_stage_1_review
admin_stage_1_review → admin_stage_1_correction | assessor_assignment | administrative_rejected
admin_stage_1_correction → admin_stage_1_review | admin_stage_1_limit_review
admin_stage_1_limit_review → assessor_assignment | administrative_rejected
assessor_assignment → assessor_stage_2_review
assessor_stage_2_review → assessor_stage_2_correction | visitasi_scheduled | administrative_rejected
assessor_stage_2_correction → assessor_stage_2_review | assessor_stage_2_limit_review
assessor_stage_2_limit_review → visitasi_scheduled | administrative_rejected
visitasi_scheduled → visitasi_completed | assessor_stage_2_review (rollback)
visitasi_completed → post_visitasi_scoring
post_visitasi_scoring → visitasi_result_submitted
visitasi_result_submitted → admin_final_validation
admin_final_validation → final_rejected | final_approved
final_rejected → appeal_submitted
appeal_submitted → admin_final_validation | final_rejected
administrative_rejected → initial_submitted | final_rejected | appeal_submitted
final_approved → completed (SK terbit)
completed → (terminal)
```

### 6.3 Keamanan State Machine

- **Validasi Transisi:** Setiap perubahan status divalidasi oleh `AkreditasiStateMachine.canTransition()`
- **Stale State Protection:** Optimistic lock (`updated_at` + `lockForUpdate()`) mencegah race condition
- **Atomic:** Transisi dibungkus dalam database transaction
- **Audit:** Setiap transisi mencatat: actor, from_status, to_status, reason, metadata, timestamp

---

## 7. Formula Penilaian & Scoring

### 7.1 Struktur EDPM

| # | Komponen | Kode | Butir | Bobot |
|---|---|---|---|---|
| 1 | Mutu Lulusan | MUTU_LULUSAN | 8 | 35 |
| 2 | Proses Pembelajaran | PROSES_PEMBELAJARAN | 10 | 29 |
| 3 | Mutu Ustaz | MUTU_USTAZ | 10 | 18 |
| 4 | Manajemen Pesantren | MANAJEMEN_PESANTREN | 12 | 18 |
| **Total** | | | **40 butir** | **100** |

Skala nilai per butir: **1, 2, 3, 4**

### 7.2 Rumus

**IK (Instrumen Kinerja)** - Rata-rata tertimbang 4 komponen:
```
IK = Σ(rata_rata_butir_komponen × bobot_komponen) / 100
```

**IPR (Instrumen Penilaian Rapor)** - Rata-rata sederhana 22 butir:
```
IPR = Σ(nilai_ipr) / jumlah_butir_valid
```

**NA (Nilai Akhir):**
```
NA = (0.7 × IK + 0.3 × IPR) × 25
```

**Nilai Akhir (Final Score):**
```
Nilai = round(NA), dibatasi 0-100
```

**NK (Nilai Komponen):**
```
NK_per_komponen = rata_rata(butir dalam komponen tersebut)
```

**NV (Nilai Visitasi):**
- Default: NV = NK
- Override: Admin dapat mengubah NV jika `nv_override_allowed = true`

### 7.3 Peringkat Akreditasi

| Peringkat | Label | Threshold |
|---|---|---|
| A | Unggul | Nilai ≥ 86 |
| B | Baik | Nilai ≥ 71 |
| C | Cukup | Nilai < 71 |

### 7.4 NA1 & NA2

- **NA1:** Nilai Akhir dari Ketua Asesor (berdasarkan IK + IPR)
- **NA2:** Nilai Akhir dari Anggota Asesor (penilaian individu)
- Final NA dihitung dari kombinasi NA1 dan NA2 setelah visitasi

---

## 8. Model Data

### 8.1 Entity Relationship (Ringkasan)

```
User (1) ─────── (1) Role
User (1) ─────── (0..1) Pesantren
User (1) ─────── (0..1) Asesor
User (1) ─────── (*) Akreditasi (via user_id)

Pesantren (1) ── (*) PesantrenUnit
Pesantren (1) ── (0..1) Profile
Pesantren (1) ── (0..1) Ipm
Pesantren (1) ── (0..1) SdmPesantren
Pesantren (1) ── (0..1) Edpm

Role (*) ─────── (*) Permission (via role_permission)

Akreditasi (1) ─ (*) Assessment
Akreditasi (1) ─ (*) AkreditasiRejection
Akreditasi (1) ─ (*) AkreditasiAuditLog
Akreditasi (1) ─ (*) AkreditasiEdpm
Akreditasi (1) ─ (*) AkreditasiCatatan
Akreditasi (1) ─ (*) Banding
Akreditasi (1) ─ (*) Document
Akreditasi (1) ─ (*) Notification (via akreditasi_id)

MasterEdpmKomponen (1) ─ (*) MasterEdpmButir
Document (*) ───── (0..1) DocumentCategory
```

### 8.2 Tabel Utama

| Tabel | Deskripsi | Field Kunci |
|---|---|---|
| users | Pengguna sistem | id, name, email, password, role_id, sso_id, m_id, nbm, sso_groups, status |
| roles | Role pengguna | id, name, parameter |
| permissions | Permission granular | id, name, key |
| role_permission | Pivot role-permission | role_id, permission_id |
| pesantrens | Data institusi pesantren | id, user_id, nama_pesantren, nspp, alamat, provinsi (40+ field) |
| pesantren_units | Unit pendidikan | id, pesantren_id, nama_unit, jenjang |
| ipms | Data IPM pesantren | id, user_id, data (json) |
| sdm_pesantrens | Data SDM pesantren | id, user_id, data (json) |
| edpms | Data EDPM pesantren | id, user_id, data (json) |
| asesors | Data asesor | id, user_id, is_ketua |
| akreditasis | Pengajuan akreditasi | id, uuid, user_id, status, na1, na2, nk, nv, nilai, peringkat, nomor_sk, sertifikat_path |
| assessments | Assessment pesantren | id, akreditasi_id, user_id, data (json) |
| akreditasi_edpms | Nilai EDPM per akreditasi | id, akreditasi_id, butir_id, komponen_id, isian (1-4) |
| akreditasi_rejections | Catatan penolakan | id, akreditasi_id, type, reason, rejected_by |
| bandings | Pengajuan banding | id, akreditasi_id, user_id, reason, status, admin_response, processed_by |
| akreditasi_audit_logs | Audit trail | id, akreditasi_id, action_type, from_status, to_status, actor_user_id, reason, metadata |
| notifications | Notifikasi | id, user_id, type, message, akreditasi_id, is_read |
| failed_notifications | Log notifikasi gagal | id, user_id, akreditasi_id, event, error |
| documents | Dokumen akreditasi | id, akreditasi_id, category_id, type, file_path, uploaded_by_user_id |
| document_categories | Kategori dokumen | id, name, code, required_for_phase, visible_to_roles, asesor_scope, is_active |
| super_admin_settings | Konfigurasi sistem | id, key, value |
| master_edpm_komponens | Komponen EDPM | id, kode, name, nama |
| master_edpm_butirs | Butir EDPM | id, komponen_id, kode, name, nama, deskripsi |
| permission_audit_logs | Audit permission | id, action, role_id, changed_by |

---

## 9. Modul & Fitur

### 9.1 Dashboard Super Admin

- Ringkasan nasional akreditasi (statistik per status, peringkat)
- **Operational Board:** Antrian lintas status: Review Awal, Tahap 1, Assign Asesor, Tahap 2, Visitasi, Scoring, Validasi Akhir, SK, Banding
- Panel SLA breach berbasis deadline
- Daftar antrian paling mendesak berdasarkan umur status
- Ringkasan workload asesor aktif
- Link cepat ke workflow console terfilter
- Export CSV dashboard

### 9.2 Akreditasi Console (Super Admin)

- List seluruh akreditasi dengan filter status, peringkat
- Detail pengajuan lengkap (semua tab: profil, IPM, SDM, EDPM, dokumen, audit)
- Action center: aksi workflow yang tersedia sesuai status saat ini
- Export: console, nilai/peringkat, status dokumen, workload asesor
- Action Menu (kebab): aksi tambahan + destructive actions

### 9.3 Workflow Admin

- **Review Awal:** Melihat data pengajuan, menerima/menolak dengan alasan
- **Buka Assessment:** Membuka masa asesmen dengan deadline
- **Review Tahap 1:** Review hasil assessment, approve/minta perbaikan/tolak
- **Assign Asesor:** Menunjuk Ketua + Anggota Asesor, reassign
- **Validasi Akhir:** Review hasil visitasi, NV override, approve/reject
- **Terbitkan SK:** Input nomor SK, masa berlaku, upload sertifikat

### 9.4 Workflow Asesor

**Ketua Asesor:**
- Review Tahap 2: review + approve/koreksi/tolak
- Jadwalkan Visitasi: tanggal, catatan
- Input NA1: Nilai Akhir dari ketua
- Input NK: Nilai Komponen
- Upload Laporan Kelompok
- Submit Hasil Visitasi

**Anggota Asesor:**
- Input NA2: Nilai Akhir individu
- Upload Laporan Individu

### 9.5 Workflow Pesantren

- Isi data: profil pesantren, IPM, SDM, EDPM
- Ajukan akreditasi
- Self-assessment (isi nilai EDPM per butir)
- Upload Kartu Kendali
- Koreksi (jika diminta Admin/Asesor)
- Lihat hasil akhir
- Download sertifikat
- Ajukan banding

### 9.6 Master Data (Super Admin)

- **Data Pesantren:** List, edit, lock/unlock, kelola EDPM
- **Role & Permission:** Matrix role-permission, export CSV
- **Users:** List, tambah, import, edit, detail, resend invite, SSO identity, reset/unlink SSO
- **Document Categories:** Kelola kategori, required phase, visibility rules
- **Master EDPM:** Komponen & butir penilaian

### 9.7 Settings (Super Admin)

| Kategori | Settings |
|---|---|
| Deadline | review_awal (5h), assessment (14h), review_tahap1 (5h), correction_tahap1 (7h), review_tahap2 (5h), correction_tahap2 (7h), scoring (7h), banding (7h) |
| Koreksi | max_siklus_tahap1 (2), max_siklus_tahap2 (2), action_on_limit (reject/auto_approve/freeze) |
| Dokumen | kartu_kendali_wajib_before, laporan_wajib_before |
| NV | nv_override_allowed, nv_reason_mode (collective/per_butir) |
| Notifikasi | superadmin_receives_admin_notif, reminder_days (7) |
| Banding | banding_eligibility (all/disabled) |

### 9.8 Audit Trail

- Log seluruh perubahan status + aksi penting
- Filter: akreditasi, actor, action type, tanggal
- Detail audit entry: from→to status, reason, metadata
- Export CSV
- Super Admin dapat melihat log global (termasuk tanpa akreditasi_id)

### 9.9 Notification Center (Super Admin)

- List seluruh notifikasi, filter: type, read/unread
- Mark read individual / mark all read
### 9.10 Assessor Workload Center

- List workload per asesor, detail asesor dengan assignment aktif
- Overload confirmation saat assign
- Export CSV

### 9.11 SK Management

- List seluruh SK, filter: nomor SK, pesantren
- Detail SK + sertifikat
- Export CSV

### 9.12 Visitasi Management

- List seluruh visitasi: terjadwal, selesai
- Detail visitasi: tanggal, catatan, laporan

---

