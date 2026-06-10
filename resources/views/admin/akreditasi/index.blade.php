@extends('layouts.metronic.app')

@section('title', 'Admin — Dashboard Akreditasi')
@section('pageTitle', 'Dashboard Akreditasi Admin')

@section('toolbar')
<form method="GET" action="{{ route('admin.akreditasi.index') }}" class="d-flex align-items-center gap-2 gap-lg-3">
    <select name="period" class="form-select form-select-solid form-select-sm w-auto" onchange="this.form.submit()">
        @foreach(($periodOptions ?? ['all' => 'Semua Periode']) as $value => $label)
            <option value="{{ $value }}" @selected(($period ?? 'all') == $value)>{{ $label }}</option>
        @endforeach
    </select>
    <a href="{{ route('admin.akreditasi.export', ['period' => $period ?? 'all', 'tab' => request('tab', 'semua')]) }}" class="btn btn-sm btn-light">
        <i class="ki-outline ki-exit-up fs-2"></i>Export CSV
    </a>
</form>
@endsection

@section('content')
@php
    use App\Models\Akreditasi;
    use Illuminate\Support\Str;

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

    $filterTabs = [
        'semua' => ['label' => 'Semua', 'statuses' => [], 'color' => 'primary'],
        'pengajuan' => ['label' => 'Pengajuan Awal', 'statuses' => [Akreditasi::STATUS_INITIAL_SUBMITTED, Akreditasi::STATUS_INITIAL_REJECTED], 'color' => 'primary'],
        'review-tahap1' => ['label' => 'Review Tahap 1', 'statuses' => [Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW, Akreditasi::STATUS_ADMIN_STAGE_1_CORRECTION, Akreditasi::STATUS_ADMIN_STAGE_1_LIMIT_REVIEW], 'color' => 'warning'],
        'assign' => ['label' => 'Assign Asesor', 'statuses' => [Akreditasi::STATUS_ASSESSOR_ASSIGNMENT], 'color' => 'info'],
        'validasi-akhir' => ['label' => 'Validasi Akhir', 'statuses' => [Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED, Akreditasi::STATUS_ADMIN_FINAL_VALIDATION], 'color' => 'success'],
        'banding' => ['label' => 'Banding', 'statuses' => [Akreditasi::STATUS_APPEAL_SUBMITTED], 'color' => 'warning'],
    ];

    $activeTab = request()->query('tab', 'semua');
    $filtered = $akreditasis;
    if ($activeTab !== 'semua' && isset($filterTabs[$activeTab])) {
        $activeStatuses = $filterTabs[$activeTab]['statuses'];
        $filtered = $akreditasis->filter(fn($a) => in_array($a->status, $activeStatuses, true));
    }

    $initialCount = $akreditasis->where('status', Akreditasi::STATUS_INITIAL_SUBMITTED)->count();
    $reviewCount = $akreditasis->whereIn('status', [Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW, Akreditasi::STATUS_ADMIN_STAGE_1_LIMIT_REVIEW])->count();
    $assignmentCount = $akreditasis->where('status', Akreditasi::STATUS_ASSESSOR_ASSIGNMENT)->count();
    $finalCount = $akreditasis->whereIn('status', [Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED, Akreditasi::STATUS_ADMIN_FINAL_VALIDATION])->count();
@endphp

<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $akreditasis->count() }}" label="Akreditasi Aktif" icon="ki-folder" color="primary" /></div>
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $initialCount }}" label="Pengajuan Baru" icon="ki-message-question" color="info" /></div>
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $reviewCount }}" label="Review Tahap 1" icon="ki-notepad-edit" color="warning" /></div>
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $finalCount }}" label="Validasi Akhir" icon="ki-verify" color="success" /></div>
</div>

<div class="card card-flush">
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                <input type="text" data-kt-admin-search="search" class="form-control form-control-solid w-250px ps-12" placeholder="Cari akreditasi atau pesantren...">
            </div>
        </div>
        <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
            <div class="w-150px">
                <select class="form-select form-select-solid" data-kt-admin-search="status" data-control="select2" data-placeholder="Semua Status">
                    <option value="all">Semua Status</option>
                    @foreach($filterTabs as $key => $tab)
                        @if($key !== 'semua')
                            <option value="{{ $key }}">{{ $tab['label'] }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="card-body pt-0">
        <ul class="nav nav-pills nav-pills-custom mb-8 gap-2" role="tablist">
            @foreach($filterTabs as $key => $tab)
                @php
                    $count = $key === 'semua' ? $akreditasis->count() : $akreditasis->whereIn('status', $tab['statuses'])->count();
                    $isActive = $activeTab === $key;
                @endphp
                <li class="nav-item" role="presentation">
                    <a href="{{ route('admin.akreditasi.index', ['tab' => $key, 'period' => $period ?? 'all']) }}" class="nav-link btn btn-sm {{ $isActive ? 'btn-primary' : 'btn-light btn-color-gray-600' }} fw-semibold">
                        {{ $tab['label'] }}
                        <span class="badge {{ $isActive ? 'badge-light' : 'badge-light-' . $tab['color'] }} ms-2">{{ $count }}</span>
                    </a>
                </li>
            @endforeach
        </ul>

        @if($filtered->isEmpty())
            <x-metronic.alert type="primary" message="Tidak ada pengajuan akreditasi pada filter ini." />
        @else
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed table-row-gray-300 fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-200px">UUID / Pesantren</th>
                            <th class="min-w-120px">Status</th>
                            <th class="min-w-80px">Siklus</th>
                            <th class="min-w-100px">Tanggal</th>
                            <th class="text-end min-w-200px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        @foreach($filtered as $akreditasi)
                            @php $color = $statusColors[$akreditasi->status] ?? 'secondary'; @endphp
                            <tr>
                                <td data-kt-admin-search="uuid">
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-gray-900 mb-1" data-kt-admin-search="uuid-text">{{ Str::limit($akreditasi->uuid, 16, '...') }}</span>
                                        <span class="text-muted fs-7" data-kt-admin-search="pesantren-text">{{ $akreditasi->user?->pesantren?->nama_pesantren ?? '—' }}</span>
                                    </div>
                                </td>
                                <td><span class="badge badge-light-{{ $color }}">{{ $akreditasi->getStatusLabel() }}</span></td>
                                <td>{{ $akreditasi->correction_cycle ?? 0 }}</td>
                                <td>{{ $akreditasi->created_at->format('d M Y') }}</td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        @if($akreditasi->status === Akreditasi::STATUS_INITIAL_SUBMITTED)
                                            <a href="{{ route('admin.akreditasi.review-awal', $akreditasi->id) }}" class="btn btn-sm btn-light-primary flex-shrink-0">Review Awal</a>
                                            <a href="{{ route('admin.akreditasi.buka-assessment', $akreditasi->id) }}" class="btn btn-sm btn-light-primary flex-shrink-0">Assessment</a>
                                        @endif
                                        @if(in_array($akreditasi->status, [Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW, Akreditasi::STATUS_ADMIN_STAGE_1_LIMIT_REVIEW]))
                                            <a href="{{ route('admin.akreditasi.review-tahap1', $akreditasi->id) }}" class="btn btn-sm btn-light-warning flex-shrink-0">Review Tahap 1</a>
                                        @endif
                                        @if(in_array($akreditasi->status, [Akreditasi::STATUS_ASSESSOR_ASSIGNMENT, Akreditasi::STATUS_ADMIN_STAGE_1_CORRECTION]))
                                            <a href="{{ route('admin.akreditasi.assign-asesor', $akreditasi->id) }}" class="btn btn-sm btn-light-info flex-shrink-0">Assign Asesor</a>
                                        @endif
                                        @if(in_array($akreditasi->status, [Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW, Akreditasi::STATUS_ASSESSOR_STAGE_2_CORRECTION, Akreditasi::STATUS_ASSESSOR_STAGE_2_LIMIT_REVIEW]))
                                            <a href="{{ route('admin.akreditasi.reassign-asesor', $akreditasi->id) }}" class="btn btn-sm btn-light-info flex-shrink-0">Reassign</a>
                                        @endif
                                        @if(in_array($akreditasi->status, [Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED, Akreditasi::STATUS_ADMIN_FINAL_VALIDATION]))
                                            <a href="{{ route('admin.akreditasi.validasi-akhir', $akreditasi->id) }}" class="btn btn-sm btn-light-success flex-shrink-0">Validasi Akhir</a>
                                        @endif
                                        @if($akreditasi->status === Akreditasi::STATUS_FINAL_APPROVED)
                                            <a href="{{ route('admin.akreditasi.terbitkan-sk', $akreditasi->id) }}" class="btn btn-sm btn-light-success flex-shrink-0">Terbitkan SK</a>
                                        @endif
                                        @if($akreditasi->status === Akreditasi::STATUS_APPEAL_SUBMITTED && $akreditasi->bandings()->where('status', 'pending')->exists())
                                            <a href="{{ route('admin.akreditasi.banding', $akreditasi->id) }}" class="btn btn-sm btn-light-warning flex-shrink-0">Review Banding</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
