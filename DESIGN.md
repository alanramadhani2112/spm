---
name: PesantrenMu
description: Sistem akreditasi pesantren berbasis dashboard operasional yang ramah, jelas, dan aman.
colors:
  primary: "#2e90fa"
  success: "#079455"
  info: "#7239ea"
  warning: "#dc6803"
  danger: "#d92d20"
  body-bg: "#ffffff"
  body-ink: "#181c32"
  neutral-100: "#f9f9f9"
  neutral-200: "#f4f4f4"
  neutral-300: "#e1e3ea"
  neutral-500: "#a1a5b7"
  neutral-700: "#5e6278"
  neutral-900: "#181c32"
typography:
  display:
    fontFamily: "Inter, Helvetica, sans-serif"
    fontSize: "2.25rem"
    fontWeight: 700
    lineHeight: 1.2
    letterSpacing: "-0.02em"
  headline:
    fontFamily: "Inter, Helvetica, sans-serif"
    fontSize: "1.5rem"
    fontWeight: 700
    lineHeight: 1.3
    letterSpacing: "-0.01em"
  title:
    fontFamily: "Inter, Helvetica, sans-serif"
    fontSize: "1rem"
    fontWeight: 600
    lineHeight: 1.4
    letterSpacing: "normal"
  body:
    fontFamily: "Inter, Helvetica, sans-serif"
    fontSize: "1rem"
    fontWeight: 400
    lineHeight: 1.5
    letterSpacing: "normal"
  label:
    fontFamily: "Inter, Helvetica, sans-serif"
    fontSize: "0.875rem"
    fontWeight: 600
    lineHeight: 1.4
    letterSpacing: "normal"
rounded:
  sm: "0.425rem"
  md: "0.475rem"
  lg: "0.625rem"
  xl: "1rem"
spacing:
  sm: "0.5rem"
  md: "1rem"
  lg: "1.5rem"
  xl: "2rem"
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "#ffffff"
    rounded: "{rounded.md}"
    padding: "0.625rem 1rem"
  button-light:
    backgroundColor: "{colors.neutral-100}"
    textColor: "{colors.neutral-900}"
    rounded: "{rounded.md}"
    padding: "0.625rem 1rem"
  card-surface:
    backgroundColor: "{colors.body-bg}"
    textColor: "{colors.body-ink}"
    rounded: "{rounded.md}"
    padding: "{spacing.lg}"
  badge-light-primary:
    backgroundColor: "#cfe2ff"
    textColor: "#0a58ca"
    rounded: "{rounded.sm}"
    padding: "0.35rem 0.65rem"
---

# Design System: PesantrenMu

## 1. Overview

**Creative North Star: "The Operational Courtyard"**

PesantrenMu memakai visual system dashboard operasional yang sangat familiar: layout aplikasi dengan sidebar gelap, content area terang, kartu statistik, tabel administratif, dan status badge berwarna. Sistem ini tidak berusaha tampil eksperimental; ia memprioritaskan keterbacaan, kecepatan orientasi, dan rasa aman untuk keputusan akreditasi yang sensitif.

Karakter visualnya datang dari Metronic-style admin UI yang dipadatkan untuk alur kerja: biru dipakai untuk tindakan utama, warna semantik lain dipakai disiplin untuk status, permukaan tetap putih atau abu sangat terang, dan tipografi sans-serif tunggal menjaga konsistensi antar form, tabel, alert, dan panel detail. Identitas produk terasa lewat kejelasan struktur dan ritme komponen, bukan lewat dekorasi.

Sistem ini secara eksplisit menolak tiga arah: dashboard SaaS generik yang terlalu glossy, antarmuka birokratis yang terasa kaku dan jauh dari pengguna pesantren, dan UI yang terlalu ramai atau ornamental. Kualitas terbaiknya muncul saat banyak informasi tetap terasa tertib, dapat dipindai, dan tidak melelahkan.

**Key Characteristics:**
- Dashboard-first, workflow-centric hierarchy.
- Light content surfaces with a dark structural shell.
- Strong semantic status colors, restrained decorative color.
- Single-family sans-serif typography with compact hierarchy.
- Cards, tables, filters, and alerts tuned for operational clarity.

## 2. Colors

Palette ini adalah product palette yang restrained: satu aksen utama untuk tindakan, satu neutral stack yang dominan, lalu warna semantik untuk status dan prioritas operasional.

### Primary
- **Operational Blue** (`#2e90fa`): dipakai untuk CTA utama, progress emphasis, link utama, dan status yang butuh dorongan tindakan tetapi tidak berbahaya.

### Secondary
- **Signal Violet** (`#7239ea`): dipakai untuk info state dan highlight yang lebih sekunder, terutama ketika primary blue sudah dipakai untuk aksi utama.

### Tertiary
- **Status Green** (`#079455`): dipakai untuk selesai, berhasil, approved, dan state yang menandakan stabilitas hasil.

### Neutral
- **Paper White** (`#ffffff`): permukaan konten utama dan latar form.
- **Soft Panel Gray** (`#f9f9f9`): panel ringan, button light, subtle card tint, dan empty-state support.
- **Divider Gray** (`#e1e3ea`): border, separator, field outline, dan pembatas struktur.
- **Muted Interface Gray** (`#a1a5b7`): teks sekunder yang benar-benar pendukung, bukan body utama.
- **Readable Slate** (`#5e6278`): teks deskriptif, helper text, dan label sekunder yang tetap perlu terbaca jelas.
- **Ink Navy** (`#181c32`): body text, heading, angka utama, dan kontras tertinggi.

### Semantic Colors
- **Approval Green** (`#079455`): success, completion, selesai.
- **Attention Amber** (`#dc6803`): review, warning, pending action, butuh tindak lanjut.
- **Critical Red** (`#d92d20`): reject, destructive action, overdue berisiko.
- **Information Violet** (`#7239ea`): info, supporting signal, auxiliary emphasis.

### Named Rules
**The Status-First Rule.** Accent color utama tidak dipakai dekoratif. Blue untuk aksi utama, semantic colors untuk arti status. Jika sebuah warna tidak menambah makna tindakan atau status, jangan dipakai.

## 3. Typography

**Display Font:** Inter, Helvetica, sans-serif  
**Body Font:** Inter, Helvetica, sans-serif  
**Label/Mono Font:** Inter for labels; default monospace only for UUID, code, and machine-readable values.

**Character:** Tipografi dibuat untuk dashboard dan admin tools, bukan editorial surface. Inter dipakai konsisten di seluruh permukaan agar heading, angka statistik, label form, isi tabel, dan helper text terasa satu sistem yang familiar.

### Hierarchy
- **Display** (700, 2.25rem, 1.2): page title utama, angka hero di stat cards, ringkasan operasional yang paling penting.
- **Headline** (700, 1.5rem, 1.3): section title besar seperti “Workflow Console Akreditasi”, “SK Management”, atau “Operational Board Visitasi”.
- **Title** (600, 1rem, 1.4): judul card, label blok, dan penanda konten sekunder yang masih penting.
- **Body** (400, 1rem, 1.5): isi utama, penjelasan status, narasi pendek di card dan detail page.
- **Label** (600, 0.875rem, 1.4): label form, badge text, toolbar microcopy, helper text yang membutuhkan ketegasan kecil.

### Named Rules
**The No-Drama Type Rule.** Typography tidak boleh terasa bergaya melebihi tugasnya. Tidak ada display font dekoratif untuk label, tombol, atau data. Kejelasan operasional selalu menang.

## 4. Elevation

Sistem ini memakai hybrid elevation ringan: sebagian besar depth datang dari struktur layout, border halus, dan pemisahan permukaan terang terhadap shell yang lebih gelap. Bayangan dipakai lembut untuk memberikan pemisahan card dari background, bukan untuk efek dramatis atau glassmorphism.

### Shadow Vocabulary
- **Base Surface Lift** (`0 0.5rem 1.5rem 0.5rem rgba(0, 0, 0, 0.075)`): bayangan utama untuk card dan container penting.
- **Compact Surface Lift** (`0 0.1rem 1rem 0.25rem rgba(0, 0, 0, 0.05)`): bayangan ringan untuk form field, compact panel, atau komponen yang butuh pemisahan tipis.
- **Inset Field Depth** (`inset 0 1px 2px rgba(0, 0, 0, 0.075)`): dipakai pada beberapa kontrol agar field terasa tactile tanpa menjadi berat.

### Named Rules
**The Flat-Enough Rule.** Permukaan default harus tetap ringan. Jika border + tonal separation sudah cukup, jangan tambahkan shadow besar.

## 5. Components

### Buttons
- **Shape:** rounded kecil-menengah (`0.475rem`) dengan rasa product UI modern yang aman.
- **Primary:** biru solid, teks putih, padding ringkas, dipakai untuk CTA utama seperti publish, submit, atau apply filter penting.
- **Light:** surface abu terang dengan teks gelap, dipakai untuk navigasi sekunder, reset, atau back action.
- **Hover / Focus:** perubahan halus pada background/border dan focus ring lembut; tidak memakai motion besar atau transform dekoratif.

### Badges / Status Pills
- **Style:** light-tint background dengan text color yang lebih gelap dari hue yang sama.
- **Usage:** semua status workflow, state statistik, dan filter aktif harus memakai badge vocabulary yang konsisten.
- **Rule:** badge harus informatif, bukan dekoratif; warna badge membawa arti yang konsisten lintas halaman.

### Cards / Containers
- **Corner Style:** radius kecil-menengah (`0.475rem` sampai `0.625rem`).
- **Background:** putih atau tinted-neutral sangat terang.
- **Border:** tipis, abu netral, sering dipadukan dengan dashed border pada highlight operational cards.
- **Internal Padding:** umumnya 1.5rem–2rem tergantung pentingnya blok.

### Tables
- **Style:** tabel administratif dengan row borders tegas, alignment vertikal yang rapi, dan kolom aksi di kanan.
- **Density:** cukup padat untuk data operasional, tetapi masih memberi ruang pada helper text dan status badges.
- **Hierarchy:** nama entitas + metadata kecil di bawahnya adalah pola yang sering dipakai.

### Inputs / Fields
- **Style:** background putih, border abu terang, radius `0.475rem`, shadow sangat ringan atau none.
- **Focus:** focus ring lembut berbasis accent color, bukan outline browser default yang hilang tanpa pengganti.
- **Error / Disabled:** error memakai semantic red; disabled state tetap kontras dan terbaca, tidak washed out.

### Navigation
- **Shell:** sidebar gelap sebagai struktur tetap, content area terang untuk kerja detail.
- **Toolbar:** tombol kecil (`btn-sm`) dengan kepadatan tinggi agar aksi navigasi cepat diakses.
- **Pattern:** dashboard → workflow console → detail/action page adalah alur utama yang harus terasa konsisten.

### Stat Cards
- **Style:** angka besar, ikon semantik, label sekunder, kadang progress bar tipis.
- **Role:** memberi orientasi cepat pada prioritas operasional, bukan vanity metrics.
- **Behavior:** satu glance harus cukup untuk tahu apa yang perlu ditindaklanjuti.

## 6. Do's and Don'ts

### Do:
- **Do** pakai `Ink Navy` (`#181c32`) untuk body text dan angka penting agar keterbacaan tetap kuat.
- **Do** pertahankan pola `badge-light-*` untuk status, dengan arti warna konsisten lintas dashboard, console, dan detail.
- **Do** gunakan `btn btn-sm` dan card spacing yang sudah ada agar toolbar dan action area tetap seragam.
- **Do** pakai tabel administratif sebagai pola utama untuk list operasional besar seperti akreditasi, SK, workload, dan audit.
- **Do** jadikan “langkah berikutnya” dan aksi utama mudah dipindai di setiap halaman workflow.

### Don't:
- **Don't** menambahkan side-stripe border tebal sebagai aksen card, callout, atau list item.
- **Don't** memakai gradient text, glassmorphism, atau visual effect trend-driven yang tidak membantu tugas operasional.
- **Don't** membuat dashboard terlihat seperti SaaS generik penuh glow, chip dekoratif, dan hero metric yang tidak bermakna.
- **Don't** membuat interface terlalu kaku atau terasa seperti sistem birokrasi dingin; helper text dan empty state tetap harus terasa membantu.
- **Don't** mengganti vocabulary komponen secara lokal (misalnya satu halaman punya tombol, badge, atau field style sendiri yang tidak dipakai di tempat lain).
