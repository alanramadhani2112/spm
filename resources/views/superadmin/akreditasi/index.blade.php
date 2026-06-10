@extends('layouts.metronic.app')

@section('title', 'Superadmin — Semua Akreditasi')
@section('pageTitle', 'Manajemen Akreditasi')

@section('toolbar')
<div class="d-flex align-items-center gap-2 gap-lg-3">
    <a href="{{ route('superadmin.akreditasi.pengajuan') }}" class="btn btn-sm btn-primary">
        <i class="ki-outline ki-add-files fs-2"></i>Pengajuan Baru
    </a>
</div>
@endsection

@section('content')
@php
    use App\Models\Akreditasi;
@endphp

<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl col-md-4"><x-metronic.stat-card value="{{ $stats['total'] ?? $akreditasis->count() }}" label="Total" icon="ki-document" color="primary" /></div>
    <div class="col-xl col-md-4"><x-metronic.stat-card value="{{ $stats['active'] ?? 0 }}" label="Aktif" icon="ki-timer" color="warning" /></div>
    <div class="col-xl col-md-4"><x-metronic.stat-card value="{{ $stats['completed'] ?? 0 }}" label="Selesai" icon="ki-shield-tick" color="success" /></div>
    <div class="col-xl col-md-4"><x-metronic.stat-card value="{{ $stats['appeal'] ?? 0 }}" label="Banding" icon="ki-message-question" color="info" /></div>
    <div class="col-xl col-md-4"><x-metronic.stat-card value="{{ $stats['overdue'] ?? 0 }}" label="Overdue" icon="ki-warning" color="danger" /></div>
</div>

<x-metronic.card title="Workflow Console Akreditasi" flush>
    <x-slot:header>
        <span class="badge badge-light-primary">{{ $akreditasis->count() }} ditampilkan</span>
    </x-slot:header>

    <form method="GET" action="{{ route('superadmin.akreditasi.index') }}" class="row g-3 align-items-end mb-8">
        <div class="col-md-3">
            <label class="form-label">Periode</label>
            <select name="period" class="form-select form-select-solid">
                @foreach(($periodOptions ?? ['all' => 'Semua Periode']) as $value => $label)
                    <option value="{{ $value }}" @selected(($period ?? 'all') == $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select form-select-solid">
                <option value="all" @selected(($status ?? 'all') === 'all')>Semua Status</option>
                @foreach($statusOptions ?? Akreditasi::STATUS_LABELS as $key => $label)
                    <option value="{{ $key }}" @selected(($status ?? 'all') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Cari</label>
            <input type="search" name="q" value="{{ $search ?? '' }}" class="form-control form-control-solid" placeholder="UUID, pesantren, email...">
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-primary w-100">Filter</button>
            <a href="{{ route('superadmin.akreditasi.index') }}" class="btn btn-light">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped table-row-bordered align-middle gs-0 gy-4">
            <thead>
                <tr class="fw-bold text-muted bg-light">
                    <th class="ps-4 min-w-70px">ID</th>
                    <th class="min-w-220px">Pesantren</th>
                    <th class="min-w-170px">Status</th>
                    <th class="min-w-130px">Asesor</th>
                    <th class="min-w-130px">Nilai</th>
                    <th class="min-w-120px">Tanggal</th>
                    <th class="text-end min-w-240px pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($akreditasis as $akreditasi)
                    @php
                        $color = $statusColors[$akreditasi->status] ?? 'secondary';
                        $statusLabel = Akreditasi::STATUS_LABELS[$akreditasi->status] ?? $akreditasi->status;
                        $pendingBanding = $akreditasi->bandings->firstWhere('status', 'pending');
                    @endphp
                    <tr>
                        <td class="ps-4"><span class="text-muted fw-bold">#{{ $akreditasi->id }}</span></td>
                        <td>
                            <div class="fw-bold text-gray-900">{{ $akreditasi->user?->pesantren?->nama_pesantren ?? $akreditasi->user?->name ?? '—' }}</div>
                            <div class="text-muted fs-8">{{ $akreditasi->user?->email }} · {{ \Illuminate\Support\Str::limit($akreditasi->uuid, 18) }}</div>
                        </td>
                        <td><span class="badge badge-light-{{ $color }}">{{ $statusLabel }}</span></td>
                        <td>
                            @forelse($akreditasi->assessments as $assessment)
                                <div class="fs-8"><span class="badge badge-light-info me-1">{{ $assessment->tipe }}</span>{{ $assessment->asesor?->name ?? '—' }}</div>
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
                            <div class="d-flex flex-wrap justify-content-end gap-2">
                                <a href="{{ route('superadmin.akreditasi.show', $akreditasi->id) }}" class="btn btn-sm btn-light-primary">Detail</a>
                                @if ($akreditasi->status === Akreditasi::STATUS_INITIAL_SUBMITTED)
                                    <a href="{{ route('superadmin.akreditasi.review-awal', $akreditasi->id) }}" class="btn btn-sm btn-light">Review Awal</a>
                                @endif
                                @if (in_array($akreditasi->status, [Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW, Akreditasi::STATUS_ADMIN_STAGE_1_LIMIT_REVIEW], true))
                                    <a href="{{ route('superadmin.akreditasi.review-tahap1', $akreditasi->id) }}" class="btn btn-sm btn-light-warning">Tahap 1</a>
                                @endif
                                @if ($akreditasi->status === Akreditasi::STATUS_ASSESSOR_ASSIGNMENT)
                                    <a href="{{ route('superadmin.akreditasi.assign-asesor', $akreditasi->id) }}" class="btn btn-sm btn-light-info">Assign</a>
                                @endif
                                @if ($akreditasi->status === Akreditasi::STATUS_POST_VISITASI_SCORING)
                                    <a href="{{ route('superadmin.akreditasi.input-na1', $akreditasi->id) }}" class="btn btn-sm btn-light-danger">NA1</a>
                                    <a href="{{ route('superadmin.akreditasi.input-na2', $akreditasi->id) }}" class="btn btn-sm btn-light-danger">NA2</a>
                                    <a href="{{ route('superadmin.akreditasi.input-nk', $akreditasi->id) }}" class="btn btn-sm btn-light-danger">NK</a>
                                @endif
                                @if (in_array($akreditasi->status, [Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED, Akreditasi::STATUS_ADMIN_FINAL_VALIDATION], true))
                                    <a href="{{ route('superadmin.akreditasi.validasi-akhir', $akreditasi->id) }}" class="btn btn-sm btn-light-success">Validasi</a>
                                @endif
                                @if ($pendingBanding)
                                    <a href="{{ route('admin.akreditasi.banding', $akreditasi->id) }}" class="btn btn-sm btn-light-warning">Banding</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-8 text-muted">Belum ada akreditasi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-metronic.card>
@endsection
