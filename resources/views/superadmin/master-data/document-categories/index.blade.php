@extends('layouts.metronic.app')

@section('title', 'Aturan Dokumen')
@section('pageTitle', 'Aturan Dokumen Akreditasi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-6">
    <div>
        <h2 class="fs-3 fw-bold text-gray-900 mb-1">Atur Kategori, Template, dan Akses Dokumen</h2>
        <p class="fs-7 text-muted mb-0">Tentukan dokumen apa yang dibutuhkan, siapa yang boleh melihat, dan apakah dokumen memiliki template acuan.</p>
    </div>
    <a href="{{ route('superadmin.master-data.index') }}" class="btn btn-sm btn-light"><i class="ki-outline ki-left fs-4"></i>Kembali</a>
</div>

@if(session('success'))
    <x-metronic.alert type="success" :message="session('success')" />
@endif

@if($errors->any())
    <x-metronic.alert type="danger">
        <div class="fw-semibold mb-2">Periksa kembali input aturan dokumen:</div>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-metronic.alert>
@endif

<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $stats['total'] ?? 0 }}" label="Total Kategori" icon="ki-document" color="primary" /></div>
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $stats['active'] ?? 0 }}" label="Aktif" icon="ki-shield-tick" color="success" /></div>
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $stats['with_template'] ?? 0 }}" label="Dengan Template" icon="ki-file-up" color="info" /></div>
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $stats['missing_rules'] ?? 0 }}" label="Belum Ada Rule" icon="ki-warning" color="warning" /></div>
</div>

<div class="row g-5 g-xl-8 mb-8">
    @foreach($presets as $preset)
        <div class="col-xl-4">
            <div class="card card-flush h-100 border border-dashed border-gray-300">
                <div class="card-body p-6">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="fw-bold text-gray-900">{{ $preset['title'] }}</div>
                            <div class="fs-8 text-muted font-monospace">{{ $preset['code'] }}</div>
                        </div>
                        <span class="badge badge-light-primary">Contoh rule</span>
                    </div>
                    <p class="fs-8 text-muted mb-4">{{ $preset['description'] }}</p>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($preset['roles'] as $role)
                            <span class="badge badge-light-info">{{ $roleOptions[$role] ?? $role }}</span>
                        @endforeach
                        @if($preset['scope'])
                            <span class="badge badge-light-warning">{{ $asesorScopeOptions[$preset['scope']] ?? $preset['scope'] }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-5 g-xl-8">
    <div class="col-xl-4">
        <x-metronic.card title="Buat Aturan Dokumen">
            <div class="mb-6 rounded bg-light-primary p-4">
                <div class="fw-semibold text-primary mb-1">Cara pakai</div>
                <div class="fs-8 text-muted">Isi nama dokumen, pilih role yang boleh mengakses, lalu tentukan cakupan asesor bila role Asesor dipilih.</div>
            </div>

            <form method="POST" action="{{ route('superadmin.master-data.document-categories.store') }}" enctype="multipart/form-data" class="d-grid gap-4" data-swal-confirm="true" data-swal-title="Simpan aturan dokumen baru?" data-swal-text="Aturan dokumen baru akan ditambahkan ke master data." data-swal-icon="question" data-swal-confirm-button="Ya, simpan" data-swal-confirm-class="btn btn-primary">
                @csrf
                <x-metronic.form-input name="name" label="Nama Kategori" :required="true" placeholder="Contoh: Kartu Kendali" />
                <x-metronic.form-input name="code" label="Kode Dokumen" placeholder="kartu_kendali" />

                <div>
                    <label class="form-label">Fase Wajib</label>
                    <select name="required_for_phase" class="form-select form-select-solid">
                        @foreach($phaseOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('required_for_phase') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="fs-8 text-muted mt-1">Kosongkan untuk berlaku di semua fase.</div>
                </div>

                <div>
                    <label class="form-label required">Role yang dapat melihat/mengakses</label>
                    <div class="d-grid gap-2">
                        @foreach($roleOptions as $role => $label)
                            <label class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="visible_to_roles[]" value="{{ $role }}" @checked(in_array($role, old('visible_to_roles', []), true))>
                                <span class="form-check-label">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="form-label">Cakupan Asesor</label>
                    <select name="asesor_scope" class="form-select form-select-solid">
                        @foreach($asesorScopeOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('asesor_scope', 'all') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="fs-8 text-muted mt-1">Dipakai hanya bila role Asesor dicentang.</div>
                </div>

                <x-metronic.form-input name="description" label="Deskripsi" type="textarea" :rows="3" placeholder="Jelaskan fungsi dan kapan dokumen ini digunakan" />

                <div>
                    <label class="form-label">Template / Contoh Dokumen</label>
                    <input type="file" name="template" class="form-control form-control-solid" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">
                    <div class="fs-8 text-muted mt-1">Opsional. PDF/DOC/XLS/Gambar maksimal 10MB.</div>
                </div>

                <label class="form-check form-check-custom form-check-solid">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                    <span class="form-check-label">Aktifkan aturan ini</span>
                </label>

                <button type="submit" class="btn btn-primary w-100">Simpan Aturan Dokumen</button>
            </form>
        </x-metronic.card>
    </div>

    <div class="col-xl-8">
        <x-metronic.card title="Daftar Aturan Dokumen" flush>
            <x-slot:header>
                <span class="badge badge-light-primary">{{ $categories->count() }} aturan</span>
            </x-slot:header>

            <div class="d-grid gap-5">
                @forelse($categories as $category)
                    @php
                        $roles = $category->visible_to_roles ?: [];
                    @endphp
                    <div class="border rounded p-5 bg-white">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-4 mb-4">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="fw-bold text-gray-900 fs-6">{{ $category->name }}</span>
                                    <span class="badge badge-light-{{ $category->is_active ? 'success' : 'secondary' }}">{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                </div>
                                <div class="fs-8 text-muted font-monospace">{{ $category->code ?: 'tanpa_kode' }}</div>
                                <div class="fs-7 text-muted mt-2">{{ $category->description ?? 'Tanpa deskripsi' }}</div>
                            </div>
                            <div class="d-flex align-items-start gap-3">
                                <div class="text-end">
                                    <span class="badge badge-light-info">{{ $category->required_for_phase ? ($phaseOptions[$category->required_for_phase] ?? $category->required_for_phase) : 'Semua fase' }}</span>
                                    @if($category->template_path)
                                        <div class="fs-8 text-success mt-2"><i class="ki-outline ki-file fs-6"></i> Template tersedia</div>
                                    @else
                                        <div class="fs-8 text-muted mt-2">Belum ada template</div>
                                    @endif
                                </div>
                                <x-superadmin.action-menu label="Buka aksi kategori {{ $category->name }}">
                                    <div class="menu-item px-3">
                                        <button type="button" class="menu-link px-3 d-flex align-items-center gap-2 border-0 bg-transparent w-100 text-start" data-bs-toggle="modal" data-bs-target="#edit-document-category-{{ $category->id }}">
                                            <i class="ki-outline ki-pencil fs-4"></i>
                                            <span>Edit Aturan</span>
                                        </button>
                                    </div>
                                    <div class="menu-item px-3">
                                        <form method="POST"
                                              action="{{ route('superadmin.master-data.document-categories.toggle', $category) }}"
                                              data-swal-confirm="true"
                                              data-swal-title="{{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }} kategori dokumen?"
                                              data-swal-text="Status aturan dokumen {{ $category->name }} akan diubah."
                                              data-swal-icon="warning"
                                              data-swal-confirm-button="Ya, ubah status"
                                              data-swal-confirm-class="btn btn-warning">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="menu-link px-3 d-flex align-items-center gap-2 border-0 bg-transparent w-100 text-start text-warning">
                                                <i class="ki-outline ki-switch fs-4"></i>
                                                <span>{{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</span>
                                            </button>
                                        </form>
                                    </div>
                                    <div class="separator my-2"></div>
                                    <div class="menu-item px-3">
                                        <form method="POST"
                                              action="{{ route('superadmin.master-data.document-categories.destroy', $category) }}"
                                              data-swal-confirm="true"
                                              data-swal-title="Hapus kategori dokumen?"
                                              data-swal-text="Kategori {{ $category->name }} akan dihapus dari master data. Aksi ini tidak dapat dibatalkan."
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
                        </div>

                        <div class="d-flex flex-wrap gap-2 mb-5">
                            @forelse($roles as $role)
                                <span class="badge badge-light-primary">{{ $roleOptions[$role] ?? $role }}</span>
                            @empty
                                <span class="badge badge-light-warning">Belum ada role akses</span>
                            @endforelse
                            @if(in_array('asesor', $roles, true))
                                <span class="badge badge-light-warning">Asesor: {{ $category->getAsesorScopeLabel() }}</span>
                            @endif
                        </div>

                    </div>

                    <x-metronic.modal id="edit-document-category-{{ $category->id }}" title="Edit Aturan Dokumen" size="xl" scrollable>
                        <form id="edit-document-category-form-{{ $category->id }}"
                              method="POST"
                              action="{{ route('superadmin.master-data.document-categories.update', $category) }}"
                              enctype="multipart/form-data"
                              class="row g-4"
                              data-swal-confirm="true"
                              data-swal-title="Simpan perubahan aturan dokumen?"
                              data-swal-text="Aturan {{ $category->name }} akan diperbarui dan perubahan tercatat sebagai aktivitas Super Admin."
                              data-swal-icon="question"
                              data-swal-confirm-button="Ya, simpan"
                              data-swal-confirm-class="btn btn-primary">
                            @csrf @method('PUT')
                            <div class="col-md-6">
                                <label class="form-label">Nama</label>
                                <input name="name" value="{{ $category->name }}" class="form-control form-control-solid" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kode</label>
                                <input name="code" value="{{ $category->code }}" class="form-control form-control-solid">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fase</label>
                                <select name="required_for_phase" class="form-select form-select-solid">
                                    @foreach($phaseOptions as $value => $label)
                                        <option value="{{ $value }}" @selected(($category->required_for_phase ?? '') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Cakupan Asesor</label>
                                <select name="asesor_scope" class="form-select form-select-solid">
                                    @foreach($asesorScopeOptions as $value => $label)
                                        <option value="{{ $value }}" @selected(($category->asesor_scope ?: 'all') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Ganti Template</label>
                                <input type="file" name="template" class="form-control form-control-solid" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Role Akses</label>
                                <div class="d-flex flex-wrap gap-4">
                                    @foreach($roleOptions as $role => $label)
                                        <label class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" name="visible_to_roles[]" value="{{ $role }}" @checked(in_array($role, $roles, true))>
                                            <span class="form-check-label">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                    <label class="form-check form-check-custom form-check-solid ms-auto">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($category->is_active)>
                                        <span class="form-check-label">Aktif</span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="description" rows="3" class="form-control form-control-solid">{{ $category->description }}</textarea>
                            </div>
                        </form>
                        <x-slot:footer>
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" form="edit-document-category-form-{{ $category->id }}" class="btn btn-primary">Simpan Perubahan</button>
                        </x-slot:footer>
                    </x-metronic.modal>
                @empty
                    <div class="text-center py-12 text-muted border rounded bg-light">Belum ada kategori dokumen. Buat aturan pertama dari form di sebelah kiri.</div>
                @endforelse
            </div>
        </x-metronic.card>
    </div>
</div>
@endsection
