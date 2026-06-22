# Rencana Perbaikan Sistem Akreditasi PesantrenMu

> Disusun dari audit 2 file Excel referensi:
> - `MASTER LK Penggalian data dan Penilaian IAPM.xlsx` — template input & butir
> - `00 Rekap LK Akreditasi PM2025 untuk labsmu.xlsx` — data scoring aktual 9 pesantren
>
> **Tanggal:** 22 Juni 2026
> **Branch:** `feature/superadmin-governance-export`
> **Status:** ✅ SELESAI — 17 langkah + 5 gap Pesantren + formula fix

---

## Ringkasan

✅ 17 langkah perbaikan dalam 4 scope: Master Data, Scoring Engine, UI/UX Forms, Workflow Integration.
✅ 5 gap scope Pesantren (IPR, profil, dokumen, assessment gate, checklist).
✅ Perbaikan formula final score: NV weighted IK + IPR.
Berdasarkan temuan gap antara kode existing dengan referensi Excel akreditasi IAPM 2025.

---

## Alur Input Data Pesantren (Target)

```
PENGAJUAN AWAL                          ASSESSMENT TERBUKA
(draft_profile → initial_submitted)     (assessment_open → submit)

Profil Pesantren saja:                  IPM (4 butir binary: Sesuai/Tidak Sesuai)
├─ IDENTITAS PESANTREN (17 field)       EDPM (40 butir IAPM — self assessment + link bukti per butir)
├─ DATA PESANTREN (8 field)             IPR (22 butir — unggah dokumen pendukung)
├─ DOKUMEN UTAMA (7 upload + LK IAPM)   SDM (9 bentuk x 6 kategori)
└─ DOKUMEN SEKUNDER (4 upload)
```

---

## Struktur Scoring IAPM (dari Excel)

### 4 Komponen IAPM — 40 Butir

| Komponen | No SK | Sub Komponen | Butir | Bobot | Cmax |
|---|---|---|---|---|---|
| MUTU LULUSAN | 1 | Karakter (K) | 1, 6 | 35 | 32 |
| | 2 | Keilmuan Keislaman & Bahasa (KS) | 2, 5, 8 | | |
| | 3 | Kepemimpinan & Sosial (KS) | 3, 4, 7 | | |
| | 4 | Kemandirian & Kewirausahaan (KS) | — | | |
| PROSES PEMBELAJARAN | 1 | Kurikulum & Proses (KP) | 9-13, 18 | 29 | 40 |
| | 2 | Penilaian & Kompetensi (IB) | 14, 17 | | |
| | 3 | Karakteristik Pembelajar (IB) | 15, 16 | | |
| MUTU USTAZ | 1 | Pengembangan Profesional (PP) | 19, 20, 27, 28 | 18 | 40 |
| | 2 | Kompetensi Keislaman & Bahasa (KU) | 21-23 | | |
| | 3 | Karakter & Keteladanan (KU) | 24-26 | | |
| MANAJEMEN PESANTREN | 1 | Administrasi (VM/PPb) | 29-31 | 18 | 48 |
| | 2 | SDM & Program (KMd/BP) | 34, 36, 37, 39 | | |
| | 3 | Sarana (PSP) | 32, 33 | | |
| | 4 | Kemitraan & Jaminan Mutu (PMs/PK/PMI) | 35, 38, 40 | | |

### IPR — 22 Butir

Indikator Pemenuhan Relatif: kualifikasi S1/D4, ijazah pesantren, NBM, sertifikat, perpustakaan, asrama, masjid, lab bahasa, rumah dinas, Poskestren, dsb.

### Formula Scoring ✅

```
NA1 (Ketua Asesor) ─┐
                    ├──→ Delta = NA1 - NA2
NA2 (Anggota) ─────┘         │
                              ├── Delta = 0 → NK = NA1 (otomatis, readonly)
                              └── Delta != 0 → Ketua input NK manual (1-4)

NK ──→ NV default = NK (mirror)
         │
         └── Admin bisa override per-butir (NV_OVERRIDE_ALLOWED)

IK = weighted average 4 komponen dari NV (bobot: 35 + 29 + 18 + 18 = 100)
IPR = average 22 butir (skala 1-4)
NA = (0.7 x IK + 0.3 x IPR) x 25  ← FINAL SCORE

Peringkat:
  A (86-100): Unggul / Mumtaz
  B (71-85):  Baik Sekali / Jayyid Jiddan
  C (<70):    Baik / Jayyid
```

---

## SCOPE A — Master Data (6 langkah)

### A.1 ✅ — Migration: Tambah field ke MasterEdpmButir
**File:** migration `2026_06_22_021121` + `app/Models/MasterEdpmButir.php`

| Field | Type | Keterangan |
|---|---|---|
| `deskripsi` | text | Isi pernyataan butir dari Excel |
| `sub_komponen` | string, nullable | Kode sub komponen |
| `no_sk` | string, nullable | Nomor SK per komponen (1-4) |

**Commit:** `5d16a0c`

### A.2 ✅ — Seed 62 Butir Aktual
**File:** `database/seeders/MasterEdpmSeeder.php`

- 40 butir IAPM dari sheet EDPM kolom "Butir Pernyataan"
- 22 butir IPR dari sheet EDPM "B. INDIKATOR PEMENUHAN RELATIF"
- Setiap butir: kode, nama, deskripsi, sub_komponen, no_sk

**Commit:** `5d16a0c`

### A.3 ✅ — Model MasterEdpmSubKomponen
**File:** migration + model + seeder

18 sub komponen: K, KS, KP, IB, SP, PP, KU, PPs, VM, PPb, KMd, PSP, KpMd, PMs, BP, PK, PMI, Pengelolaan Alumni. + relasi `MasterEdpmButir.subKomponen()`

**Commit:** `3a122ab`

### A.4 ✅ — Validasi Struktur SDM JSON
**File:** `app/Http/Controllers/Pesantren/DataController.php`, `_sdm-fields.blade.php`

9 bentuk pendidikan x 6 kategori x L/P, nilai integer >= 0. Validasi di controller + form tabel.

**Commit:** `16dd3d9`

### A.5 ✅ — EDPM Form Per-Butir
**File:** `resources/views/pesantren/data/_edpm-fields.blade.php`

40 butir IAPM: self assessment dropdown (Sesuai/Perlu Perbaikan/Belum) + link bukti dokumen per butir. Grouping per komponen + sub komponen.

**Commit:** `5049b28`

### A.6 ✅ — IPR Form Per-Butir (Asesor)
**File:** `resources/views/asesor/ketua/input-ipr.blade.php`

22 butir IPR: input nilai skala 1-4 oleh Ketua Asesor/Super Admin.

**Commit:** `cbce91d`

---

## SCOPE B — Scoring Engine (3 langkah)

### B.1 ✅ — NK Input Manual (Delta-Based)
**File:** `app/Services/ScoringService.php`, `app/Services/AkreditasiWorkflowService.php`

- Delta = NA1 - NA2 per butir
- Delta = 0: NK auto-fill = NA1 (readonly di UI, server-side enforce)
- Delta != 0: ketua input NK manual (1-4)
- Simpan ke `AkreditasiEdpm` dengan type `nk`

**Commit:** `b21d02b`

### B.2 ✅ — Update Label Peringkat
**File:** `app/Services/ScoringService.php :: getPeringkatLabel()`

| Skor | Baru (Excel) |
|---|---|
| 86-100 (A) | Unggul / Mumtaz |
| 71-85 (B) | Baik Sekali / Jayyid Jiddan |
| <70 (C) | Baik / Jayyid |

**Commit:** `b21d02b`

### B.3 ✅ — NV: Mirror NK + Admin Override Per-Butir
**File:** `app/Services/ScoringService.php`, `app/Services/AkreditasiWorkflowService.php`

- Default: NV = NK (mirror)
- `NV_OVERRIDE_ALLOWED = true`: admin bisa override NV per-butir
- Override di `adminValidasiAkhir`, disimpan ke `AkreditasiEdpm` type `nv`
- Final score dari NV (bukan NK)

**Commit:** `d5668bb` (backend), `b21d02b` (UI), `5049b28` (delta logic)

---

## SCOPE C — UI/UX Forms (4 langkah)

### C.1 ✅ — Form IPM 4 Butir Binary
**File:** `resources/views/pesantren/data/_ipm-fields.blade.php`

4 butir IPM: Sesuai / Tidak Sesuai. Simpan ke `Ipm.data` JSON. Gate: semua harus "Sesuai" untuk `assessmentReady`.

**Commit:** `65023a1`

### C.2 ✅ — NA1/NA2: Tampilkan Deskripsi Butir
**File:** `resources/views/asesor/ketua/input-na1.blade.php`, `input-na2.blade.php`

- Label: `MasterEdpmButir.deskripsi` (bukan "Butir X")
- Grouping per komponen + sub komponen
- Sub komponen header ditampilkan

**Commit:** `e6fd0b3`

### C.3 ✅ — Form NK dengan Tabel Delta
**File:** `resources/views/asesor/ketua/input-nk.blade.php`

Tabel: Kode Butir | Deskripsi | NA1 | NA2 | Delta | NK input. Delta=0 → readonly + info "Otomatis", Delta!=0 → editable + "Perlu Input Manual".

**Commit:** `b21d02b`

### C.4 ✅ — Form NV Admin Override
**File:** `resources/views/admin/akreditasi/validasi-akhir.blade.php`

Tabel: Butir | NK | NV override (input 0-4). Muncul hanya jika toggle "Override NV" ON. Reason mode: collective/per_butir.

**Commit:** existing from `d5668bb`

---

## SCOPE D — Workflow Integration (3 langkah)

### D.1 ✅ — Pisahkan Gate Pengajuan vs Assessment
**File:** `app/Services/AkreditasiWorkflowService.php`

- `submitPengajuanAwal`: hanya cek `profilMinimum`
- `submitAssessment`: cek `assessmentReady` (IPM + EDPM + IPR + SDM)

**Commit:** `65023a1`

### D.2 ✅ — IPM Per-Butir Validation
**File:** `app/Services/PesantrenService.php :: checkDataCompleteness`

`assessmentReady = true` hanya jika 4 butir IPM = "Sesuai" semua + IPR 22 dokumen terunggah + SDM + EDPM.

**Commit:** `65023a1`, `c0a6ab5`

### D.3 ✅ — Wire NV Override
**File:** `app/Services/AkreditasiWorkflowService.php :: adminValidasiAkhir`

NV values dari form → controller → service. Override per-butir disimpan, final score dihitung dari NV + IPR.

**Commit:** `715cdda` (formula fix)

---

## ➕ BONUS: Perbaikan Scope Pesantren (5 Gap)

### Gap 1 ✅ — IPR Pesantren: Form 22 Butir Unggah Dokumen
**File:** Model `Ipr`, migration `iprs`, form `_ipr-fields.blade.php`, controller `updateIpr`

22 butir IPR: upload PDF per butir. Data disimpan ke `iprs` table.

**Commit:** `89b88bf`

### Gap 2 ✅ — Tambah Field nspp + persyarikatan
**File:** `_profile-fields.blade.php`, `DataController`

Field `nspp` (NSPP) dan `persyarikatan` ditambahkan ke form profil.

**Commit:** `22b528b`

### Gap 3 ✅ — Restruktur Dokumen: 7 Utama + 4 Sekunder + LK IAPM
**File:** `_profile-fields.blade.php`, `_document-fields.blade.php`

Dokumen diklasifikasi visual: 7 utama (wajib) + file_lk_iapm + 4 sekunder (pendukung).

**Commit:** `c53d7ae`, `3521d86`

### Gap 4 ✅ — assessmentReady Include IPR
**File:** `PesantrenService::checkDataCompleteness`

IPR 22 butir wajib semua terunggah untuk memenuhi `assessmentReady`.

**Commit:** `c0a6ab5`

### Gap 5 ✅ — Field status_kepemilikan_tanah
**File:** `_profile-fields.blade.php`

Dropdown: Milik Sendiri / Wakaf / Sewa / Pinjam Pakai.

**Commit:** `c0a6ab5`

---

## ➕ BONUS: Formula Final Score Fix

**Bug ditemukan:** Final score = `avg(SEMUA NV) x 25` — semua butir setara, IPR diabaikan.

**Setelah fix:**
```
IK = weighted avg 4 komponen dari NV (bobot 35/29/18/18)
IPR = avg 22 butir IPR
NA = (0.7 x IK + 0.3 x IPR) x 25  ← Final Score
```

**Commit:** `715cdda`

---

## ➕ BONUS: Cleanup Pesantren Scope

- `assessmentRules` sync ke struktur baru (sdm.butirs, edpm.butirs, ipr.butirs)
- `pesantrenData` + `storeAssessmentPayload` include IPR
- `koreksi.blade.php` refactor ke shared partials
- Hapus dead code `correction.blade.php`
- `assessment.blade.php` + `koreksi.blade.php` include IPR section
- Checklist kelengkapan data include IPR
- Exception message update

**Commit:** `f62de7f`, `0548590`, `148ae04`, `5838683`

---

## Urutan Eksekusi

```
✅  0.  Commit IPR existing work
✅  1.  A.1 Migration MasterEdpmButir
✅  2.  A.2 Seed 62 butir
✅  3.  A.3 MasterEdpmSubKomponen model
✅  4.  A.4 SDM validasi
✅  5.  A.5 EDPM form per-butir
✅  6.  A.6 IPR form per-butir
✅  7.  C.1 IPM form 4 butir
✅  8.  D.1 Pisahkan gate pengajuan/assessment
✅  9.  D.2 IPM per-butir validation
✅  10. B.2 Label peringkat
✅  11. B.1 NK delta + input manual
✅  12. B.3 NV override
✅  13. C.2 NA1/NA2 deskripsi
✅  14. C.3 NK delta UI
✅  15. C.4 NV override UI
✅  16. D.3 Wire NV ke adminValidasiAkhir
```

---

## Status Saat Ini

- **205/205 test PASS**
- **17/17 langkah rencana awal SELESAI**
- **5/5 gap Pesantren SELESAI**
- **Formula final score sudah sesuai Excel (NV weighted + IPR)**
- **ScoringService** fully aligned dengan formula Excel
- **Profil Pesantren** fields fully mapped ke model + form (25 field identitas/data + 14 field dokumen)
- **State machine** 20 status + 18 transisi aktif
- **Breadcrumb** di header toolbar Metronic dot
- **Dead code** dibersihkan (correction.blade.php)
- **Semua view** pakai shared partials (tidak ada hardcode struktur lama)
- **Branch:** `feature/superadmin-governance-export` — semua commit di-push ke origin

---

## Referensi

- `docs/flowchart-akreditasi.html` — Flowchart state machine
- `docs/SRS-PesantrenMu.md` — SRS document
- `C:\Users\LENOVO\Downloads\MASTER LK Penggalian data dan Penilaian IAPM.xlsx`
- `C:\Users\LENOVO\Downloads\00 Rekap LK Akreditasi PM2025 untuk labsmu.xlsx`
