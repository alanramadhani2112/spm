@php
    $pesantren = $pesantren ?? null;
    $selectedLayanan = old('layanan_satuan_pendidikan', $pesantren?->layanan_satuan_pendidikan ?? []);
    $selectedLayanan = is_array($selectedLayanan) ? $selectedLayanan : [];
    $units = collect(old('units', $pesantren?->units?->map(fn($unit) => [
        'layanan_satuan_pendidikan' => $unit->layanan_satuan_pendidikan,
        'jumlah_rombel' => $unit->jumlah_rombel,
    ])->toArray() ?? []));
    $unitRows = $units->pad(3, ['layanan_satuan_pendidikan' => '', 'jumlah_rombel' => 0])->take(5);
    $layananOptions = ['PDF Ulya', 'PDF Wustha', 'Muadalah', 'MTs', 'MA'];
@endphp

<div class="row g-5">
    <div class="col-md-6">
        <x-metronic.form-input name="nama_pesantren" label="Nama Pesantren" :value="$pesantren?->nama_pesantren" :required="true" />
    </div>
    <div class="col-md-6">
        <x-metronic.form-input name="ns_pesantren" label="NS Pesantren" :value="$pesantren?->ns_pesantren" :required="true" />
    </div>
    <div class="col-md-6">
        <x-metronic.form-input name="provinsi_kode" label="Kode Provinsi" :value="$pesantren?->provinsi_kode" :required="true" placeholder="Contoh: 32" />
    </div>
    <div class="col-md-6">
        <x-metronic.form-input name="tahun_pendirian" label="Tahun Pendirian" :value="$pesantren?->tahun_pendirian" :required="true" placeholder="Contoh: 2001" />
    </div>
    <div class="col-md-12">
        <x-metronic.form-input name="alamat" label="Alamat" type="textarea" :value="$pesantren?->alamat" :required="true" :rows="3" />
    </div>
    <div class="col-md-4">
        <x-metronic.form-input name="kota_kabupaten" label="Kota/Kabupaten" :value="$pesantren?->kota_kabupaten" />
    </div>
    <div class="col-md-4">
        <x-metronic.form-input name="kecamatan" label="Kecamatan" :value="$pesantren?->kecamatan" />
    </div>
    <div class="col-md-4">
        <x-metronic.form-input name="kelurahan" label="Kelurahan" :value="$pesantren?->kelurahan" />
    </div>
    <div class="col-md-6">
        <x-metronic.form-input name="nama_mudir" label="Nama Mudir" :value="$pesantren?->nama_mudir" />
    </div>
    <div class="col-md-6">
        <x-metronic.form-input name="jenjang_pendidikan_mudir" label="Pendidikan Mudir" :value="$pesantren?->jenjang_pendidikan_mudir" />
    </div>
    <div class="col-md-4">
        <x-metronic.form-input name="telp_pesantren" label="Telepon Pesantren" :value="$pesantren?->telp_pesantren" />
    </div>
    <div class="col-md-4">
        <x-metronic.form-input name="hp_wa" label="HP/WA" :value="$pesantren?->hp_wa" />
    </div>
    <div class="col-md-4">
        <x-metronic.form-input name="email_pesantren" label="Email Pesantren" type="email" :value="$pesantren?->email_pesantren" />
    </div>
    <div class="col-md-4">
        <x-metronic.form-input name="nspp" label="NSPP" :value="$pesantren?->nspp" />
    </div>
    <div class="col-md-4">
        <x-metronic.form-input name="persyarikatan" label="Persyarikatan" :value="$pesantren?->persyarikatan" placeholder="Ranting/Cabang/Daerah/Wilayah/Pusat" />
    </div>
    <div class="col-md-6">
        <x-metronic.form-input name="luas_tanah" label="Luas Tanah" :value="$pesantren?->luas_tanah" placeholder="Contoh: 2.000 m2" />
    </div>
    <div class="col-md-6">
        <x-metronic.form-input name="luas_bangunan" label="Luas Bangunan" :value="$pesantren?->luas_bangunan" placeholder="Contoh: 1.200 m2" />
    </div>
    <div class="col-md-4">
        <label class="form-label">Status Kepemilikan Tanah</label>
        <select name="status_kepemilikan_tanah" class="form-select form-select-solid">
            @php $skt = $pesantren?->status_kepemilikan_tanah ?? ""; @endphp
            <option value="">Pilih status</option>
            <option value="milik_sendiri" @selected($skt === "milik_sendiri")>Milik Sendiri</option>
            <option value="wakaf" @selected($skt === "wakaf")>Wakaf</option>
            <option value="sewa" @selected($skt === "sewa")>Sewa</option>
            <option value="pinjam_pakai" @selected($skt === "pinjam_pakai")>Pinjam Pakai</option>
        </select>
    </div>
    <div class="col-md-4">
        <x-metronic.form-input name="sertifikat_nsp" label="Nomor Sertifikat NSP" :value="$pesantren?->sertifikat_nsp" />
    </div>
    <div class="col-md-12">
        <x-metronic.form-input name="visi" label="Visi" type="textarea" :value="$pesantren?->visi" :rows="2" />
    </div>
    <div class="col-md-12">
        <x-metronic.form-input name="misi" label="Misi" type="textarea" :value="$pesantren?->misi" :rows="3" />
    </div>
</div>

<div class="separator separator-dashed my-8"></div>

<div class="mb-8">
    <label class="form-label required">Layanan Satuan Pendidikan</label>
    <div class="d-flex flex-wrap gap-4">
        @foreach($layananOptions as $option)
            <label class="form-check form-check-custom form-check-solid">
                <input class="form-check-input" type="checkbox" name="layanan_satuan_pendidikan[]" value="{{ $option }}" @checked(in_array($option, $selectedLayanan, true))>
                <span class="form-check-label">{{ $option }}</span>
            </label>
        @endforeach
    </div>
    @error('layanan_satuan_pendidikan')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>

<div class="mb-8">
    <label class="form-label required">Unit Pendidikan</label>
    <div class="table-responsive">
        <table class="table table-row-dashed align-middle">
            <thead><tr class="fw-bold text-muted"><th>Layanan</th><th>Jumlah Rombel</th></tr></thead>
            <tbody>
                @foreach($unitRows as $index => $unit)
                    <tr>
                        <td><input type="text" name="units[{{ $index }}][layanan_satuan_pendidikan]" class="form-control form-control-solid" value="{{ $unit['layanan_satuan_pendidikan'] ?? '' }}" placeholder="Contoh: MTs"></td>
                        <td><input type="number" min="0" name="units[{{ $index }}][jumlah_rombel]" class="form-control form-control-solid" value="{{ $unit['jumlah_rombel'] ?? 0 }}"></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @error('units')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>

<div class="separator separator-dashed my-8"></div>

<h5 class="fw-bold text-gray-800 mb-4">Dokumen Utama (7 Dokumen Wajib)</h5>
<div class="row g-5">
    @php
        $dokUtama = [
            'dok_profil' => 'Dokumen Profil Pesantren',
            'dok_nsp' => 'Sertifikat NSP',
            'dok_renstra' => 'Renstra',
            'dok_rk_anggaran' => 'RK Anggaran',
            'dok_kurikulum' => 'Kurikulum',
            'dok_silabus_rpp' => 'Silabus/RPP',
            'dok_kepengasuhan' => 'Kepengasuhan',
            'file_lk_iapm' => 'File LK IAPM',
        ];
    @endphp
    @foreach($dokUtama as $field => $label)
        <div class="col-md-6">
            <x-metronic.form-input name="{{ $field }}" label="{{ $label }}" type="file" help="PDF maksimal 5MB" />
            @if($pesantren?->{$field})
                <div class="fs-8 text-muted mt-n5 mb-6">File tersimpan: {{ basename($pesantren->{$field}) }}</div>
            @endif
        </div>
    @endforeach
</div>

<h5 class="fw-bold text-gray-800 mb-4 mt-6">Dokumen Sekunder (4 Dokumen Pendukung)</h5>
<div class="row g-5">
    @php
        $dokSekunder = [
            'dok_peraturan_kepegawaian' => 'Peraturan Kepegawaian',
            'dok_sarpras' => 'Sarpras',
            'dok_laporan_tahunan' => 'Laporan Tahunan',
            'dok_sop' => 'SOP',
        ];
    @endphp
    @foreach($dokSekunder as $field => $label)
        <div class="col-md-6">
            <x-metronic.form-input name="{{ $field }}" label="{{ $label }}" type="file" help="PDF maksimal 5MB" />
            @if($pesantren?->{$field})
                <div class="fs-8 text-muted mt-n5 mb-6">File tersimpan: {{ basename($pesantren->{$field}) }}</div>
            @endif
        </div>
    @endforeach
</div>


