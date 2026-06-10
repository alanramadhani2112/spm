@extends('layouts.metronic.app')

@section('title', 'Master EDPM')
@section('pageTitle', 'Master EDPM / IPR')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-4 mb-8">
    <div>
        <h2 class="fs-2 fw-bold text-gray-900 mb-2">Kelola Struktur Instrumen EDPM</h2>
        <p class="fs-7 text-muted mb-0">Susun instrumen dengan alur sederhana: <span class="fw-semibold text-gray-900">Komponen</span> → <span class="fw-semibold text-gray-900">Butir</span> → digunakan asesor saat penilaian.</p>
    </div>
    <a href="{{ route('superadmin.master-data.index') }}" class="btn btn-sm btn-light"><i class="ki-outline ki-left fs-4"></i>Kembali</a>
</div>

@if(session('success'))
    <x-metronic.alert type="success" :message="session('success')" />
@endif

@if($errors->any())
    <x-metronic.alert type="danger">
        <div class="fw-semibold mb-2">Periksa kembali input master EDPM:</div>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-metronic.alert>
@endif

<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $komponens->count() }}" label="Komponen" icon="ki-category" color="primary" /></div>
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $butirs->count() }}" label="Butir Penilaian" icon="ki-check-square" color="success" /></div>
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $komponens->where('butirs_count', 0)->count() }}" label="Komponen Kosong" icon="ki-warning" color="warning" /></div>
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $komponens->count() ? round($butirs->count() / max($komponens->count(), 1), 1) : 0 }}" label="Rata-rata Butir" icon="ki-chart" color="info" /></div>
</div>

<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl-4">
        <x-metronic.card title="Tambah Komponen">
            <div class="mb-6 rounded bg-light-primary p-4">
                <div class="fw-semibold text-primary mb-1">Komponen adalah kelompok penilaian</div>
                <div class="fs-8 text-muted">Contoh: Mutu Lulusan, Proses Pembelajaran, atau Manajemen Pesantren.</div>
            </div>
            <form method="POST" action="{{ route('superadmin.master-data.edpm.komponen.store') }}" class="d-grid gap-4">
                @csrf
                <x-metronic.form-input name="kode" label="Kode Komponen" :required="true" placeholder="Contoh: MUTU_LULUSAN" />
                <x-metronic.form-input name="nama" label="Nama Komponen" :required="true" placeholder="Contoh: Mutu Lulusan" />
                <button type="submit" class="btn btn-primary w-100">Tambah Komponen</button>
            </form>
        </x-metronic.card>
    </div>

    <div class="col-xl-8">
        <x-metronic.card title="Peta Komponen EDPM" flush>
            <x-slot:header>
                <span class="badge badge-light-primary">{{ $komponens->count() }} komponen</span>
            </x-slot:header>
            <div class="table-responsive">
                <table class="table table-row-bordered align-middle gy-4">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th class="ps-4 min-w-120px">Kode</th>
                            <th class="min-w-220px">Komponen</th>
                            <th class="min-w-100px">Butir</th>
                            <th class="text-end min-w-260px pe-4">Edit Cepat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($komponens as $komponen)
                            <tr>
                                <td class="ps-4"><span class="badge badge-light-primary font-monospace">{{ $komponen->kode }}</span></td>
                                <td>
                                    <div class="fw-bold text-gray-900">{{ $komponen->nama ?? $komponen->name }}</div>
                                    <div class="fs-8 text-muted">Kelompok butir penilaian EDPM/IPR</div>
                                </td>
                                <td><span class="badge badge-light-{{ $komponen->butirs_count ? 'success' : 'warning' }}">{{ $komponen->butirs_count }} butir</span></td>
                                <td class="pe-4">
                                    <form method="POST" action="{{ route('superadmin.master-data.edpm.komponen.update', $komponen) }}" class="d-flex flex-wrap gap-2 justify-content-end mb-2">
                                        @csrf @method('PUT')
                                        <input name="kode" value="{{ $komponen->kode }}" class="form-control form-control-sm form-control-solid w-125px" aria-label="Kode komponen">
                                        <input name="nama" value="{{ $komponen->nama ?? $komponen->name }}" class="form-control form-control-sm form-control-solid w-200px" aria-label="Nama komponen">
                                        <button class="btn btn-sm btn-light-primary">Simpan</button>
                                    </form>
                                    <form method="POST" action="{{ route('superadmin.master-data.edpm.komponen.destroy', $komponen) }}" onsubmit="return confirm('Hapus komponen dan seluruh butirnya?')" class="text-end">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-light-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-10">Belum ada komponen. Tambahkan komponen pertama dari form di sebelah kiri.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-metronic.card>
    </div>
</div>

<div class="row g-5 g-xl-8">
    <div class="col-xl-4">
        <x-metronic.card title="Tambah Butir Penilaian">
            <div class="mb-6 rounded bg-light-success p-4">
                <div class="fw-semibold text-success mb-1">Butir adalah pertanyaan/indikator</div>
                <div class="fs-8 text-muted">Setiap butir harus masuk ke salah satu komponen agar mudah dipahami asesor.</div>
            </div>
            <form method="POST" action="{{ route('superadmin.master-data.edpm.butir.store') }}" class="d-grid gap-4">
                @csrf
                <x-metronic.form-input name="komponen_id" label="Komponen" type="select" :options="$komponens->mapWithKeys(fn($k) => [$k->id => ($k->kode.' — '.($k->nama ?? $k->name))])->toArray()" :required="true" />
                <x-metronic.form-input name="kode" label="Kode Butir" placeholder="Contoh: 1.1" />
                <x-metronic.form-input name="nama" label="Nama Butir" :required="true" placeholder="Contoh: Ketersediaan dokumen kurikulum" />
                <x-metronic.form-input name="deskripsi" label="Deskripsi/Panduan" type="textarea" :rows="3" placeholder="Berikan panduan singkat untuk asesor" />
                <button type="submit" class="btn btn-success w-100">Tambah Butir</button>
            </form>
        </x-metronic.card>
    </div>

    <div class="col-xl-8">
        <x-metronic.card title="Daftar Butir Berdasarkan Komponen" flush>
            <x-slot:header>
                <span class="badge badge-light-success">{{ $butirs->count() }} butir</span>
            </x-slot:header>

            <div class="d-grid gap-6">
                @forelse($komponens as $komponen)
                    @php $groupedButirs = $butirs->where('komponen_id', $komponen->id); @endphp
                    <div class="border rounded p-5">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <span class="badge badge-light-primary font-monospace me-2">{{ $komponen->kode }}</span>
                                <span class="fw-bold text-gray-900">{{ $komponen->nama ?? $komponen->name }}</span>
                            </div>
                            <span class="badge badge-light-{{ $groupedButirs->count() ? 'success' : 'warning' }}">{{ $groupedButirs->count() }} butir</span>
                        </div>

                        @forelse($groupedButirs as $butir)
                            <div class="border-top py-4">
                                <div class="d-flex flex-wrap justify-content-between gap-4 mb-3">
                                    <div>
                                        <div class="fw-semibold text-gray-900"><span class="badge badge-light-secondary me-2">{{ $butir->kode ?? '—' }}</span>{{ $butir->nama ?? $butir->name }}</div>
                                        <div class="fs-8 text-muted mt-1">{{ $butir->deskripsi ?? 'Tanpa deskripsi' }}</div>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('superadmin.master-data.edpm.butir.update', $butir) }}" class="row g-2 align-items-end">
                                    @csrf @method('PUT')
                                    <div class="col-md-4">
                                        <label class="form-label fs-8">Komponen</label>
                                        <select name="komponen_id" class="form-select form-select-sm form-select-solid">
                                            @foreach($komponens as $option)
                                                <option value="{{ $option->id }}" @selected($butir->komponen_id == $option->id)>{{ $option->kode }} — {{ $option->nama ?? $option->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fs-8">Kode</label>
                                        <input name="kode" value="{{ $butir->kode }}" class="form-control form-control-sm form-control-solid">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fs-8">Nama</label>
                                        <input name="nama" value="{{ $butir->nama ?? $butir->name }}" class="form-control form-control-sm form-control-solid">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fs-8">Deskripsi</label>
                                        <textarea name="deskripsi" rows="2" class="form-control form-control-sm form-control-solid">{{ $butir->deskripsi }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <button class="btn btn-sm btn-light-primary">Simpan Butir</button>
                                    </div>
                                </form>
                                <div class="d-flex justify-content-end mt-2">
                                    <form method="POST" action="{{ route('superadmin.master-data.edpm.butir.destroy', $butir) }}" onsubmit="return confirm('Hapus butir ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-light-danger">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="border-top py-5 text-muted fs-7">Belum ada butir untuk komponen ini.</div>
                        @endforelse
                    </div>
                @empty
                    <div class="text-center text-muted py-12 border rounded bg-light">Belum ada komponen dan butir.</div>
                @endforelse
            </div>
        </x-metronic.card>
    </div>
</div>
@endsection
