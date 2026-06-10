@extends('layouts.metronic.app')

@section('title', 'Superadmin — Semua Akreditasi')
@section('pageTitle', 'Manajemen Akreditasi')

@section('toolbar')
<div class="d-flex flex-wrap align-items-center gap-2 gap-lg-3">
    <a href="{{ route('superadmin.akreditasi.export', request()->only(['period', 'status', 'q'])) }}" class="btn btn-sm btn-light">
        <i class="ki-outline ki-exit-up fs-2"></i>Export CSV
    </a>
    <a href="{{ route('superadmin.akreditasi.pengajuan') }}" class="btn btn-sm btn-primary">
        <i class="ki-outline ki-add-files fs-2"></i>Pengajuan Baru
    </a>
</div>
@endsection

@section('content')
@php
    use App\Models\Akreditasi;
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-start gap-4 mb-8">
    <div>
        <h2 class="fs-2 fw-bold text-gray-900 mb-2">Workflow Console Akreditasi</h2>
        <p class="fs-7 text-muted mb-0">Pantau semua pengajuan, temukan status kritis, dan jalankan aksi operasional dari satu console.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('superadmin.dashboard') }}" class="btn btn-sm btn-light"><i class="ki-outline ki-chart-pie-4 fs-3"></i>Dashboard</a>
        <a href="{{ route('superadmin.audit.index') }}" class="btn btn-sm btn-light"><i class="ki-outline ki-time fs-3"></i>Audit Log</a>
    </div>
</div>

<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl col-md-4"><x-metronic.stat-card value="{{ $stats['total'] ?? $akreditasis->count() }}" label="Total" icon="ki-document" color="primary" /></div>
    <div class="col-xl col-md-4"><x-metronic.stat-card value="{{ $stats['active'] ?? 0 }}" label="Aktif" icon="ki-timer" color="warning" /></div>
    <div class="col-xl col-md-4"><x-metronic.stat-card value="{{ $stats['completed'] ?? 0 }}" label="Selesai" icon="ki-shield-tick" color="success" /></div>
    <div class="col-xl col-md-4"><x-metronic.stat-card value="{{ $stats['appeal'] ?? 0 }}" label="Banding" icon="ki-message-question" color="info" /></div>
    <div class="col-xl col-md-4"><x-metronic.stat-card value="{{ $stats['overdue'] ?? 0 }}" label="Overdue" icon="ki-warning" color="danger" /></div>
</div>

<div class="card card-flush bg-light-primary border border-primary border-dashed mb-8">
    <div class="card-body p-7">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-5">
            <div class="d-flex align-items-start gap-4">
                <div class="symbol symbol-45px">
                    <span class="symbol-label bg-primary"><i class="ki-outline ki-compass fs-2 text-white"></i></span>
                </div>
                <div>
                    <h3 class="fw-bold text-gray-900 mb-1">Gunakan status sebagai petunjuk aksi berikutnya</h3>
                    <div class="fs-7 text-muted">Setiap baris menampilkan status, langkah berikutnya, asesor, nilai, dan aksi yang relevan. Mulai dari tombol <span class="fw-semibold text-gray-900">Detail</span> bila perlu melihat konteks lengkap.</div>
                </div>
            </div>
            <span class="badge badge-light-primary">{{ $akreditasis->count() }} data ditampilkan</span>
        </div>
    </div>
</div>

<x-metronic.card title="Filter & Daftar Akreditasi" flush>
    <x-slot:header>
        <div class="d-flex flex-wrap align-items-center gap-2">
            @if(($period ?? 'all') !== 'all')
                <span class="badge badge-light-info">Periode: {{ $period }}</span>
            @endif
            @if(($status ?? 'all') !== 'all')
                <span class="badge badge-light-warning">Status: {{ $statusOptions[$status] ?? $status }}</span>
            @endif
            @if(($search ?? '') !== '')
                <span class="badge badge-light-primary">Cari: {{ $search }}</span>
            @endif
        </div>
    </x-slot:header>

    <form method="GET" action="{{ route('superadmin.akreditasi.index') }}" class="row g-3 align-items-end mb-8">
        <div class="col-lg-3 col-md-6">
            <label for="filter_period" class="form-label">Periode</label>
            <select id="filter_period" name="period" class="form-select form-select-solid">
                @foreach(($periodOptions ?? ['all' => 'Semua Periode']) as $value => $label)
                    <option value="{{ $value }}" @selected(($period ?? 'all') == $value)>{{ $label }}</option>
                @endforeach
            </select>
            <div class="fs-8 text-muted mt-1">Batasi data berdasarkan tahun pengajuan.</div>
        </div>
        <div class="col-lg-3 col-md-6">
            <label for="filter_status" class="form-label">Status Workflow</label>
            <select id="filter_status" name="status" class="form-select form-select-solid">
                <option value="all" @selected(($status ?? 'all') === 'all')>Semua Status</option>
                @foreach($statusOptions ?? Akreditasi::STATUS_LABELS as $key => $label)
                    <option value="{{ $key }}" @selected(($status ?? 'all') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <div class="fs-8 text-muted mt-1">Pilih status untuk fokus pada antrian tertentu.</div>
        </div>
        <div class="col-lg-4 col-md-8">
            <label for="filter_search" class="form-label">Cari Pengajuan</label>
            <input id="filter_search" type="search" name="q" value="{{ $search ?? '' }}" class="form-control form-control-solid" placeholder="UUID, pesantren, NSP, email...">
            <div class="fs-8 text-muted mt-1">Gunakan nama pesantren, UUID, NSP, atau email.</div>
        </div>
        <div class="col-lg-2 col-md-4 d-flex gap-2">
            <button class="btn btn-primary flex-grow-1">Terapkan</button>
            <a href="{{ route('superadmin.akreditasi.index') }}" class="btn btn-light" aria-label="Reset filter akreditasi">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-row-bordered align-middle gs-0 gy-4">
            <thead>
                <tr class="fw-bold text-muted bg-light">
                    <th class="ps-4 min-w-80px">ID</th>
                    <th class="min-w-260px">Pesantren</th>
                    <th class="min-w-240px">Status dan Langkah Berikutnya</th>
                    <th class="min-w-190px">Asesor</th>
                    <th class="min-w-150px">Nilai</th>
                    <th class="min-w-130px">Tanggal</th>
                    <th class="text-end min-w-90px pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($akreditasis as $akreditasi)
                    @php
                        $color = $statusColors[$akreditasi->status] ?? 'secondary';
                        $statusLabel = Akreditasi::STATUS_LABELS[$akreditasi->status] ?? $akreditasi->status;
                        $nextStep = $nextStepLabels[$akreditasi->status] ?? 'Pantau status pengajuan';
                        $actions = $availableActionsById[$akreditasi->id] ?? [];
                    @endphp
                    <tr>
                        <td class="ps-4"><span class="text-muted fw-bold">#{{ $akreditasi->id }}</span></td>
                        <td>
                            <div class="fw-bold text-gray-900">{{ $akreditasi->user?->pesantren?->nama_pesantren ?? $akreditasi->user?->name ?? '—' }}</div>
                            <div class="text-muted fs-8">{{ $akreditasi->user?->email }} · {{ \Illuminate\Support\Str::limit($akreditasi->uuid, 18) }}</div>
                        </td>
                        <td>
                            <div class="d-flex flex-column gap-1">
                                <span class="badge badge-light-{{ $color }} w-fit-content">{{ $statusLabel }}</span>
                                <span class="fs-8 text-gray-700">{{ $nextStep }}</span>
                            </div>
                        </td>
                        <td>
                            @forelse($akreditasi->assessments as $assessment)
                                <div class="fs-8 mb-1"><span class="badge badge-light-info me-1">{{ strtoupper($assessment->tipe) }}</span>{{ $assessment->asesor?->name ?? '—' }}</div>
                            @empty
                                <span class="text-muted fs-8">Belum ditugaskan</span>
                            @endforelse
                        </td>
                        <td>
                            <div class="fs-8 text-muted">Nilai: <span class="fw-bold text-gray-900">{{ $akreditasi->nilai ?? '—' }}</span></div>
                            <div class="fs-8 text-muted">Peringkat: <span class="fw-bold text-gray-900">{{ $akreditasi->peringkat ?? '—' }}</span></div>
                        </td>
                        <td><span class="text-muted fs-7">{{ $akreditasi->created_at->format('d M Y') }}</span></td>
                        <td class="text-end pe-4">
                            <x-superadmin.action-menu label="Buka aksi akreditasi {{ $akreditasi->uuid }}">
                                <div class="menu-item px-3">
                                    <a href="{{ route('superadmin.akreditasi.show', $akreditasi->id) }}" class="menu-link px-3 d-flex align-items-center gap-2">
                                        <i class="ki-outline ki-eye fs-4"></i>
                                        <span>Detail</span>
                                    </a>
                                </div>
                                @forelse($actions as $action)
                                    <div class="menu-item px-3">
                                        <a href="{{ $action['route'] }}"
                                           class="menu-link px-3 d-flex align-items-center gap-2 text-{{ $action['color'] }}"
                                           data-swal-confirm="true"
                                           data-swal-title="Buka aksi {{ $action['label'] }}?"
                                           data-swal-text="Anda akan masuk ke halaman {{ $action['label'] }} untuk pengajuan {{ $akreditasi->uuid }}."
                                           data-swal-icon="question"
                                           data-swal-confirm-button="Ya, buka">
                                            <i class="ki-outline ki-right-square fs-4"></i>
                                            <span>{{ $action['label'] }}</span>
                                        </a>
                                    </div>
                                @empty
                                    <div class="menu-item px-3">
                                        <span class="menu-link px-3 text-muted">
                                            <i class="ki-outline ki-information-5 fs-4 me-2"></i>Tidak ada aksi
                                        </span>
                                    </div>
                                @endforelse
                            </x-superadmin.action-menu>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="text-center py-12 text-muted border rounded bg-light">
                                Belum ada akreditasi yang cocok dengan filter ini. Coba reset filter atau buat pengajuan baru.
                                <div class="mt-4">
                                    <a href="{{ route('superadmin.akreditasi.pengajuan') }}" class="btn btn-sm btn-primary">Buat Pengajuan</a>
                                    <a href="{{ route('superadmin.akreditasi.index') }}" class="btn btn-sm btn-light">Reset Filter</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-metronic.card>
@endsection
