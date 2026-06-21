<?php

namespace Database\Seeders;

use App\Models\MasterEdpmButir;
use App\Models\MasterEdpmKomponen;
use App\Services\ScoringService;
use Illuminate\Database\Seeder;

class MasterEdpmSeeder extends Seeder
{
    public function run(): void
    {
        // Komponen IAPM (4)
        $ikKomponen = [];
        foreach (ScoringService::KOMPONEN_CONFIG as $key => $config) {
            $name = str_replace('_', ' ', $key);
            $name = ucwords(strtolower($name));
            $komponen = MasterEdpmKomponen::firstOrCreate(
                ['id' => $config['id']],
                ['kode' => $key, 'name' => $name, 'nama' => $name]
            );
            $ikKomponen[$config['id']] = $komponen;
        }

        // Butir IAPM (40)
        $iapmButirs = [
            ['komponen_id' => 1, 'no' => '1.0', 'no_sk' => '1.0', 'sub_komponen' => '', 'deskripsi' => 'Menjadi pribadi yang bertaqwa (berakidah lurus; beribadah secara benar; dan berakhlak mulia)'],
            ['komponen_id' => 1, 'no' => '2.0', 'no_sk' => '2.0', 'sub_komponen' => 'KS', 'deskripsi' => 'Santri Mampu Membaca, Menghafal, dan Memahami Makna Al-Quran.'],
            ['komponen_id' => 1, 'no' => '3.0', 'no_sk' => '3.0', 'sub_komponen' => 'KS', 'deskripsi' => 'Santri Mampu Menjadi Pendidik, Muballigh, dan Imam Shalat.'],
            ['komponen_id' => 1, 'no' => '4.0', 'no_sk' => '3.0', 'sub_komponen' => 'KS', 'deskripsi' => 'Santri Memiliki Kompetensi Kepemimpinan, Kekaderan dan Keorganisasian IPM, HW, dan Tapak Suci'],
            ['komponen_id' => 1, 'no' => '5.0', 'no_sk' => '2.0', 'sub_komponen' => 'KS', 'deskripsi' => 'Mahir Berbahasa Arab dan Inggris'],
            ['komponen_id' => 1, 'no' => '6.0', 'no_sk' => '4.0', 'sub_komponen' => 'K', 'deskripsi' => 'Santri berjiwa mandiri dan wirausaha.'],
            ['komponen_id' => 1, 'no' => '7.0', 'no_sk' => '3.0', 'sub_komponen' => 'KS', 'deskripsi' => 'Memiliki Keterampilan Sosial dan Public Speaking'],
            ['komponen_id' => 1, 'no' => '8.0', 'no_sk' => '2.0', 'sub_komponen' => 'KS', 'deskripsi' => 'Memiliki Keterampilan Berkemajuan (Menguasai Kutub Turats, IPTEK, TIK, dan Jejaring)'],
            ['komponen_id' => 2, 'no' => '9.0', 'no_sk' => '1.0', 'sub_komponen' => 'KP', 'deskripsi' => 'Proses Pembelajaran dilaksanakan secara holistik, integratif, dan HOTS'],
            ['komponen_id' => 2, 'no' => '10.0', 'no_sk' => '1.0', 'sub_komponen' => 'IB', 'deskripsi' => 'Pembelajaran yang Menerapkan Nilai-Nilai Keteladanan, Menumbuhkan Kemauan, dan Mengembangkan Kreativitas'],
            ['komponen_id' => 2, 'no' => '11.0', 'no_sk' => '1.0', 'sub_komponen' => 'SP', 'deskripsi' => 'Pemanfaatan TIK untuk Pembelajaran yang Efektif dan Efisien'],
            ['komponen_id' => 2, 'no' => '12.0', 'no_sk' => '1.0', 'sub_komponen' => 'IB', 'deskripsi' => 'Proses Pembelajaran Menggunakan Strategi, Model, dan Metode yang Aktif, Inovatif, Efektif, Menyenangkan, dan Menantang'],
            ['komponen_id' => 2, 'no' => '13.0', 'no_sk' => '1.0', 'sub_komponen' => 'KP', 'deskripsi' => 'Melakukan Pengayaan Kutub Turats dalam Proses Pembelajaran'],
            ['komponen_id' => 2, 'no' => '14.0', 'no_sk' => '2.0', 'sub_komponen' => 'KP', 'deskripsi' => 'Melakukan Penilaian Proses dan Hasil Sebagai Dasar Perbaikan yang Dilaksanakan Secara Sistematis'],
            ['komponen_id' => 2, 'no' => '15.0', 'no_sk' => '3.0', 'sub_komponen' => 'IB', 'deskripsi' => 'Santri sebagai Pembelajar Sepanjang Hayat'],
            ['komponen_id' => 2, 'no' => '16.0', 'no_sk' => '3.0', 'sub_komponen' => 'IB', 'deskripsi' => 'Santri Menunjukkan Sikap Tawadhu’ dan Ihtiram Kepada Ustadz'],
            ['komponen_id' => 2, 'no' => '17.0', 'no_sk' => '2.0', 'sub_komponen' => 'KP', 'deskripsi' => 'Peningkatan Soft Skills dan Hard Skills secara Seimbang.'],
            ['komponen_id' => 2, 'no' => '18.0', 'no_sk' => '1.0', 'sub_komponen' => 'KP', 'deskripsi' => 'Menggunakan Bahasa Asing Sebagai Bahasa Pengantar'],
            ['komponen_id' => 3, 'no' => '19.0', 'no_sk' => '1.0', 'sub_komponen' => 'PP', 'deskripsi' => 'Ustadz Melakukan Evaluasi Diri, Refleksi dan Perbaikan Kinerja Secara Berkala dan Terukur'],
            ['komponen_id' => 3, 'no' => '20.0', 'no_sk' => '1.0', 'sub_komponen' => 'PP', 'deskripsi' => 'Pengembangan Kompetensi Ustadz Secara Berkelanjutan'],
            ['komponen_id' => 3, 'no' => '21.0', 'no_sk' => '2.0', 'sub_komponen' => 'KU', 'deskripsi' => 'Ustadz yang Mengampu Dirasah Islamiyah Memiliki Kemampuan Berbahasa Arab Secara Aktif'],
            ['komponen_id' => 3, 'no' => '22.0', 'no_sk' => '2.0', 'sub_komponen' => 'KU', 'deskripsi' => 'Ustadz Mampu Menggunakan Pengantar Bahasa Arab/Inggris dalam Pembukaan dan Penutupan Pembelajaran.'],
            ['komponen_id' => 3, 'no' => '23.0', 'no_sk' => '2.0', 'sub_komponen' => 'KU', 'deskripsi' => 'Ustadz Memiliki Pemahaman Tentang Karakteristik Warga Muhammadiyah dan Mampu Mengamalkannya.'],
            ['komponen_id' => 3, 'no' => '24.0', 'no_sk' => '3.0', 'sub_komponen' => 'PPs', 'deskripsi' => 'Aktif dalam Kegiatan Persyarikatan Muhammadiyah'],
            ['komponen_id' => 3, 'no' => '25.0', 'no_sk' => '3.0', 'sub_komponen' => 'PPs', 'deskripsi' => 'Ustadz Melakukan Pengembangan dan Pembiasaan Hidup Islami.'],
            ['komponen_id' => 3, 'no' => '26.0', 'no_sk' => '3.0', 'sub_komponen' => 'KU', 'deskripsi' => 'Ustadz Menjadi Uswatun Hasanah'],
            ['komponen_id' => 3, 'no' => '27.0', 'no_sk' => '1.0', 'sub_komponen' => 'KU', 'deskripsi' => 'Menyusun Perencanaan dengan Mengembangkan Strategi, Model, Metode, Teknik, dan Media Pembelajaran Yang Aktif, Kreatif, dan Inovatif'],
            ['komponen_id' => 3, 'no' => '28.0', 'no_sk' => '1.0', 'sub_komponen' => 'KU', 'deskripsi' => 'Memiliki kemampuan Informasi dan Teknologi (IT)'],
            ['komponen_id' => 4, 'no' => '29.0', 'no_sk' => '1.0', 'sub_komponen' => 'VM', 'deskripsi' => 'Pesantren Merumuskan Visi, Misi dan Tujuan Serta Mengimplementasikannya'],
            ['komponen_id' => 4, 'no' => '30.0', 'no_sk' => '1.0', 'sub_komponen' => 'PPb', 'deskripsi' => 'Pesantren Membuat Rencana Kerja Jangka Menengah (RKJM), Rencana Kerja Tahunan (RKT), dan Rencana Kerja Anggaran Pesantren (RKAP).'],
            ['komponen_id' => 4, 'no' => '31.0', 'no_sk' => '1.0', 'sub_komponen' => 'KMd', 'deskripsi' => 'Mudir Mampu Merencanakan, Melaksanakan, Mengevaluasi, Melakukan Tindak Lanjut Atas Sistem Operasional Prosedur (SOP), Peraturan Akademik, dan Sistem Informasi Manajemen (SIM)'],
            ['komponen_id' => 4, 'no' => '32.0', 'no_sk' => '3.0', 'sub_komponen' => 'PSP', 'deskripsi' => 'Pesantren Mengelola Sarana dan Prasarana'],
            ['komponen_id' => 4, 'no' => '33.0', 'no_sk' => '3.0', 'sub_komponen' => 'PPb', 'deskripsi' => 'Pesantren Mengelola Unit Usaha'],
            ['komponen_id' => 4, 'no' => '34.0', 'no_sk' => '2.0', 'sub_komponen' => 'KpMd', 'deskripsi' => 'Kepemimpinan Yang Kreatif, Inovatif, Partisipatif, Kolaboratif, Transformatif dan Efektif'],
            ['komponen_id' => 4, 'no' => '35.0', 'no_sk' => '4.0', 'sub_komponen' => 'PMs', 'deskripsi' => 'Pesantren melibatkan masyarakat dalam pelaksanaan program.'],
            ['komponen_id' => 4, 'no' => '36.0', 'no_sk' => '2.0', 'sub_komponen' => 'BP', 'deskripsi' => 'Menciptakan Budaya Ta’dzim dan Ta’awun di Pesantren'],
            ['komponen_id' => 4, 'no' => '37.0', 'no_sk' => '2.0', 'sub_komponen' => 'BP', 'deskripsi' => 'Menciptakan Lingkungan Berbahasa Arab dan Inggris di Pesantren.'],
            ['komponen_id' => 4, 'no' => '38.0', 'no_sk' => '4.0', 'sub_komponen' => 'Pengelolaan Alumni', 'deskripsi' => 'Pesantren Melakukan Pembinaan dan Pengelolaan alumni'],
            ['komponen_id' => 4, 'no' => '39.0', 'no_sk' => '3.0', 'sub_komponen' => 'PK', 'deskripsi' => 'Pesantren Menyelenggarakan Pembinaan Kegiatan Kesantrian Untuk Mengembangkan Minat dan Bakat Santri.'],
            ['komponen_id' => 4, 'no' => '40.0', 'no_sk' => '4.0', 'sub_komponen' => 'PMI', 'deskripsi' => 'Pesantren melaksanakan Penjaminan Mutu Internal'],
        ];

        $order = 1;
        foreach ($iapmButirs as $b) {
            MasterEdpmButir::firstOrCreate(
                ['komponen_id' => $b['komponen_id'], 'name' => 'Butir ' . $b['no']],
                [
                    'kode' => $b['komponen_id'] . '.' . $b['no'],
                    'nama' => 'Butir ' . $b['no'],
                    'deskripsi' => $b['deskripsi'],
                    'sub_komponen' => $b['sub_komponen'],
                    'no_sk' => $b['no_sk'],
                ]
            );
            $order++;
        }

        // Komponen IPR
        $iprKomponen = MasterEdpmKomponen::firstOrCreate(
            ['id' => ScoringService::IPR_CONFIG['id']],
            ['kode' => 'IPR', 'name' => 'IPR', 'nama' => 'IPR']
        );

        // Butir IPR (22)
        $iprButirs = [
            ['no' => '1.0', 'deskripsi' => 'Kualifikasi akademik guru minimum sarjana (S1) atau diploma empat'],
            ['no' => '2.0', 'deskripsi' => 'Ustadz pesantren memiliki ijazah atau alumni pesantren'],
            ['no' => '3.0', 'deskripsi' => 'Ustadz yang mengajar sesuai latar belakang pendidikan Dirasah Islamiyah'],
            ['no' => '4.0', 'deskripsi' => 'Ustadz yang mengajar memiliki kompetensi Bahasa Arab'],
            ['no' => '5.0', 'deskripsi' => 'Ustadz yang mengajar memiliki NBM'],
            ['no' => '6.0', 'deskripsi' => 'Ustadz yang mengajar memiliki sertifikat perkaderan Muhammadiyah'],
            ['no' => '7.0', 'deskripsi' => 'Ustadz yang mengajar aktif di persyarikatan'],
            ['no' => '8.0', 'deskripsi' => 'Pesantren memiliki perpustakaan kitab turats dan kontemporer'],
            ['no' => '9.0', 'deskripsi' => 'Jumlah rombongan belajar'],
            ['no' => '10.0', 'deskripsi' => 'Pesantren memiliki asrama dengan daya yang mencukupi kebutuhan'],
            ['no' => '11.0', 'deskripsi' => 'Pesantren memiliki lapangan'],
            ['no' => '12.0', 'deskripsi' => 'Pesantren memiliki masjid daya yang mencukupi kebutuhan'],
            ['no' => '13.0', 'deskripsi' => 'Pesantren memiliki ruang belajar daya yang mencukupi kebutuhan'],
            ['no' => '14.0', 'deskripsi' => 'Pesantren memiliki dapur umum yang mencukupi kebutuhan'],
            ['no' => '15.0', 'deskripsi' => 'Pesantren memiliki kamar MCK daya yang mencukupi kebutuhan'],
            ['no' => '16.0', 'deskripsi' => 'Pesantren memiliki ruang kantor yang mencukupi kebutuhan'],
            ['no' => '17.0', 'deskripsi' => 'Pesantren memiliki ruang organisasi santri'],
            ['no' => '18.0', 'deskripsi' => 'Pesantren memiliki laboratorium bahasa dan ruang micro teaching'],
            ['no' => '19.0', 'deskripsi' => 'Pesantren memiliki rumah dinas mudir pesantren'],
            ['no' => '20.0', 'deskripsi' => 'Pesantren memiliki ruang tamu'],
            ['no' => '21.0', 'deskripsi' => 'Pesantren memiliki rumah ustadz pesantren'],
            ['no' => '22.0', 'deskripsi' => 'Pesantren memiliki Poskestren'],
        ];

        foreach ($iprButirs as $b) {
            MasterEdpmButir::firstOrCreate(
                ['komponen_id' => $iprKomponen->id, 'name' => 'IPR Butir ' . $b['no']],
                [
                    'kode' => 'IPR.' . $b['no'],
                    'nama' => 'IPR Butir ' . $b['no'],
                    'deskripsi' => $b['deskripsi'],
                ]
            );
        }
    }
}