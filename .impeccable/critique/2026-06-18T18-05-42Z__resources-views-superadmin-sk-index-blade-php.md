---
target: resources/views/superadmin/sk/index.blade.php
total_score: 29
p0_count: 0
p1_count: 2
timestamp: 2026-06-18T18-05-42Z
slug: resources-views-superadmin-sk-index-blade-php
---
# Critique: SK Management

## Design Health Score

| # | Heuristic | Score | Key Issue |
|---|-----------|-------|-----------|
| 1 | Visibility of System Status | 3 | Status SK dan availability sertifikat terlihat, tetapi state siap terbit vs sudah terbit masih terlalu setara secara visual. |
| 2 | Match System / Real World | 4 | Istilah SK, masa berlaku, sertifikat, dan aksi publish sesuai domain administrasi akreditasi. |
| 3 | User Control and Freedom | 3 | Filter dan reset ada, tetapi tidak ada jalur cepat dari toolbar ke state “siap terbit” atau “sudah terbit”. |
| 4 | Consistency and Standards | 4 | Mengikuti vocabulary Metronic + Super Admin pages dengan baik. |
| 5 | Error Prevention | 2 | Tidak ada indikator visual untuk SK kedaluwarsa/nyaris kedaluwarsa; user harus membaca tanggal manual. |
| 6 | Recognition Rather Than Recall | 3 | Badge dan nomor SK membantu, namun tabel memaksa user membaca beberapa kolom untuk memahami prioritas. |
| 7 | Flexibility and Efficiency | 3 | Sudah ada export dan filter, tapi belum ada shortcut operasional untuk antrian paling bernilai (publish-ready). |
| 8 | Aesthetic and Minimalist Design | 3 | Bersih dan rapi, tetapi masih terasa seperti tabel administratif generik; hierarchy bisa dipertegas. |
| 9 | Error Recovery | 2 | Empty state aman, tetapi belum mengarahkan user ke daftar siap terbit atau workflow terkait. |
| 10 | Help and Documentation | 2 | Halaman cukup jelas, tapi tidak memberi framing singkat tentang kapan user sebaiknya membuka halaman ini dibanding workflow console. |
| **Total** | | **29/40** | **Solid, but not yet flagship** |

## Anti-Patterns Verdict

**LLM assessment**: Halaman ini tidak memicu AI slop berat. Ia cukup disiplin terhadap system yang ada, tidak memakai gradient text, side-stripe gimmick, atau hero-metric template palsu. Masalahnya bukan “AI-looking”, tetapi terlalu aman: terasa seperti tabel admin yang benar namun belum mencapai kualitas operasional yang benar-benar tajam.

**Deterministic scan**: Detector bersih untuk target file ini (`[]`). Tidak ada anti-pattern lokal yang terdeteksi pada markup view SK Management. Itu bagus, tetapi clean detector bukan bukti bahwa UX sudah kuat.

**Visual overlays**: Tidak dijalankan pada browser dalam pass ini, jadi tidak ada overlay user-visible yang bisa dirujuk.

## Overall Impression

Ini adalah halaman yang fungsional, konsisten, dan sudah bisa dipakai, tetapi belum memberi rasa “command center” untuk penerbitan SK. Informasinya benar, komponen rapi, namun prioritas operasional—mana yang siap diproses sekarang, mana yang selesai tapi perlu audit, mana yang tidak punya sertifikat—masih tenggelam di dalam tabel.

## What's Working

1. **Vocabulary visual konsisten** — stat cards, badges, toolbar, dan tabel mengikuti pola halaman Super Admin lain sehingga pengguna tidak perlu belajar UI baru.
2. **Domain language tepat** — istilah seperti SK, masa berlaku, sertifikat, dan terbitkan SK langsung cocok dengan mental model admin akreditasi.
3. **CTA publish aman** — aksi “Terbitkan SK” hanya muncul pada status yang tepat, jadi affordance cukup terjaga dan tidak noisy.

## Priority Issues

- **[P1] Prioritas kerja belum cukup menonjol**  
  **Why it matters**: Halaman ini seharusnya membantu user melihat antrian publish tercepat, tetapi row “siap terbit” dan row “sudah selesai” masih terasa hampir setara.  
  **Fix**: Beri visual treatment yang lebih jelas untuk row ready-to-publish: grouping, ordering emphasis, atau kolom/summary cue yang membuat item publish-ready langsung menonjol.  
  **Suggested command**: `/impeccable layout resources/views/superadmin/sk/index.blade.php`

- **[P1] Tabel memaksa pembacaan horizontal untuk keputusan sederhana**  
  **Why it matters**: Untuk mengetahui apakah item siap aksi, user harus memindai status, nomor SK, sertifikat, lalu aksi. Ini meningkatkan beban scan.  
  **Fix**: Satukan sinyal keputusan ke blok yang lebih ringkas: misalnya metadata status + sertifikat dalam satu kolom summary atau action summary yang lebih padat.  
  **Suggested command**: `/impeccable distill resources/views/superadmin/sk/index.blade.php`

- **[P2] Empty state terlalu netral**  
  **Why it matters**: Saat hasil filter kosong, user hanya bisa reset. Tidak ada arahan ke workflow console atau status siap terbit, padahal ini halaman operasional.  
  **Fix**: Tambahkan recovery path kedua: kembali ke workflow console atau buka semua item siap terbit.  
  **Suggested command**: `/impeccable onboard resources/views/superadmin/sk/index.blade.php`

- **[P2] Hierarki toolbar kurang lengkap untuk command surface**  
  **Why it matters**: Toolbar hanya memberi Workflow Console dan Export CSV, padahal konteks kerja SK sering butuh jalan cepat ke backlog publish-ready.  
  **Fix**: Tambahkan jalur cepat yang lebih task-oriented, bukan hanya navigation-oriented.  
  **Suggested command**: `/impeccable clarify resources/views/superadmin/sk/index.blade.php`

- **[P2] State tanggal tidak punya semantic emphasis**  
  **Why it matters**: Masa berlaku akhir adalah data sensitif, tetapi tampil seperti metadata biasa. User bisa melewatkan item kedaluwarsa atau hampir kedaluwarsa.  
  **Fix**: Tambahkan semantic emphasis untuk state kedaluwarsa / mendekati akhir masa berlaku.  
  **Suggested command**: `/impeccable colorize resources/views/superadmin/sk/index.blade.php`

## Persona Red Flags

**Admin akreditasi operasional**: Ia butuh tahu item mana yang harus diterbitkan sekarang. Pada halaman ini, semua row terasa seragam sehingga ia tetap harus membaca tabel satu per satu.

**Super Admin pengawas**: Ia ingin memindai risiko dan coverage sertifikat. Tidak ada penekanan visual untuk item yang belum punya sertifikat atau masa berlaku yang riskan.

**Operator non-teknis pesantren/internal**: Filter dan hasil cukup jelas, tetapi halaman belum memberi penjelasan kapan harus memakai SK Management dibanding Workflow Console.

## Minor Observations

- Label “Belum ada” pada sertifikat aman, tetapi bisa dibuat lebih actionable.
- “Nilai” dan “Peringkat” cukup ringkas; ini sudah baik.
- Stat cards berguna, tetapi hubungan mereka dengan tabel di bawah belum kuat.

## Questions to Consider

- Kalau user hanya punya 30 detik, bagaimana halaman ini menunjukkan item publish-ready tanpa perlu membaca tabel penuh?
- Apakah SK Management ini lebih tepat sebagai daftar administratif, atau sebagai command surface untuk penerbitan SK?
- Mana yang lebih penting: melihat semua histori SK, atau menyelesaikan SK yang siap terbit hari ini?
