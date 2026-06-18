---
target: resources/views/superadmin/sk/index.blade.php
total_score: 38
p0_count: 0
p1_count: 0
timestamp: 2026-06-18T18-26-44Z
slug: resources-views-superadmin-sk-index-blade-php
---
# Critique: SK Management

## Design Health Score

| # | Heuristic | Score | Key Issue |
|---|-----------|-------|-----------|
| 1 | Visibility of System Status | 4 | State siap terbit dan sertifikat kini lebih terlihat; sisa gap kecil ada pada pemisahan urgency vs history dalam satu tabel yang sama. |
| 2 | Match System / Real World | 4 | Istilah dan tindakan sangat sesuai dengan alur administrasi SK akreditasi. |
| 3 | User Control and Freedom | 4 | Toolbar dan hero actions sekarang memberi jalur cepat yang lebih baik ke antrian publish-ready. |
| 4 | Consistency and Standards | 4 | Tetap konsisten dengan pola Metronic + Super Admin yang sudah ada. |
| 5 | Error Prevention | 3 | Expiry kini punya semantic emphasis; masih bisa ditingkatkan dengan grouping lebih kuat untuk state risiko. |
| 6 | Recognition Rather Than Recall | 4 | User lebih cepat mengenali item publish-ready tanpa harus membaca seluruh tabel. |
| 7 | Flexibility and Efficiency | 4 | Ada shortcut operasional yang lebih baik, terutama untuk fokus ke item siap terbit. |
| 8 | Aesthetic and Minimalist Design | 4 | Hierarki lebih kuat dan halaman terasa lebih seperti command surface daripada tabel generik. |
| 9 | Error Recovery | 4 | Empty state kini memberi recovery path yang jelas. |
| 10 | Help and Documentation | 3 | Framing penggunaan halaman sudah lebih baik, tetapi masih bisa dibuat sedikit lebih tegas soal perbedaan fungsi dengan workflow console. |
| **Total** | | **38/40** | **Strong and ship-ready** |

## Anti-Patterns Verdict

**LLM assessment**: Polish berhasil mengangkat halaman dari “tabel administratif aman” menjadi “command surface operasional” tanpa keluar dari system yang ada. Ia tetap tidak terlihat AI-generated, dan sekarang juga tidak terasa generik. Masih ada sedikit rasa campuran antara daftar histori dan daftar tindakan, tetapi itu sudah minor.

**Deterministic scan**: Detector tetap bersih untuk target file ini (`[]`). Tidak ada anti-pattern lokal yang terdeteksi pada markup polished view.

**Visual overlays**: Tidak dijalankan pada browser dalam pass ini, jadi tidak ada overlay user-visible yang bisa dirujuk.

## Overall Impression

Halaman ini sekarang jauh lebih meyakinkan. User bisa lebih cepat memahami kapan harus masuk ke SK Management, mana backlog yang siap diproses, mana item yang punya risiko tanggal/sertifikat, dan ke mana harus bergerak bila hasil filter kosong. Ini sudah mendekati bentuk yang layak kirim.

## What's Working

1. **Prioritas publish-ready sekarang jelas** — CTA hero, callout header, dan treatment row berhasil memberi fokus kerja yang sebelumnya hilang.
2. **Recovery path lengkap** — empty state tidak lagi buntu; user punya langkah berikutnya yang masuk akal.
3. **Semantic time state lebih kuat** — masa berlaku akhir sekarang punya arti operasional, bukan sekadar metadata tanggal.

## Priority Issues

- **[P2] Ready vs published masih hidup dalam satu ritme tabel**  
  **Why it matters**: Walau sudah membaik, command surface dan histori masih berbagi treatment dasar yang sama.  
  **Fix**: Jika ingin flagship, pertimbangkan pemisahan visual yang lebih tegas antara backlog tindakan dan arsip hasil.  
  **Suggested command**: `/impeccable layout resources/views/superadmin/sk/index.blade.php`

- **[P2] Sertifikat belum sepenuhnya jadi status operasional mandiri**  
  **Why it matters**: “Belum tersedia” sudah lebih baik, tetapi belum terasa seperti masalah yang punya severity berbeda dari sekadar metadata hilang.  
  **Fix**: Pertimbangkan aturan visual berbeda untuk completed tanpa sertifikat vs ready tanpa sertifikat.  
  **Suggested command**: `/impeccable colorize resources/views/superadmin/sk/index.blade.php`

- **[P3] Framing page purpose masih bisa dipertegas**  
  **Why it matters**: User baru masih bisa bertanya apakah harus mulai dari workflow console atau langsung dari SK Management.  
  **Fix**: Tambahkan satu kalimat yang lebih eksplisit tentang kapan halaman ini dipakai.  
  **Suggested command**: `/impeccable clarify resources/views/superadmin/sk/index.blade.php`

## Persona Red Flags

**Admin akreditasi operasional**: Sudah jauh lebih terbantu; ia bisa masuk ke backlog siap terbit lebih cepat. Sisa friction kecil hanya pada campuran item tindakan dan histori dalam satu scan.

**Super Admin pengawas**: Sekarang ia punya sinyal lebih baik untuk expiry dan coverage sertifikat, walau belum sepenuhnya dibedakan menurut severity operasional.

**Operator non-teknis internal**: Framing halaman lebih jelas dari sebelumnya, dan jalur lanjutannya lebih aman.

## Minor Observations

- Konsistensi lebar tombol aksi membantu scan kolom kanan.
- UUID sebagai metadata sekunder di kolom nomor SK adalah keputusan bagus.
- Badge “Belum tersedia” terasa lebih jujur daripada “Belum ada”.

## Questions to Consider

- Apakah SK Management sebaiknya tetap satu halaman campuran, atau nantinya dipisah jadi backlog publish vs histori SK?
- Kapan completed tanpa sertifikat dianggap warning biasa, dan kapan dianggap masalah operasional?
- Apakah user lebih sering datang untuk menerbitkan SK baru, atau untuk meninjau arsip SK yang sudah ada?
