@extends('layouts.metronic.app')

@section('title', 'Master EDPM')
@section('pageTitle', 'Master EDPM')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-6">
    <a href="{{ route('superadmin.master-data.index') }}" class="btn btn-sm btn-light"><i class="ki-outline ki-left fs-4"></i>Kembali</a>
</div>

<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl-4">
        <x-metronic.card title="Tambah Komponen">
            <form method="POST" action="{{ route('superadmin.master-data.edpm.komponen.store') }}">
                @csrf
                <x-metronic.form-input name="kode" label="Kode" :required="true" placeholder="Contoh: MUTU_LULUSAN" />
                <x-metronic.form-input name="nama" label="Nama Komponen" :required="true" />
                <button type="submit" class="btn btn-primary w-100">Tambah Komponen</button>
            </form>
        </x-metronic.card>
    </div>
    <div class="col-xl-8">
        <x-metronic.data-table title="Komponen EDPM" :headers="['Kode', 'Nama', 'Butir', 'Aksi']">
            @forelse($komponens as $komponen)
                <tr>
                    <td class="fw-bold text-gray-900">{{ $komponen->kode }}</td>
                    <td>{{ $komponen->nama ?? $komponen->name }}</td>
                    <td><span class="badge badge-light-primary">{{ $komponen->butirs_count }} butir</span></td>
                    <td>
                        <form method="POST" action="{{ route('superadmin.master-data.edpm.komponen.update', $komponen) }}" class="d-flex gap-2 align-items-center mb-2">
                            @csrf @method('PUT')
                            <input name="kode" value="{{ $komponen->kode }}" class="form-control form-control-sm form-control-solid w-125px">
                            <input name="nama" value="{{ $komponen->nama ?? $komponen->name }}" class="form-control form-control-sm form-control-solid">
                            <button class="btn btn-sm btn-light-primary">Simpan</button>
                        </form>
                        <form method="POST" action="{{ route('superadmin.master-data.edpm.komponen.destroy', $komponen) }}" onsubmit="return confirm('Hapus komponen dan seluruh butirnya?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-light-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-8">Belum ada komponen.</td></tr>
            @endforelse
        </x-metronic.data-table>
    </div>
</div>

<div class="row g-5 g-xl-8">
    <div class="col-xl-4">
        <x-metronic.card title="Tambah Butir">
            <form method="POST" action="{{ route('superadmin.master-data.edpm.butir.store') }}">
                @csrf
                <x-metronic.form-input name="komponen_id" label="Komponen" type="select" :options="$komponens->pluck('nama', 'id')->toArray()" :required="true" />
                <x-metronic.form-input name="kode" label="Kode Butir" placeholder="Contoh: 1.1" />
                <x-metronic.form-input name="nama" label="Nama Butir" :required="true" />
                <x-metronic.form-input name="deskripsi" label="Deskripsi" type="textarea" :rows="3" />
                <button type="submit" class="btn btn-primary w-100">Tambah Butir</button>
            </form>
        </x-metronic.card>
    </div>
    <div class="col-xl-8">
        <x-metronic.data-table title="Butir EDPM" :headers="['Komponen', 'Kode', 'Nama/Deskripsi', 'Aksi']">
            @forelse($butirs as $butir)
                <tr>
                    <td>{{ $butir->komponen?->nama ?? '—' }}</td>
                    <td class="fw-bold">{{ $butir->kode ?? '—' }}</td>
                    <td>
                        <div class="fw-semibold text-gray-900">{{ $butir->nama ?? $butir->name }}</div>
                        <div class="fs-8 text-muted">{{ $butir->deskripsi ?? 'Tanpa deskripsi' }}</div>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('superadmin.master-data.edpm.butir.update', $butir) }}" class="d-grid gap-2 mb-2">
                            @csrf @method('PUT')
                            <select name="komponen_id" class="form-select form-select-sm form-select-solid">
                                @foreach($komponens as $komponen)
                                    <option value="{{ $komponen->id }}" @selected($butir->komponen_id == $komponen->id)>{{ $komponen->nama ?? $komponen->name }}</option>
                                @endforeach
                            </select>
                            <input name="kode" value="{{ $butir->kode }}" class="form-control form-control-sm form-control-solid" placeholder="Kode">
                            <input name="nama" value="{{ $butir->nama ?? $butir->name }}" class="form-control form-control-sm form-control-solid" placeholder="Nama">
                            <textarea name="deskripsi" rows="2" class="form-control form-control-sm form-control-solid" placeholder="Deskripsi">{{ $butir->deskripsi }}</textarea>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-light-primary">Simpan</button>
                            </div>
                        </form>
                        <form method="POST" action="{{ route('superadmin.master-data.edpm.butir.destroy', $butir) }}" onsubmit="return confirm('Hapus butir ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-light-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-8">Belum ada butir.</td></tr>
            @endforelse
        </x-metronic.data-table>
    </div>
</div>
@endsection
