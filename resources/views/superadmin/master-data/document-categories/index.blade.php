@extends('layouts.metronic.app')

@section('title', 'Kategori Dokumen')
@section('pageTitle', 'Kategori Dokumen')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-6">
    <a href="{{ route('superadmin.master-data.index') }}" class="btn btn-sm btn-light"><i class="ki-outline ki-left fs-4"></i>Kembali</a>
</div>

<div class="row g-5 g-xl-8">
    <div class="col-xl-4">
        <x-metronic.card title="Tambah Kategori Dokumen">
            <form method="POST" action="{{ route('superadmin.master-data.document-categories.store') }}">
                @csrf
                <x-metronic.form-input name="name" label="Nama Kategori" :required="true" />
                <x-metronic.form-input name="required_for_phase" label="Fase Wajib" placeholder="Contoh: assessment, final_validation" />
                <x-metronic.form-input name="description" label="Deskripsi" type="textarea" :rows="3" />
                <label class="form-check form-check-custom form-check-solid mb-8">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                    <span class="form-check-label">Aktif</span>
                </label>
                <button type="submit" class="btn btn-primary w-100">Tambah Kategori</button>
            </form>
        </x-metronic.card>
    </div>

    <div class="col-xl-8">
        <x-metronic.data-table title="Daftar Kategori Dokumen" :headers="['Nama', 'Fase', 'Status', 'Aksi']">
            @forelse($categories as $category)
                <tr>
                    <td>
                        <div class="fw-bold text-gray-900">{{ $category->name }}</div>
                        <div class="fs-8 text-muted">{{ $category->description ?? 'Tanpa deskripsi' }}</div>
                    </td>
                    <td><span class="badge badge-light-info">{{ $category->required_for_phase ?: 'Semua fase' }}</span></td>
                    <td>
                        <span class="badge badge-light-{{ $category->is_active ? 'success' : 'secondary' }}">{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('superadmin.master-data.document-categories.update', $category) }}" class="d-grid gap-2 mb-2">
                            @csrf @method('PUT')
                            <input name="name" value="{{ $category->name }}" class="form-control form-control-sm form-control-solid" placeholder="Nama">
                            <input name="required_for_phase" value="{{ $category->required_for_phase }}" class="form-control form-control-sm form-control-solid" placeholder="Fase">
                            <textarea name="description" rows="2" class="form-control form-control-sm form-control-solid" placeholder="Deskripsi">{{ $category->description }}</textarea>
                            <label class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($category->is_active)>
                                <span class="form-check-label">Aktif</span>
                            </label>
                            <button class="btn btn-sm btn-light-primary">Simpan</button>
                        </form>
                        <div class="d-flex gap-2">
                            <form method="POST" action="{{ route('superadmin.master-data.document-categories.toggle', $category) }}">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-light-warning">Toggle</button>
                            </form>
                            <form method="POST" action="{{ route('superadmin.master-data.document-categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori dokumen ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-light-danger">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-8">Belum ada kategori dokumen.</td></tr>
            @endforelse
        </x-metronic.data-table>
    </div>
</div>
@endsection
