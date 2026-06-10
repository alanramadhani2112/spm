@extends('layouts.metronic.app')

@section('title', 'Super Admin Dashboard')
@section('pageTitle', 'Dashboard Super Admin')

@section('toolbar')
<form method="GET" action="{{ route('superadmin.dashboard') }}" class="d-flex align-items-center gap-2 gap-lg-3">
    <select name="period" class="form-select form-select-solid form-select-sm w-auto" data-placeholder="Periode" onchange="this.form.submit()">
        @foreach(($periodOptions ?? ['all' => 'Semua Periode']) as $value => $label)
            <option value="{{ $value }}" @selected(($period ?? 'all') == $value)>{{ $label }}</option>
        @endforeach
    </select>
    <a href="{{ route('superadmin.dashboard.export', ['period' => $period ?? 'all']) }}" class="btn btn-sm btn-light">
        <i class="ki-outline ki-exit-up fs-2"></i>Export CSV
    </a>
</form>
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

    $completionRate = $totalAkreditasi > 0 ? round(($completedAkreditasi / $totalAkreditasi) * 100) : 0;
@endphp

<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl-3 col-md-6">
        <x-metronic.stat-card value="{{ $totalAkreditasi }}" label="Total Akreditasi" icon="ki-document" color="primary" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-metronic.stat-card value="{{ $activeAkreditasi }}" label="Akreditasi Aktif" icon="ki-timer" color="warning" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-metronic.stat-card value="{{ $completedAkreditasi }}" label="Selesai" icon="ki-shield-tick" color="success" progress="{{ $completionRate }}" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-metronic.stat-card value="{{ $overdueCount }}" label="Terlambat" icon="ki-warning" color="danger" />
    </div>
</div>

<div class="row g-5 g-xl-8">
    <div class="col-xl-8">
        <div class="card card-flush">
            <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                <div class="card-title">
                    <h3 class="fw-bold text-gray-900 m-0">Rincian Status Nasional</h3>
                    <span class="text-muted fs-7 ms-3">Distribusi seluruh proses akreditasi per status workflow</span>
                </div>
                <div class="card-toolbar">
                    <div class="d-flex align-items-center position-relative my-1">
                        <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                        <input type="text" data-kt-sa-search="search" class="form-control form-control-solid w-200px ps-12" placeholder="Cari status...">
                    </div>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed table-row-gray-300 fs-6 gy-5">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-300px">Status</th>
                                <th class="text-end min-w-100px">Jumlah</th>
                                <th class="text-end min-w-150px">Persentase</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            @php $total = max($totalAkreditasi, 1); @endphp
                            @forelse($byStatus as $status => $data)
                                @php
                                    $color = $statusColors[$status] ?? 'secondary';
                                    $pct = round(($data['total'] / $total) * 100, 1);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge badge-light-{{ $color }} me-3">{{ $data['label'] }}</span>
                                            <div class="progress h-8px w-100 bg-light-{{ $color }}">
                                                <div class="progress-bar bg-{{ $color }}" style="width: {{ $pct }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end fw-bold text-gray-900">{{ $data['total'] }}</td>
                                    <td class="text-end text-muted">{{ $pct }}%</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center py-10 text-muted">Belum ada data akreditasi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card card-flush bg-light-primary h-100">
            <div class="card-body d-flex flex-column p-9">
                <i class="ki-outline ki-chart-pie-4 fs-3x text-primary mb-5"></i>
                <h3 class="fw-bold text-gray-900 mb-2">Kesehatan Sistem</h3>
                <div class="text-muted fs-7 mb-8">Monitoring aktivitas, penyelesaian, dan risiko keterlambatan.</div>

                <div class="mb-6">
                    <div class="d-flex justify-content-between fw-semibold fs-7 mb-2">
                        <span>Completion Rate</span>
                        <span class="text-primary">{{ $completionRate }}%</span>
                    </div>
                    <div class="progress h-8px">
                        <div class="progress-bar bg-primary" style="width: {{ $completionRate }}%"></div>
                    </div>
                </div>

                <div class="separator separator-dashed my-6"></div>

                <div class="d-flex flex-column gap-6">
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-35px me-3">
                            <span class="symbol-label bg-light-primary"><i class="ki-outline ki-timer fs-5 text-primary"></i></span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-gray-900 fs-6">{{ $activeAkreditasi }}</span>
                            <span class="text-muted fs-8">Akreditasi Aktif</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-35px me-3">
                            <span class="symbol-label bg-light-success"><i class="ki-outline ki-verify fs-5 text-success"></i></span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-gray-900 fs-6">{{ $completedAkreditasi }}</span>
                            <span class="text-muted fs-8">Akreditasi Selesai</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-35px me-3">
                            <span class="symbol-label bg-light-danger"><i class="ki-outline ki-warning-2 fs-5 text-danger"></i></span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-gray-900 fs-6">{{ $overdueCount }}</span>
                            <span class="text-muted fs-8">Terlambat</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
