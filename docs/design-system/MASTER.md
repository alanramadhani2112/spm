# Sistem Desain Frontend SPM — Metronic

Dokumen ini menjadi acuan UI/UX untuk pengembangan frontend `sistem-pesantrenmu`. Tujuannya menjaga seluruh halaman konsisten, mudah dipahami, aksesibel, dan tetap mengikuti pola Metronic.

## Prinsip Utama

1. **Accessibility first**
   - Semua input wajib memiliki label yang terlihat.
   - Tombol icon-only wajib memiliki `aria-label`.
   - Jangan mengandalkan warna saja untuk status; selalu sertakan teks/status badge.
   - Focus state bawaan Bootstrap/Metronic tidak boleh dihapus.
   - Error validasi harus muncul dekat field dan, untuk form panjang, juga muncul sebagai summary di atas form.

2. **Satu halaman, satu aksi utama**
   - Primary CTA ditempatkan di toolbar/header kanan.
   - Aksi sekunder memakai `btn-light-*`.
   - Aksi berbahaya memakai `btn-light-danger` atau `btn-danger`, dipisah secara visual, dan wajib confirmation.

3. **Gunakan Metronic, bukan custom style acak**
   - Prioritaskan class Metronic/Bootstrap: `card`, `card-flush`, `row g-5 g-xl-8`, `badge badge-light-*`, `btn btn-*`, `form-control-solid`, `form-select-solid`.
   - Hindari inline color dan hardcoded CSS kecuali benar-benar diperlukan.
   - Gunakan icon Metronic/KeenIcons secara konsisten: `ki-outline ...`.

4. **Readable sebelum padat**
   - Admin page tidak boleh hanya berupa tabel mentah.
   - Beri konteks: page intro, helper text, summary cards, dan empty state.
   - Data kompleks harus dikelompokkan berdasarkan konteks, bukan ditumpuk satu tabel besar tanpa hierarki.

5. **Responsive by default**
   - Layout utama memakai grid `row g-5 g-xl-8`.
   - Form panjang collapse rapi ke satu kolom di mobile.
   - Tabel besar harus berada dalam `.table-responsive`.
   - Hindari fixed width kecuali untuk elemen kecil seperti kode/badge.

## Struktur Standar Halaman

Setiap halaman Super Admin sebaiknya mengikuti pola ini:

```text
Page Header
  - Judul jelas
  - Deskripsi singkat
  - Primary CTA / Back button

Feedback
  - Success alert
  - Error summary jika ada validation error

Summary / Stats
  - 3–5 stat-card jika relevan

Guidance / Context
  - Callout singkat: fungsi halaman dan cara pakai

Main Content
  - Form, filter, tabel, detail, atau workflow step

Empty State
  - Pesan jelas + CTA relevan
```

## Komponen dan Pola

### Page Header

Gunakan header untuk menjawab: “halaman ini untuk apa?”

```blade
<div class="d-flex flex-wrap justify-content-between align-items-start gap-4 mb-8">
    <div>
        <h2 class="fs-2 fw-bold text-gray-900 mb-2">Judul Halaman</h2>
        <p class="fs-7 text-muted mb-0">Deskripsi singkat fungsi halaman.</p>
    </div>
    <a href="..." class="btn btn-sm btn-light">Kembali</a>
</div>
```

### Summary Cards

Gunakan maksimal 4–5 kartu ringkasan. Jangan menampilkan angka yang tidak actionable.

```blade
<x-metronic.stat-card value="{{ $count }}" label="Label" icon="ki-document" color="primary" />
```

Warna standar:

| Makna | Warna Metronic |
| --- | --- |
| Informasi utama | `primary` |
| Berhasil/aktif/selesai | `success` |
| Perlu perhatian | `warning` |
| Berbahaya/gagal/overdue | `danger` |
| Informasi pendukung | `info` |
| Netral/nonaktif | `secondary` |

### Forms

Checklist form:

- Label terlihat untuk setiap field.
- Required field diberi tanda jelas.
- Placeholder hanya contoh, bukan pengganti label.
- Helper text untuk field yang butuh penjelasan.
- `old()` digunakan agar input tidak hilang setelah validasi gagal.
- Submit button jelas dan ditempatkan konsisten.
- File upload mencantumkan format dan batas ukuran.

Pola error summary:

```blade
@if($errors->any())
    <x-metronic.alert type="danger">
        <div class="fw-semibold mb-2">Periksa kembali input:</div>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-metronic.alert>
@endif
```

### Tables dan Lists

- Tabel wajib memiliki header yang jelas.
- Status memakai badge dengan teks.
- Aksi utama dan aksi destruktif dipisah.
- Tombol aksi jangan hanya icon kecil.
- Empty state harus memberi arahan berikutnya.
- Untuk data hierarkis, gunakan grouping/card list daripada tabel datar.

### Badges

Gunakan badge untuk mempercepat scanning, tapi jangan hanya mengandalkan warna.

```blade
<span class="badge badge-light-success">Aktif</span>
<span class="badge badge-light-warning">Butuh Review</span>
<span class="badge badge-light-danger">Ditolak</span>
```

### Empty State

Empty state harus menjawab:

1. Apa yang kosong?
2. Kenapa ini penting?
3. Apa langkah berikutnya?

```blade
<div class="text-center py-12 text-muted border rounded bg-light">
    Belum ada data. Tambahkan data pertama dari form di sebelah kiri.
</div>
```

## Standar Halaman Master Data

### Master EDPM

- Tampilkan hierarchy: `Komponen → Butir → Penilaian`.
- Komponen kosong harus terlihat dengan badge warning.
- Butir sebaiknya dikelompokkan per komponen.
- Form tambah komponen dan butir harus memiliki helper text.

### Document Categories

- Harus menampilkan aturan akses dokumen, bukan hanya nama kategori.
- Setiap kategori minimal menjelaskan:
  - nama dokumen
  - kode dokumen
  - fase wajib
  - role yang bisa mengakses
  - cakupan asesor bila role `asesor`
  - status aktif
  - ada/tidaknya template
- Preset umum:
  - Kartu Kendali → Pesantren
  - Laporan Visitasi Individu → Asesor, cakupan Ketua & Anggota
  - Laporan Visitasi Kelompok → Asesor, cakupan Ketua Asesor

## Standar Workflow Akreditasi

- Status workflow harus terlihat di header/detail.
- Action center harus hanya menampilkan aksi yang relevan dengan status saat ini.
- Aksi destructive seperti tolak/final reject harus dipisah dan diberi alasan wajib.
- Timeline/audit log harus mudah discan berdasarkan waktu, aktor, dan perubahan status.

## Checklist Sebelum Merge UI

### Accessibility

- [ ] Semua input punya label.
- [ ] Icon-only button punya `aria-label`.
- [ ] Focus state terlihat.
- [ ] Status tidak hanya berdasarkan warna.
- [ ] Error validasi jelas dan dekat field.

### Interaction

- [ ] Primary CTA jelas.
- [ ] Destructive action terpisah dan dikonfirmasi.
- [ ] Tombol cukup besar untuk touch target.
- [ ] Tidak ada aksi penting yang hover-only.

### Layout

- [ ] Desktop, tablet, dan mobile tidak rusak.
- [ ] Tidak ada horizontal overflow yang tidak disengaja.
- [ ] Spacing konsisten memakai grid Metronic.
- [ ] Empty state tersedia.

### Visual Consistency

- [ ] Menggunakan komponen/class Metronic.
- [ ] Badge warna konsisten dengan makna.
- [ ] Icon family konsisten.
- [ ] Tidak ada inline color acak.

### Laravel/Blade

- [ ] `old()` dipakai untuk form input.
- [ ] CSRF/method spoofing benar.
- [ ] Route names digunakan, bukan hardcoded URL.
- [ ] Tests relevan ditambahkan/diperbarui.
