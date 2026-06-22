@php
    use App\Services\PesantrenService;
    $data = old('sdm', $sdm?->data ?? []);
    $butirData = $data['butirs'] ?? [];

    $bentukPendidikan = [
        'MI'  => 'Madrasah Ibtidaiyah (MI)',
        'MTs' => 'Madrasah Tsanawiyah (MTs)',
        'MA'  => 'Madrasah Aliyah (MA)',
        'Pondok Pesantren' => 'Pondok Pesantren',
        'Madrason Hidayah' => 'Madrason Hidayah',
        'Mahad Aly' => "Ma'had Aly",
        'TPQ' => 'Taman Pendidikan Al-Qur\'an (TPQ)',
        'Diniyah' => 'Diniyah Takmiliyah',
        'Lainnya' => 'Lainnya',
    ];

    $kategoriSDM = [
        'ustaz_tetap'       => ['label' => 'Ustaz Tetap', 'desc' => 'Guru/ustaz yang mengajar tetap'],
        'ustaz_tidak_tetap' => ['label' => 'Ustaz Tidak Tetap', 'desc' => 'Guru/ustaz yang mengajar tidak tetap'],
        'tenaga_kependidikan' => ['label' => 'Tenaga Kependidikan', 'desc' => 'Staf administrasi dan kependidikan'],
        's1_d4'             => ['label' => 'Kualifikasi S1/D4', 'desc' => 'Jumlah yang memiliki kualifikasi S1/D4'],
        'bersertifikat'     => ['label' => 'Bersertifikat', 'desc' => 'Jumlah yang memiliki sertifikat pendidik'],
        'nbm'               => ['label' => 'NBM Aktif', 'desc' => 'Nomor Badan Mushaf yang aktif'],
    ];
@endphp

<div class="row g-5">
    <div class="col-12">
        <div class="mb-4 p-4 rounded bg-light-info">
            <div class="d-flex align-items-start gap-3">
                <i class="ki-outline ki-information-2 fs-4 text-info mt-1"></i>
                <div class="fs-7 text-gray-700">
                    <strong>Data SDM Pesantren</strong> — Lengkapi jumlah SDM per bentuk pendidikan dan kategori.
                    <br>Kolom <strong>L</strong> = Laki-laki, <strong>P</strong> = Perempuan.
                </div>
            </div>
        </div>
    </div>

    @foreach($bentukPendidikan as $kode => $nama)
        @php $existing = $butirData[$kode] ?? []; @endphp
        <div class="col-12">
            <div class="card card-flush">
                <div class="card-header bg-light">
                    <h3 class="card-title fw-bold fs-7 text-gray-800">{{ $nama }}</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="bg-light-secondary">
                            <tr>
                                <th class="fs-8 fw-bold text-gray-600" style="width:40%">Kategori</th>
                                <th class="fs-8 fw-bold text-gray-600 text-center" style="width:15%">Laki-laki</th>
                                <th class="fs-8 fw-bold text-gray-600 text-center" style="width:15%">Perempuan</th>
                                <th class="fs-8 fw-bold text-gray-600" style="width:30%">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kategoriSDM as $katKey => $kat)
                                <tr>
                                    <td class="fs-7 fw-semibold text-gray-800 align-middle">{{ $kat['label'] }}</td>
                                    <td class="text-center">
                                        <input type="number" min="0" name="sdm[butirs][{{ $kode }}][{{ $katKey }}_L]"
                                               class="form-control form-control-sm form-control-solid text-center"
                                               value="{{ $existing[$katKey . '_L'] ?? 0 }}">
                                    </td>
                                    <td class="text-center">
                                        <input type="number" min="0" name="sdm[butirs][{{ $kode }}][{{ $katKey }}_P]"
                                               class="form-control form-control-sm form-control-solid text-center"
                                               value="{{ $existing[$katKey . '_P'] ?? 0 }}">
                                    </td>
                                    <td class="fs-8 text-muted align-middle">{{ $kat['desc'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach

    <div class="col-md-12 mt-4">
        <label class="form-label">Catatan SDM</label>
        <textarea name="sdm[catatan_sdm]" rows="3" class="form-control form-control-solid" placeholder="Ringkasan kualifikasi dan pembinaan SDM">{{ $data['catatan_sdm'] ?? '' }}</textarea>
    </div>
</div>

