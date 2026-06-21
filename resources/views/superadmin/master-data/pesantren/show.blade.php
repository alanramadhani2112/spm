@extends('layouts.metronic.app')

@section('title', 'Detail Pesantren')
@section('pageTitle', 'Detail Pesantren')

@section('toolbar')
<div class="d-flex flex-wrap gap-2">
    <a href="{{ route('superadmin.master-data.pesantren.index') }}" class="btn btn-sm btn-light">
        <i class="ki-outline ki-left fs-4"></i>Kembali
    </a>
</div>
@endsection

@section('breadcrumbs')
    <x-superadmin.breadcrumb :items="[['label' => 'Master Data', 'route' => 'superadmin.master-data.index'], ['label' => 'Data Pesantren', 'route' => 'superadmin.master-data.pesantren.index'], ['label' => $pesantren->nama_pesantren, 'active' => true]]" />
@endsection

@section('content')


@php
    $checks = [
        'profilMinimum' => ['label' => 'Profil Minimum', 'ok' => $completeness['profilMinimum'] ?? false],
        'unit' => ['label' => 'Unit Pendidikan', 'ok' => $pesantren->units->isNotEmpty()],
        'assessmentReady' => ['label' => 'Assessment Ready', 'ok' => $completeness['assessmentReady'] ?? false],
    ];
    $selectedLayanan = old('layanan_satuan_pendidikan', $pesantren->layanan_satuan_pendidikan ?? []);
    $selectedLayanan = is_array($selectedLayanan) ? $selectedLayanan : [];
    $unitRows = collect(old('units', $pesantren->units->map(fn($unit) => [
        'layanan_satuan_pendidikan' => $unit->layanan_satuan_pendidikan,
        'jumlah_rombel' => $unit->jumlah_rombel,
    ])->toArray()))->pad(3, ['layanan_satuan_pendidikan' => '', 'jumlah_rombel' => 0])->take(5);
    $layananOptions = ['PDF Ulya', 'PDF Wustha', 'Muadalah', 'MTs', 'MA'];
    $datasetOverrides = [
        'ipm' => ['title' => 'Override Data IPM', 'route' => route('superadmin.master-data.pesantren.ipm.update', $pesantren), 'data' => $ipm?->data ?? new stdClass()],
        'sdm' => ['title' => 'Override Data SDM', 'route' => route('superadmin.master-data.pesantren.sdm.update', $pesantren), 'data' => $sdm?->data ?? new stdClass()],
        'edpm' => ['title' => 'Override Data EDPM', 'route' => route('superadmin.master-data.pesantren.edpm.update', $pesantren), 'data' => $edpm?->data ?? new stdClass()],
    ];
@endphp

<div class="card card-flush bg-light-primary border border-primary border-dashed mb-8">
    <div class="card-body p-7">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-5">
            <div>
                <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
                    <h2 class="fw-bold text-gray-900 mb-0">{{ $pesantren->nama_pesantren }}</h2>
                    <span class="badge badge-light-{{ $pesantren->is_locked ? 'danger' : 'info' }}">{{ $pesantren->is_locked ? 'Terkunci' : 'Terbuka' }}</span>
                </div>
                <div class="fs-7 text-muted">{{ $pesantren->user?->name ?? 'User tidak ditemukan' }} - {{ $pesantren->user?->email ?? 'email kosong' }}</div>
                <div class="fs-8 text-muted mt-1">NSP: {{ $pesantren->ns_pesantren ?: '-' }}</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($checks as $check)
                    <span class="badge badge-light-{{ $check['ok'] ? 'success' : 'warning' }}">{{ $check['label'] }}: {{ $check['ok'] ? 'OK' : 'Kurang' }}</span>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl-4">
        <x-metronic.stat-card value="{{ $pesantren->units->count() }}" label="Unit Pendidikan" icon="ki-bank" color="primary" />
    </div>
    <div class="col-xl-4">
        <x-metronic.stat-card value="{{ $activeAkreditasis->count() }}" label="Akreditasi Aktif" icon="ki-shield-search" color="warning" />
    </div>
    <div class="col-xl-4">
        <x-metronic.stat-card value="{{ ($completeness['assessmentReady'] ?? false) ? 'Ready' : 'Kurang' }}" label="Status Assessment" icon="ki-verify" color="{{ ($completeness['assessmentReady'] ?? false) ? 'success' : 'danger' }}" />
    </div>
</div>

@if($activeAkreditasis->isNotEmpty())
    <x-metronic.card title="Akreditasi Aktif" class="mb-8">
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-7">
                <thead>
                    <tr class="text-start text-muted fw-bold text-uppercase">
                        <th>Nomor</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activeAkreditasis as $akreditasi)
                        <tr>
                            <td>{{ $akreditasi->nomor_pendaftaran ?? $akreditasi->uuid }}</td>
                            <td><span class="badge badge-light-primary">{{ $akreditasi->status }}</span></td>
                            <td>{{ optional($akreditasi->created_at)->format('d M Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-metronic.card>
@endif

<x-metronic.card title="Override Dokumen Pesantren" class="mb-8">
    <x-slot:header>
        <span class="badge badge-light-danger">PDF max 5MB</span>
    </x-slot:header>

    <form method="POST" action="{{ route('superadmin.master-data.pesantren.documents.update', $pesantren) }}" enctype="multipart/form-data" class="d-grid gap-6">
        @csrf
        @method('PATCH')
        <div class="row g-5">
            @foreach($documentFields as $field => $label)
                <div class="col-md-6">
                    <label for="{{ $field }}" class="form-label">{{ $label }}</label>
                    <input id="{{ $field }}" type="file" name="{{ $field }}" class="form-control form-control-solid" accept="application/pdf,.pdf">
                    <div class="fs-8 text-muted mt-1">Saat ini: {{ $pesantren->{$field} ?: 'Belum ada' }}</div>
                </div>
            @endforeach
        </div>
        <div>
            <label for="documents_reason" class="form-label required">Alasan Override Dokumen</label>
            <textarea id="documents_reason" name="reason" class="form-control form-control-solid" rows="3" required placeholder="Jelaskan alasan Super Admin mengubah dokumen pesantren">{{ old('reason') }}</textarea>
            @error('documents')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-light-primary"
                    data-swal-confirm="true"
                    data-swal-title="Simpan override dokumen?"
                    data-swal-text="Dokumen yang diupload akan mengganti dokumen lama dan tercatat di Log Audit."
                    data-swal-icon="warning"
                    data-swal-confirm-button="Ya, simpan">
                Simpan Dokumen
            </button>
        </div>
    </form>
</x-metronic.card>

<div class="row g-5 g-xl-8 mb-8">
    @foreach($datasetOverrides as $key => $override)
        <div class="col-xl-4">
            <x-metronic.card title="{{ $override['title'] }}" class="h-100">
                <form method="POST" action="{{ $override['route'] }}" class="d-grid gap-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label for="{{ $key }}_data_json" class="form-label required">Data JSON</label>
                        <textarea id="{{ $key }}_data_json" name="data_json" class="form-control form-control-solid font-monospace" rows="10" required>{{ old('data_json', json_encode($override['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
                    </div>
                    <div>
                        <label for="{{ $key }}_reason" class="form-label required">Alasan Override</label>
                        <textarea id="{{ $key }}_reason" name="reason" class="form-control form-control-solid" rows="3" required placeholder="Jelaskan alasan override {{ strtoupper($key) }}">{{ old('reason') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-light-primary"
                            data-swal-confirm="true"
                            data-swal-title="Simpan override {{ strtoupper($key) }}?"
                            data-swal-text="Perubahan akan tercatat di Log Audit."
                            data-swal-icon="warning"
                            data-swal-confirm-button="Ya, simpan">
                        Simpan {{ strtoupper($key) }}
                    </button>
                </form>
            </x-metronic.card>
        </div>
    @endforeach
</div>

<x-metronic.card title="Override Profil Pesantren">
    <x-slot:header>
        <span class="badge badge-light-danger">Wajib alasan audit</span>
    </x-slot:header>

    @if($pesantren->is_locked)
        <x-metronic.alert type="warning">
            <div class="fw-semibold mb-1">Profil pesantren sedang terkunci.</div>
            <div>Super Admin tetap bisa melakukan override. Semua perubahan wajib tercatat dengan alasan.</div>
        </x-metronic.alert>
    @endif

    <form method="POST" action="{{ route('superadmin.master-data.pesantren.update', $pesantren) }}" class="d-grid gap-8">
        @csrf
        @method('PUT')

        <div class="row g-5">
            <div class="col-md-6">
                <x-metronic.form-input name="nama_pesantren" label="Nama Pesantren" :value="$pesantren->nama_pesantren" :required="true" />
            </div>
            <div class="col-md-6">
                <x-metronic.form-input name="ns_pesantren" label="NS Pesantren" :value="$pesantren->ns_pesantren" :required="true" />
            </div>
            <div class="col-md-6">
                <x-metronic.form-input name="provinsi_kode" label="Kode Provinsi" :value="$pesantren->provinsi_kode" :required="true" placeholder="Contoh: 32" />
            </div>
            <div class="col-md-6">
                <x-metronic.form-input name="tahun_pendirian" label="Tahun Pendirian" :value="$pesantren->tahun_pendirian" :required="true" placeholder="Contoh: 2001" />
            </div>
            <div class="col-md-12">
                <x-metronic.form-input name="alamat" label="Alamat" type="textarea" :value="$pesantren->alamat" :required="true" :rows="3" />
            </div>
            <div class="col-md-4">
                <x-metronic.form-input name="kota_kabupaten" label="Kota/Kabupaten" :value="$pesantren->kota_kabupaten" />
            </div>
            <div class="col-md-4">
                <x-metronic.form-input name="kecamatan" label="Kecamatan" :value="$pesantren->kecamatan" />
            </div>
            <div class="col-md-4">
                <x-metronic.form-input name="kelurahan" label="Kelurahan" :value="$pesantren->kelurahan" />
            </div>
            <div class="col-md-6">
                <x-metronic.form-input name="nama_mudir" label="Nama Mudir" :value="$pesantren->nama_mudir" />
            </div>
            <div class="col-md-6">
                <x-metronic.form-input name="jenjang_pendidikan_mudir" label="Pendidikan Mudir" :value="$pesantren->jenjang_pendidikan_mudir" />
            </div>
            <div class="col-md-4">
                <x-metronic.form-input name="telp_pesantren" label="Telepon Pesantren" :value="$pesantren->telp_pesantren" />
            </div>
            <div class="col-md-4">
                <x-metronic.form-input name="hp_wa" label="HP/WA" :value="$pesantren->hp_wa" />
            </div>
            <div class="col-md-4">
                <x-metronic.form-input name="email_pesantren" label="Email Pesantren" type="email" :value="$pesantren->email_pesantren" />
            </div>
            <div class="col-md-6">
                <x-metronic.form-input name="luas_tanah" label="Luas Tanah" :value="$pesantren->luas_tanah" placeholder="Contoh: 2.000 m2" />
            </div>
            <div class="col-md-6">
                <x-metronic.form-input name="luas_bangunan" label="Luas Bangunan" :value="$pesantren->luas_bangunan" placeholder="Contoh: 1.200 m2" />
            </div>
            <div class="col-md-12">
                <x-metronic.form-input name="visi" label="Visi" type="textarea" :value="$pesantren->visi" :rows="2" />
            </div>
            <div class="col-md-12">
                <x-metronic.form-input name="misi" label="Misi" type="textarea" :value="$pesantren->misi" :rows="3" />
            </div>
        </div>

        <div>
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

        <div>
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

        <div>
            <label for="reason" class="form-label required">Alasan Override</label>
            <textarea id="reason" name="reason" class="form-control form-control-solid" rows="3" required placeholder="Jelaskan alasan Super Admin mengubah data pesantren">{{ old('reason') }}</textarea>
            @error('reason')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        <div class="d-flex justify-content-end gap-3">
            <a href="{{ route('superadmin.master-data.pesantren.index') }}" class="btn btn-light">Batal</a>
            <button type="submit" class="btn btn-primary"
                    data-swal-confirm="true"
                    data-swal-title="Simpan override profil?"
                    data-swal-text="Perubahan akan tercatat di Log Audit."
                    data-swal-icon="warning"
                    data-swal-confirm-button="Ya, simpan">
                Simpan Override
            </button>
        </div>
    </form>
</x-metronic.card>
@endsection

