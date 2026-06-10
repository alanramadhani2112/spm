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

    $statusColors = [
        Akreditasi::STATUS_DRAFT_PROFILE => 'secondary',
        Akreditasi::STATUS_INITIAL_SUBMITTED => 'primary',
        Akreditasi::STATUS_ASSESSMENT_OPEN => 'info',
        Akreditasi::STATUS_INITIAL_REJECTED => 'danger',
        Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW => 'warning',
        Akreditasi::STATUS_ADMIN_STAGE_1_CORRECTION => 'warning',
        Akreditasi::STATUS_ADMIN_STAGE_1_LIMIT_REVIEW => 'warning',
        Akreditasi::STATUS_ASSESSOR_ASSIGNMENT => 'info',
        Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW => 'warning',
        Akreditasi::STATUS_ASSESSOR_STAGE_2_CORRECTION => 'warning',
        Akreditasi::STATUS_ASSESSOR_STAGE_2_LIMIT_REVIEW => 'warning',
        Akreditasi::STATUS_VISITASI_SCHEDULED => 'info',
        Akreditasi::STATUS_VISITASI_COMPLETED => 'info',
        Akreditasi::STATUS_POST_VISITASI_SCORING => 'danger',
        Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED => 'primary',
        Akreditasi::STATUS_ADMIN_FINAL_VALIDATION => 'warning',
        Akreditasi::STATUS_FINAL_APPROVED => 'success',
        Akreditasi::STATUS_FINAL_REJECTED => 'danger',
        Akreditasi::STATUS_APPEAL_SUBMITTED => 'warning',
        Akreditasi::STATUS_COMPLETED => 'success',
    ];
@endphp

<x-metronic.card title="Semua Akreditasi" flush class="px-0 py-0">
    <x-slot name="toolbox">
        <span class="badge badge-light-primary fs-7">{{ $akreditasis->count() }} akreditasi</span>
    </x-slot>

    <div class="table-responsive">
        <table class="table table-striped table-row-bordered align-middle gs-0 gy-4">
            <thead>
                <tr class="fw-bold text-muted bg-light">
                    <th class="ps-4 min-w-50px">ID</th>
                    <th class="min-w-150px">Pesantren</th>
                    <th class="min-w-120px">Status</th>
                    <th class="min-w-120px">Tanggal</th>
                    <th class="min-w-200px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($akreditasis as $akreditasi)
                @php
                    $color = $statusColors[$akreditasi->status] ?? 'secondary';
                    $statusLabel = Akreditasi::STATUS_LABELS[$akreditasi->status] ?? $akreditasi->status;
                @endphp
                <tr>
                    <td class="ps-4">
                        <span class="text-muted fw-bold">#{{ $akreditasi->id }}</span>
                    </td>
                    <td>
                        <span class="fw-bold">{{ $akreditasi->user?->name ?? '—' }}</span>
                        <br><span class="text-muted fs-8">{{ $akreditasi->user?->email }}</span>
                    </td>
                    <td>
                        <span class="badge badge-light-{{ $color }}">{{ $statusLabel }}</span>
                    </td>
                    <td>
                        <span class="text-muted fs-7">{{ $akreditasi->created_at->format('d M Y') }}</span>
                    </td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            @if ($akreditasi->status === Akreditasi::STATUS_INITIAL_SUBMITTED)
                                <a href="{{ route('superadmin.akreditasi.review-awal', $akreditasi->id) }}" class="btn btn-sm btn-icon btn-light-primary">
                                    <i class="ki-outline ki-magnifier fs-6"></i>
                                </a>
                            @endif

                            @if ($akreditasi->status === Akreditasi::STATUS_ASSESSMENT_OPEN)
                                <a href="{{ route('superadmin.akreditasi.buka-assessment', $akreditasi->id) }}" class="btn btn-sm btn-icon btn-light-info">
                                    <i class="ki-outline ki-verify fs-6"></i>
                                </a>
                            @endif

                            @if (in_array($akreditasi->status, [Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW, Akreditasi::STATUS_ADMIN_STAGE_1_LIMIT_REVIEW]))
                                <a href="{{ route('superadmin.akreditasi.review-tahap1', $akreditasi->id) }}" class="btn btn-sm btn-icon btn-light-warning">
                                    <i class="ki-outline ki-check-circle fs-6"></i>
                                </a>
                            @endif

                            @if ($akreditasi->status === Akreditasi::STATUS_ASSESSOR_ASSIGNMENT)
                                <a href="{{ route('superadmin.akreditasi.assign-asesor', $akreditasi->id) }}" class="btn btn-sm btn-icon btn-light-info">
                                    <i class="ki-outline ki-user fs-6"></i>
                                </a>
                            @endif

                            @if (in_array($akreditasi->status, [Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW, Akreditasi::STATUS_ASSESSOR_STAGE_2_LIMIT_REVIEW]))
                                <a href="{{ route('superadmin.akreditasi.review-tahap2', $akreditasi->id) }}" class="btn btn-sm btn-icon btn-light-warning">
                                    <i class="ki-outline ki-verify fs-6"></i>
                                </a>
                            @endif

                            @if (in_array($akreditasi->status, [Akreditasi::STATUS_VISITASI_SCHEDULED, Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW]))
                                <a href="{{ route('superadmin.akreditasi.jadwalkan-visitasi', $akreditasi->id) }}" class="btn btn-sm btn-icon btn-light-info">
                                    <i class="ki-outline ki-calendar fs-6"></i>
                                </a>
                            @endif

                            @if ($akreditasi->status === Akreditasi::STATUS_POST_VISITASI_SCORING)
                                <a href="{{ route('superadmin.akreditasi.input-na1', $akreditasi->id) }}" class="btn btn-sm btn-light-danger">NA1</a>
                                <a href="{{ route('superadmin.akreditasi.input-na2', $akreditasi->id) }}" class="btn btn-sm btn-light-danger">NA2</a>
                                <a href="{{ route('superadmin.akreditasi.input-nk', $akreditasi->id) }}" class="btn btn-sm btn-light-danger">NK</a>
                            @endif

                            @if (in_array($akreditasi->status, [Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED, Akreditasi::STATUS_ADMIN_FINAL_VALIDATION]))
                                <a href="{{ route('superadmin.akreditasi.validasi-akhir', $akreditasi->id) }}" class="btn btn-sm btn-icon btn-light-warning">
                                    <i class="ki-outline ki-shield-tick fs-6"></i>
                                </a>
                            @endif

                            @if ($akreditasi->status === Akreditasi::STATUS_FINAL_APPROVED)
                                <span class="badge badge-light-success">Siap SK</span>
                            @endif

                            @if ($akreditasi->status === Akreditasi::STATUS_COMPLETED)
                                <span class="badge badge-light-success">Selesai</span>
                            @endif

                            @if ($akreditasi->status === Akreditasi::STATUS_FINAL_REJECTED || $akreditasi->status === Akreditasi::STATUS_APPEAL_SUBMITTED)
                                <span class="badge badge-light-danger">Banding</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-8 text-muted">
                        <i class="ki-outline ki-document fs-3tx d-block mb-3"></i>
                        Belum ada akreditasi.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-metronic.card>
@endsection
