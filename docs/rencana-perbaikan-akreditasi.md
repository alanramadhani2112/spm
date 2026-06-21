# Rencana Perbaikan Sistem Akreditasi PesantrenMu

> Disusun dari audit 2 file Excel referensi:
> - `MASTER LK Penggalian data dan Penilaian IAPM.xlsx` — template input & butir
> - `00 Rekap LK Akreditasi PM2025 untuk labsmu.xlsx` — data scoring aktual 9 pesantren
>
> **Tanggal:** 22 Juni 2026
> **Branch:** `feature/superadmin-governance-export`

---

## Ringkasan

17 langkah perbaikan dalam 4 scope: Master Data, Scoring Engine, UI/UX Forms, Workflow Integration.
Berdasarkan temuan gap antara kode existing dengan referensi Excel akreditasi IAPM 2025.

---

## Alur Input Data Pesantren (Target)

```
PENGAJUAN AWAL                          ASSESSMENT TERBUKA
(draft_profile → initial_submitted)     (assessment_open → submit)

Profil Pesantren saja:                  IPM (4 butir binary: Sesuai/Tidak Sesuai)
├─ IDENTITAS PESANTREN (14 field)       EDPM (40 butir IAPM — self assessment + link bukti per butir)
├─ DATA PESANTREN (11 field)            IPR (22 butir — unggah dokumen pendukung)
├─ DOKUMEN UTAMA (7 upload)             SDM (9 bentuk x 6 kategori)
└─ DOKUMEN SEKUNDER (11 upload)
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

### Formula Scoring

```
NA1 (Ketua Asesor) ─┐
                    ├──→ Delta = NA1 - NA2
NA2 (Anggota) ─────┘         │
                              ├── Delta = 0 → NK = NA1
                              └── Delta != 0 → Ketua input NK manual (1-4)

NK ──→ NV default = NK (mirror)
         │
         └── Admin bisa override per-butir (NV_OVERRIDE_ALLOWED)

IK = weighted average 4 komponen (bobot: 35 + 29 + 18 + 18 = 100)
IPR = average 22 butir (skala 1-4)
NA = (0.7 x IK + 0.3 x IPR) x 25

Peringkat:
  A (86-100): Unggul / Mumtaz
  B (71-85):  Baik Sekali / Jayyid Jiddan
  C (<70):    Baik / Jayyid
```

---

## SCOPE A — Master Data (6 langkah)

### A.1 — Migration: Tambah field ke MasterEdpmButir
**File:** migration + `app/Models/MasterEdpmButir.php`

| Field | Type | Keterangan |
|---|---|---|
| `deskripsi` | text | Isi pernyataan butir dari Excel |
| `sub_komponen` | string, nullable | Kode 14 sub komponen |
| `no_sk` | string, nullable | Nomor SK per komponen (1-4) |

### A.2 — Seed 62 Butir Aktual
**File:** `database/seeders/MasterEdpmSeeder.php`

- 40 butir IAPM dari sheet EDPM kolom "Butir Pernyataaan"
- 22 butir IPR dari sheet EDPM "B. INDIKATOR PEMENUHAN RELATIF"
- Setiap butir: kode, nama, deskripsi, sub_komponen, no_sk

### A.3 — Model MasterEdpmSubKomponen
**File:** migration + model + seeder

14 sub komponen: K, KS, KP, IB, SP, PP, KU, PPs, VM, PPb, KMd, PSP, KpMd, PMs, BP, PK, PMI

### A.4 — Validasi Struktur SDM JSON
**File:** `app/Http/Controllers/Pesantren/DataController.php`

Validation rule untuk struktur SDM: 9 bentuk pendidikan x 12 sub-key (L/P per kategori), nilai integer >= 0.

### A.5 — EDPM Form Per-Butir
**File:** `resources/views/pesantren/data/_edpm-fields.blade.php` (refactor)

40 butir IAPM: self assessment text + link bukti dokumen per butir. Grouping per komponen + sub komponen.

### A.6 — IPR Form Per-Butir
**File:** `resources/views/pesantren/data/_ipr-fields.blade.php` (new)

22 butir IPR: unggah dokumen pendukung per butir.

---

## SCOPE B — Scoring Engine (3 langkah)

### B.1 — NK Input Manual (Delta-Based)
**File:** `app/Services/ScoringService.php`, `app/Http/Controllers/Asesor/KetuaAsesorController.php`

- Hitung delta = NA1 - NA2 per butir
- Delta = 0: NK auto-fill = NA1
- Delta != 0: ketua input NK manual (1-4)
- Simpan ke `AkreditasiEdpm` dengan type `nk`

### B.2 — Update Label Peringkat
**File:** `app/Services/ScoringService.php :: getPeringkatLabel()`

| Skor | Lama | Baru (Excel) |
|---|---|---|
| 86-100 (A) | Unggul | Unggul / Mumtaz |
| 71-85 (B) | Baik | Baik Sekali / Jayyid Jiddan |
| <70 (C) | Cukup | Baik / Jayyid |

### B.3 — NV: Mirror NK + Admin Override Per-Butir
**File:** `app/Services/ScoringService.php`, `app/Services/AkreditasiWorkflowService.php`

- Default: NV = NK (mirror) — `calculateNvDefault` sudah benar
- Kalau `NV_OVERRIDE_ALLOWED = true`: admin bisa override NV per-butir
- Override disimpan ke `AkreditasiEdpm` dengan type `nv`
- Scoring pakai NV (bukan NK)

---

## SCOPE C — UI/UX Forms (4 langkah)

### C.1 — Form IPM 4 Butir Binary
**File:** `resources/views/pesantren/ipm/index.blade.php` (new)

4 butir IPM: Sesuai / Tidak Sesuai (checkbox/radio). Simpan ke `Ipm.data` JSON.

### C.2 — NA1/NA2: Tampilkan Deskripsi Butir
**File:** `resources/views/asesor/ketua/input-na1.blade.php`, `input-na2.blade.php`

- Ganti label generik "Butir 1" -> `MasterEdpmButir.deskripsi`
- Grouping per komponen + sub komponen
- Tampilkan EDPM pesantren sebagai referensi

### C.3 — Form NK dengan Tabel Delta
**File:** `resources/views/asesor/ketua/input-nk.blade.php`

Tabel: Butir | Deskripsi | NA1 | NA2 | Delta | NK input. Delta=0 readonly, Delta!=0 editable.

### C.4 — Form NV Admin Override
**File:** `resources/views/admin/akreditasi/validasi-akhir.blade.php`

Tabel: Butir | NK | NV override (optional). Muncul hanya jika `NV_OVERRIDE_ALLOWED=true`. Reason mode: collective/individual.

---

## SCOPE D — Workflow Integration (3 langkah)

### D.1 — Pisahkan Gate Pengajuan vs Assessment
**File:** `app/Services/PesantrenService.php`

- `submitPengajuanAwal`: hanya cek `profilMinimum` (identitas + layanan + dokumen)
- `submitAssessment`: cek `assessmentReady` (IPM 4 butir + EDPM 40 butir + IPR 22 butir + SDM)
- Profile lock tetap di `submitPengajuan`

### D.2 — IPM Per-Butir Validation
**File:** `app/Services/PesantrenService.php :: checkDataCompleteness`

`assessmentReady = true` hanya jika 4 butir IPM = "Sesuai" semua. Bukan sekadar exists.

### D.3 — Wire NV Override
**File:** `app/Services/AkreditasiWorkflowService.php :: adminValidasiAkhir`

Parameter `$nvValues` dan `$nvReasons` sudah ada — wire ke `ScoringService` untuk override per-butir sebelum scoring final.

---

## Urutan Eksekusi

```
0.  Commit IPR existing work
1.  A.1 Migration MasterEdpmButir
2.  A.2 Seed 62 butir
3.  A.3 MasterEdpmSubKomponen model
4.  A.4 SDM validasi
5.  A.5 EDPM form per-butir
6.  A.6 IPR form per-butir
7.  C.1 IPM form 4 butir
8.  D.1 Pisahkan gate pengajuan/assessment
9.  D.2 IPM per-butir validation
10. B.2 Label peringkat
11. B.1 NK delta + input manual
12. B.3 NV override
13. C.2 NA1/NA2 deskripsi
14. C.3 NK delta UI
15. C.4 NV override UI
16. D.3 Wire NV ke adminValidasiAkhir
```

---

## Status Saat Ini

- **205/205 test PASS**
- **ScoringService** sudah fully aligned dengan formula Excel
- **Profil Pesantren** fields sudah fully mapped ke model
- **State machine** 20 status + 18 transisi aktif
- **5 gap P1-P3** sudah difix (unlock profile, rejection record, stage 2 limit, banding count, cancelled status)
- **Breadcrumb** sudah dipindah ke header dengan style Metronic dot
- **IPR form** (uncommitted) dalam pengerjaan

---

## Referensi

- `docs/flowchart-akreditasi.html` — Flowchart state machine
- `docs/SRS-PesantrenMu.md` — SRS document
- `C:\Users\LENOVO\Downloads\MASTER LK Penggalian data dan Penilaian IAPM.xlsx`
- `C:\Users\LENOVO\Downloads\00 Rekap LK Akreditasi PM2025 untuk labsmu.xlsx`