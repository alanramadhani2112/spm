<?php

namespace Database\Seeders;

use App\Models\MasterEdpmKomponen;
use App\Models\MasterEdpmSubKomponen;
use App\Services\ScoringService;
use Illuminate\Database\Seeder;

class MasterEdpmSubKomponenSeeder extends Seeder
{
    /** Sub komponen IAPM dari 4 komponen (40 butir) */
    public const SUB_KOMPONEN_DATA = [
        // Komponen 1 — MUTU LULUSAN
        ['komponen_id' => 1, 'kode' => 'K',  'nama' => 'Karakter (K)', 'deskripsi' => 'Kemandirian & Kewirausahaan - butir 6'],
        ['komponen_id' => 1, 'kode' => 'KS', 'nama' => 'Keilmuan Keislaman & Bahasa / Kepemimpinan & Sosial (KS)', 'deskripsi' => 'Butir 2,3,4,5,7,8'],

        // Komponen 2 — PROSES PEMBELAJARAN
        ['komponen_id' => 2, 'kode' => 'KP', 'nama' => 'Kurikulum & Proses (KP)', 'deskripsi' => 'Butir 9,13,14,17,18'],
        ['komponen_id' => 2, 'kode' => 'IB', 'nama' => 'Penilaian & Kompetensi / Karakteristik Pembelajar (IB)', 'deskripsi' => 'Butir 10,12,15,16'],
        ['komponen_id' => 2, 'kode' => 'SP', 'nama' => 'Pemanfaatan TIK (SP)', 'deskripsi' => 'Butir 11'],

        // Komponen 3 — MUTU USTAZ
        ['komponen_id' => 3, 'kode' => 'PP', 'nama' => 'Pengembangan Profesional (PP)', 'deskripsi' => 'Butir 19,20'],
        ['komponen_id' => 3, 'kode' => 'KU', 'nama' => 'Kompetensi Keislaman & Bahasa / Karakter & Keteladanan (KU)', 'deskripsi' => 'Butir 21,22,23,26,27,28'],
        ['komponen_id' => 3, 'kode' => 'PPs','nama' => 'Karakter & Keteladanan / Profesionalisme Pesantren (PPs)', 'deskripsi' => 'Butir 24,25'],

        // Komponen 4 — MANAJEMEN PESANTREN
        ['komponen_id' => 4, 'kode' => 'VM', 'nama' => 'Visi, Misi & Tujuan (VM)', 'deskripsi' => 'Butir 29'],
        ['komponen_id' => 4, 'kode' => 'PPb','nama' => 'Perencanaan, Pembiayaan & Unit Usaha (PPb)', 'deskripsi' => 'Butir 30,33'],
        ['komponen_id' => 4, 'kode' => 'KMd','nama' => 'Kepemimpinan Mudir - Administrasi (KMd)', 'deskripsi' => 'Butir 31'],
        ['komponen_id' => 4, 'kode' => 'PSP','nama' => 'Sarana & Prasarana (PSP)', 'deskripsi' => 'Butir 32'],
        ['komponen_id' => 4, 'kode' => 'KpMd','nama' => 'Kepemimpinan Mudir (KpMd)', 'deskripsi' => 'Butir 34'],
        ['komponen_id' => 4, 'kode' => 'PMs','nama' => 'Kemitraan - Pelibatan Masyarakat (PMs)', 'deskripsi' => 'Butir 35'],
        ['komponen_id' => 4, 'kode' => 'BP', 'nama' => 'Budaya Pesantren (BP)', 'deskripsi' => 'Butir 36,37'],
        ['komponen_id' => 4, 'kode' => 'PK', 'nama' => 'Kemitraan - Pembinaan Kegiatan Kesantrian (PK)', 'deskripsi' => 'Butir 39'],
        ['komponen_id' => 4, 'kode' => 'PMI','nama' => 'Penjaminan Mutu Internal (PMI)', 'deskripsi' => 'Butir 40'],
        ['komponen_id' => 4, 'kode' => 'Pengelolaan Alumni', 'nama' => 'Pengelolaan Alumni', 'deskripsi' => 'Butir 38'],
    ];

    public function run(): void
    {
        foreach (self::SUB_KOMPONEN_DATA as $data) {
            MasterEdpmSubKomponen::firstOrCreate(
                ['kode' => $data['kode']],
                $data
            );
        }
    }
}
