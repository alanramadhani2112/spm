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
            <form method="POST" action="{{ route('superadmin.master-data.edpm.komponen.store') }}" class="d-grid gap-4" data-swal-confirm="true" data-swal-title="Tambah komponen EDPM?" data-swal-text="Komponen baru akan ditambahkan ke master EDPM/IPR." data-swal-icon="question" data-swal-confirm-button="Ya, tambah" data-swal-confirm-class="btn btn-primary">
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
                            <th class="text-end min-w-90px pe-4">Aksi</th>
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
                                <td class="text-end pe-4">
                                    <x-superadmin.action-menu label="Buka aksi komponen {{ $komponen->kode }}">
                                        <div class="menu-item px-3">
                                            <button type="button" class="menu-link px-3 d-flex align-items-center gap-2 border-0 bg-transparent w-100 text-start" data-bs-toggle="modal" data-bs-target="#edit-komponen-{{ $komponen->id }}">
                                                <i class="ki-outline ki-pencil fs-4"></i>
                                                <span>Edit Komponen</span>
                                            </button>
                                        </div>
                                        <div class="separator my-2"></div>
                                        <div class="menu-item px-3">
                                            <form method="POST"
                                                  action="{{ route('superadmin.master-data.edpm.komponen.destroy', $komponen) }}"
                                                  data-swal-confirm="true"
                                                  data-swal-title="Hapus komponen EDPM?"
                                                  data-swal-text="Komponen {{ $komponen->kode }} dan relasi butirnya akan dihapus. Aksi ini tidak dapat dibatalkan."
                                                  data-swal-icon="warning"
                                                  data-swal-confirm-button="Ya, hapus"
                                                  data-swal-confirm-class="btn btn-danger">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="menu-link px-3 d-flex align-items-center gap-2 border-0 bg-transparent w-100 text-start text-danger">
                                                    <i class="ki-outline ki-trash fs-4"></i>
                                                    <span>Hapus</span>
                                                </button>
                                            </form>
                                        </div>
                                    </x-superadmin.action-menu>

                                    <x-metronic.modal id="edit-komponen-{{ $komponen->id }}" title="Edit Komponen EDPM" size="sm">
                                        <form id="edit-komponen-form-{{ $komponen->id }}"
                                              method="POST"
                                              action="{{ route('superadmin.master-data.edpm.komponen.update', $komponen) }}"
                                              class="d-grid gap-4 text-start"
                                              data-swal-confirm="true"
                                              data-swal-title="Simpan perubahan komponen?"
                                              data-swal-text="Komponen {{ $komponen->kode }} akan diperbarui."
                                              data-swal-icon="question"
                                              data-swal-confirm-button="Ya, simpan"
                                              data-swal-confirm-class="btn btn-primary">
                                            @csrf @method('PUT')
                                            <div>
                                                <label class="form-label">Kode Komponen</label>
                                                <input name="kode" value="{{ $komponen->kode }}" class="form-control form-control-solid" required>
                                            </div>
                                            <div>
                                                <label class="form-label">Nama Komponen</label>
                                                <input name="nama" value="{{ $komponen->nama ?? $komponen->name }}" class="form-control form-control-solid" required>
                                            </div>
                                        </form>
                                        <x-slot:footer>
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" form="edit-komponen-form-{{ $komponen->id }}" class="btn btn-primary">Simpan</button>
                                        </x-slot:footer>
                                    </x-metronic.modal>
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
            <form method="POST" action="{{ route('superadmin.master-data.edpm.butir.store') }}" class="d-grid gap-4" data-swal-confirm="true" data-swal-title="Tambah butir EDPM?" data-swal-text="Butir penilaian baru akan ditambahkan ke master EDPM/IPR." data-swal-icon="question" data-swal-confirm-button="Ya, tambah" data-swal-confirm-class="btn btn-success">
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
                                <div class="d-flex flex-wrap justify-content-between align-items-start gap-4 mb-3">
                                    <div>
                                        <div class="fw-semibold text-gray-900"><span class="badge badge-light-secondary me-2">{{ $butir->kode ?? '—' }}</span>{{ $butir->nama ?? $butir->name }}</div>
                                        <div class="fs-8 text-muted mt-1">{{ $butir->deskripsi ?? 'Tanpa deskripsi' }}</div>
                                    </div>
                                    <x-superadmin.action-menu label="Buka aksi butir {{ $butir->kode ?? $butir->id }}">
                                        <div class="menu-item px-3">
                                            <button type="button" class="menu-link px-3 d-flex align-items-center gap-2 border-0 bg-transparent w-100 text-start" data-bs-toggle="modal" data-bs-target="#edit-butir-{{ $butir->id }}">
                                                <i class="ki-outline ki-pencil fs-4"></i>
                                                <span>Edit Butir</span>
                                            </button>
                                        </div>
                                        <div class="separator my-2"></div>
                                        <div class="menu-item px-3">
                                            <form method="POST"
                                                  action="{{ route('superadmin.master-data.edpm.butir.destroy', $butir) }}"
                                                  data-swal-confirm="true"
                                                  data-swal-title="Hapus butir EDPM?"
                                                  data-swal-text="Butir {{ $butir->kode ?? $butir->id }} akan dihapus dari master penilaian."
                                                  data-swal-icon="warning"
                                                  data-swal-confirm-button="Ya, hapus"
                                                  data-swal-confirm-class="btn btn-danger">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="menu-link px-3 d-flex align-items-center gap-2 border-0 bg-transparent w-100 text-start text-danger">
                                                    <i class="ki-outline ki-trash fs-4"></i>
                                                    <span>Hapus</span>
                                                </button>
                                            </form>
                                        </div>
                                    </x-superadmin.action-menu>
                                </div>

                                <x-metronic.modal id="edit-butir-{{ $butir->id }}" title="Edit Butir EDPM" size="lg" scrollable>
                                    <form id="edit-butir-form-{{ $butir->id }}"
                                          method="POST"
                                          action="{{ route('superadmin.master-data.edpm.butir.update', $butir) }}"
                                          class="row g-4 text-start"
                                          data-swal-confirm="true"
                                          data-swal-title="Simpan perubahan butir?"
                                          data-swal-text="Butir {{ $butir->kode ?? $butir->id }} akan diperbarui."
                                          data-swal-icon="question"
                                          data-swal-confirm-button="Ya, simpan"
                                          data-swal-confirm-class="btn btn-primary">
                                        @csrf @method('PUT')
                                        <div class="col-md-6">
                                            <label class="form-label">Komponen</label>
                                            <select name="komponen_id" class="form-select form-select-solid">
                                                @foreach($komponens as $option)
                                                    <option value="{{ $option->id }}" @selected($butir->komponen_id == $option->id)>{{ $option->kode }} — {{ $option->nama ?? $option->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Kode</label>
                                            <input name="kode" value="{{ $butir->kode }}" class="form-control form-control-solid">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label required">Nama</label>
                                            <input name="nama" value="{{ $butir->nama ?? $butir->name }}" class="form-control form-control-solid" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Deskripsi</label>
                                            <textarea name="deskripsi" rows="3" class="form-control form-control-solid">{{ $butir->deskripsi }}</textarea>
                                        </div>
                                    </form>
                                    <x-slot:footer>
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" form="edit-butir-form-{{ $butir->id }}" class="btn btn-primary">Simpan Butir</button>
                                    </x-slot:footer>
                                </x-metronic.modal>
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
