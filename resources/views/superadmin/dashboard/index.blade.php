@extends('layouts.metronic.app')

@section('title', 'Super Admin Dashboard')
@section('pageTitle', 'Dashboard Super Admin')

@section('toolbar')
<form method="GET" action="{{ route('superadmin.dashboard') }}" class="d-flex flex-wrap align-items-center gap-2 gap-lg-3">
    <label for="dashboard_period" class="visually-hidden">Periode dashboard</label>
    <select id="dashboard_period" name="period" class="form-select form-select-solid form-select-sm w-auto" onchange="this.form.submit()">
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
    $completionRate = $totalAkreditasi > 0 ? round(($completedAkreditasi / $totalAkreditasi) * 100) : 0;
    $totalStatusRows = max($totalAkreditasi, 1);
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-start gap-4 mb-8">
    <div>
        <h2 class="fs-2 fw-bold text-gray-900 mb-2">Ringkasan Nasional Akreditasi</h2>
        <p class="fs-7 text-muted mb-0">Pantau beban kerja, status kritis, dan pengajuan terbaru dari satu tempat.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('superadmin.akreditasi.index') }}" class="btn btn-sm btn-primary">
            <i class="ki-outline ki-document fs-3"></i>Buka Workflow Console
        </a>
        <a href="{{ route('superadmin.master-data.index') }}" class="btn btn-sm btn-light">
            <i class="ki-outline ki-setting-2 fs-3"></i>Master Data
        </a>
    </div>
</div>

<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl-3 col-md-6">
        <x-metronic.stat-card value="{{ $totalAkreditasi }}" label="Total Akreditasi" icon="ki-document" color="primary" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-metronic.stat-card value="{{ $activeAkreditasi }}" label="Sedang Berjalan" icon="ki-timer" color="warning" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-metronic.stat-card value="{{ $completedAkreditasi }}" label="Selesai" icon="ki-shield-tick" color="success" progress="{{ $completionRate }}" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-metronic.stat-card value="{{ $overdueCount }}" label="Overdue" icon="ki-warning" color="danger" />
    </div>
</div>

<div class="card card-flush bg-light-primary border border-primary border-dashed mb-8">
    <div class="card-body p-7">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-5">
            <div class="d-flex align-items-start gap-4">
                <div class="symbol symbol-45px">
                    <span class="symbol-label bg-primary"><i class="ki-outline ki-compass fs-2 text-white"></i></span>
                </div>
                <div>
                    <h3 class="fw-bold text-gray-900 mb-1">Apa yang perlu dipantau hari ini?</h3>
                    <div class="fs-7 text-muted">Mulai dari kartu prioritas: review pengajuan baru, validasi akhir, banding, dan item yang melewati deadline.</div>
                </div>
            </div>
            <a href="{{ route('superadmin.akreditasi.index') }}" class="btn btn-sm btn-primary">Lihat Semua Pengajuan</a>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-gray-900 mb-0">Prioritas Hari Ini</h3>
    <span class="fs-8 text-muted">Buka kartu untuk masuk ke antrian terkait</span>
</div>

<div class="row g-5 g-xl-8 mb-8">
    @foreach($priorityCards as $card)
        <div class="col-xl col-md-6">
            <a href="{{ $card['route'] }}" class="card card-flush h-100 border border-gray-300 border-dashed hover-elevate-up text-decoration-none">
                <div class="card-body p-6">
                    <div class="d-flex align-items-center justify-content-between mb-5">
                        <span class="symbol symbol-40px">
                            <span class="symbol-label bg-light-{{ $card['color'] }}"><i class="ki-outline {{ $card['icon'] }} fs-2 text-{{ $card['color'] }}"></i></span>
                        </span>
                        <span class="badge badge-light-{{ $card['color'] }}">{{ $card['count'] }}</span>
                    </div>
                    <div class="fw-bold text-gray-900 mb-1">{{ $card['label'] }}</div>
                    <div class="fs-8 text-muted">{{ $card['description'] }}</div>
                </div>
            </a>
        </div>
    @endforeach
</div>

<div class="row g-5 g-xl-8">
    <div class="col-xl-8">
        <div class="card card-flush h-100">
            <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                <div class="card-title d-flex flex-column">
                    <h3 class="fw-bold text-gray-900 m-0">Distribusi Status Workflow</h3>
                    <span class="text-muted fs-7 mt-1">Klik status untuk membuka console dengan filter terkait.</span>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed table-row-gray-300 fs-6 gy-5">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-260px">Status</th>
                                <th class="min-w-180px">Progress</th>
                                <th class="text-end min-w-100px">Jumlah</th>
                                <th class="text-end min-w-120px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 fw-semibold">
                            @forelse($byStatus as $status => $data)
                                @php
                                    $color = $statusColors[$status] ?? 'secondary';
                                    $pct = round(($data['total'] / $totalStatusRows) * 100, 1);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <span class="badge badge-light-{{ $color }} w-fit-content">{{ $data['label'] }}</span>
                                            <span class="fs-8 text-muted font-monospace">{{ $status }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="progress h-8px flex-grow-1 bg-light-{{ $color }}">
                                                <div class="progress-bar bg-{{ $color }}" style="width: {{ $pct }}%"></div>
                                            </div>
                                            <span class="fs-8 text-muted min-w-45px">{{ $pct }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-end fw-bold text-gray-900">{{ $data['total'] }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('superadmin.akreditasi.index', ['status' => $status, 'period' => $period ?? 'all']) }}" class="btn btn-sm btn-light-primary">Lihat</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <div class="text-center py-12 text-muted border rounded bg-light">
                                            Belum ada data akreditasi untuk periode ini. Data akan muncul setelah pesantren mengajukan akreditasi.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card card-flush h-100">
            <div class="card-header py-5">
                <div class="card-title d-flex flex-column">
                    <h3 class="fw-bold text-gray-900 m-0">Aktivitas Terbaru</h3>
                    <span class="text-muted fs-7 mt-1">Pengajuan terakhir pada periode terpilih.</span>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="mb-8">
                    <div class="d-flex justify-content-between fw-semibold fs-7 mb-2">
                        <span>Completion Rate</span>
                        <span class="text-primary">{{ $completionRate }}%</span>
                    </div>
                    <div class="progress h-8px">
                        <div class="progress-bar bg-primary" style="width: {{ $completionRate }}%"></div>
                    </div>
                </div>

                <div class="d-grid gap-5">
                    @forelse($recentAkreditasis as $akreditasi)
                        @php $color = $statusColors[$akreditasi->status] ?? 'secondary'; @endphp
                        <div class="d-flex align-items-start gap-4">
                            <div class="symbol symbol-35px flex-shrink-0">
                                <span class="symbol-label bg-light-{{ $color }}"><i class="ki-outline ki-document fs-5 text-{{ $color }}"></i></span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold text-gray-900">{{ $akreditasi->user?->pesantren?->nama_pesantren ?? $akreditasi->user?->name ?? 'Pesantren' }}</div>
                                <div class="d-flex flex-wrap gap-2 mt-1">
                                    <span class="badge badge-light-{{ $color }}">{{ $akreditasi->getStatusLabel() }}</span>
                                    <span class="fs-8 text-muted">{{ $akreditasi->created_at?->format('d M Y') }}</span>
                                </div>
                            </div>
                            <a href="{{ route('superadmin.akreditasi.show', $akreditasi) }}" class="btn btn-sm btn-icon btn-light" aria-label="Lihat detail akreditasi {{ $akreditasi->uuid }}">
                                <i class="ki-outline ki-right fs-3"></i>
                            </a>
                        </div>
                    @empty
                        <div class="text-center py-10 text-muted border rounded bg-light">Belum ada aktivitas akreditasi untuk periode ini.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
