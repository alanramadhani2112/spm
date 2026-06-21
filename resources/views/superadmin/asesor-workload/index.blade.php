@extends('layouts.metronic.app')

@section('title', 'Beban Kerja Asesor')
@section('pageTitle', 'Beban Kerja Asesor')

@section('toolbar')
<form method="GET" action="{{ route('superadmin.asesor-workload.index') }}" class="d-flex flex-wrap align-items-center gap-2 gap-lg-3">
    <label for="workload_period" class="visually-hidden">Periode workload</label>
    <select id="workload_period" name="period" class="form-select form-select-solid form-select-sm w-auto" onchange="this.form.submit()">
        @foreach($periodOptions as $value => $label)
            <option value="{{ $value }}" @selected($period === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <label for="workload_load" class="visually-hidden">Filter beban</label>
    <select id="workload_load" name="load" class="form-select form-select-solid form-select-sm w-auto" onchange="this.form.submit()">
        @foreach($loadOptions as $value => $label)
            <option value="{{ $value }}" @selected($load === $value)>{{ $label }}</option>
        @endforeach
    </select>
    @if(auth()->user()?->hasPermission('superadmin.assessor_workload.export'))
        <a href="{{ route('superadmin.asesor-workload.export', ['period' => $period, 'load' => $load]) }}" class="btn btn-sm btn-light-primary">
            <i class="ki-outline ki-file-down fs-2"></i>Ekspor CSV
        </a>
    @endif
    <a href="{{ route('superadmin.akreditasi.index', ['status' => \App\Models\Akreditasi::STATUS_ASSESSOR_ASSIGNMENT, 'period' => $period]) }}" class="btn btn-sm btn-primary">
        <i class="ki-outline ki-user-tick fs-2"></i>Queue Assign
    </a>
</form>
@endsection

@section('content')
    <x-superadmin.breadcrumb :items="[['label' => 'Dashboard', 'route' => 'superadmin.dashboard'], ['label' => 'Beban Kerja Asesor', 'active' => true]]" />

<div class="d-flex flex-wrap justify-content-between align-items-start gap-4 mb-8">
    <div class="mw-lg-600px">
        <h2 class="fs-2 fw-bold text-gray-900 mb-2">Pusat Beban Kerja Asesor</h2>
        <p class="fs-7 text-muted mb-3">Distribusi assignment aktif, peran ketua/anggota, status berjalan, dan sinyal overload.</p>
        <div class="d-flex flex-wrap gap-2">
            <span class="badge badge-light-primary">{{ $filteredRows->count() }} asesor ditampilkan</span>
            <span class="badge badge-light-{{ $summary['high'] > 0 ? 'danger' : 'success' }}">{{ $summary['high'] > 0 ? $summary['high'].' overload' : 'Tidak ada overload' }}</span>
            @if(($load ?? 'all') !== 'all')
                <span class="badge badge-light-warning">Filter beban aktif</span>
            @endif
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('superadmin.dashboard', ['period' => $period]) }}" class="btn btn-sm btn-light">
            <i class="ki-outline ki-category fs-3"></i>Dashboard
        </a>
        <a href="{{ route('superadmin.akreditasi.index', ['period' => $period]) }}" class="btn btn-sm btn-light-primary">
            <i class="ki-outline ki-document fs-3"></i>Workflow Console
        </a>
    </div>
</div>

@if($summary['high'] > 0)
    <div class="alert alert-dismissible bg-light-danger border border-danger border-dashed d-flex align-items-start gap-4 p-6 mb-8">
        <i class="ki-outline ki-warning-2 fs-2hx text-danger"></i>
        <div>
            <h3 class="fw-bold text-gray-900 mb-1">{{ $summary['high'] }} asesor overload</h3>
            <div class="fs-7 text-muted">Prioritaskan asesor dengan status normal saat melakukan assignment berikutnya.</div>
        </div>
    </div>
@else
    <div class="alert bg-light-success border border-success border-dashed d-flex align-items-start gap-4 p-6 mb-8">
        <i class="ki-outline ki-check-circle fs-2hx text-success"></i>
        <div>
            <h3 class="fw-bold text-gray-900 mb-1">Tidak ada asesor overload</h3>
            <div class="fs-7 text-muted">Distribusi aktif masih berada di bawah ambang tinggi.</div>
        </div>
    </div>
@endif

<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl-2 col-md-4 col-6">
        <x-metronic.stat-card value="{{ $summary['total_asesor'] }}" label="Total Asesor" icon="ki-people" color="primary" />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-metronic.stat-card value="{{ $summary['normal'] }}" label="Normal" icon="ki-check-circle" color="success" />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-metronic.stat-card value="{{ $summary['medium'] }}" label="Medium" icon="ki-timer" color="warning" />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-metronic.stat-card value="{{ $summary['high'] }}" label="Overload" icon="ki-warning" color="danger" />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-metronic.stat-card value="{{ $summary['active_assignments'] }}" label="Assignment Aktif" icon="ki-briefcase" color="info" />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-metronic.stat-card value="{{ $summary['overdue_assignments'] }}" label="Overdue" icon="ki-calendar-remove" color="danger" />
    </div>
</div>

<div class="card card-flush">
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title d-flex flex-column">
            <h3 class="fw-bold text-gray-900 m-0">Distribusi Beban Asesor</h3>
            <span class="text-muted fs-7 mt-1">{{ $filteredRows->count() }} dari {{ $rows->count() }} asesor ditampilkan.</span>
        </div>
        <div class="card-toolbar">
            <span class="fs-8 text-muted">Gunakan menu aksi untuk membuka detail workload atau queue assignment.</span>
        </div>
    </div>
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed table-row-gray-300 fs-6 gy-5 mb-0">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-220px">Asesor</th>
                        <th class="min-w-180px">Beban</th>
                        <th class="min-w-150px">Peran</th>
                        <th class="min-w-220px">Status Aktif</th>
                        <th class="min-w-160px">Risiko</th>
                        <th class="min-w-220px">Assignment Terbaru</th>
                        <th class="text-end min-w-100px">Aksi</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-700">
                    @forelse($filteredRows as $row)
                        @php
                            $progress = min(100, $row['total'] * 20);
                            $latest = $row['latest_assignment'];
                            $latestAkreditasi = $latest?->akreditasi;
                            $riskColor = $row['overdue'] > 0 ? 'danger' : $row['color'];
                            $riskLabel = $row['overdue'] > 0
                                ? 'Overdue '.$row['overdue']
                                : ($row['level'] === 'high' ? 'Butuh redistribusi' : ($row['level'] === 'medium' ? 'Pantau distribusi' : 'Siap assignment'));
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3 min-w-0">
                                    <span class="symbol symbol-40px flex-shrink-0">
                                        <span class="symbol-label bg-light-{{ $row['color'] }} text-{{ $row['color'] }} fw-bold">{{ strtoupper(substr($row['name'], 0, 1)) }}</span>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="fw-bold text-gray-900 text-truncate">{{ $row['name'] }}</div>
                                        <div class="fs-8 text-muted text-truncate">{{ $row['email'] ?? 'Email belum tersedia' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
                                    <span class="fw-bold text-gray-900">Aktif {{ $row['total'] }}</span>
                                    <span class="badge badge-light-{{ $row['color'] }}">{{ $row['capacity_note'] }}</span>
                                </div>
                                <div class="progress h-8px bg-light-{{ $row['color'] }}">
                                    <div class="progress-bar bg-{{ $row['color'] }}" style="width: {{ $progress }}%"></div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <span>Ketua {{ $row['ketua'] }}</span>
                                    <span class="fs-8 text-muted">Anggota {{ $row['anggota'] }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    @forelse($row['status_counts'] as $status)
                                        <span class="badge badge-light-{{ $status['color'] }}">{{ $status['label'] }}: {{ $status['total'] }}</span>
                                    @empty
                                        <span class="badge badge-light-success">Tidak ada assignment aktif</span>
                                    @endforelse
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-light-{{ $riskColor }}">{{ $riskLabel }}</span>
                            </td>
                            <td>
                                @if($latestAkreditasi)
                                    <div class="fw-bold text-gray-900 text-truncate">{{ $latestAkreditasi->user?->pesantren?->nama_pesantren ?? $latestAkreditasi->user?->name ?? 'Pesantren' }}</div>
                                    <div class="fs-8 text-muted text-truncate">{{ $latestAkreditasi->getStatusLabel() }} - {{ $latestAkreditasi->created_at?->format('d M Y') }}</div>
                                @else
                                    <span class="text-muted fs-7">Belum ada assignment aktif.</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <x-superadmin.action-menu label="Buka aksi Beban Kerja Asesor {{ $row['name'] }}">
                                    <div class="menu-item px-3">
                                        <a href="{{ route('superadmin.asesor-workload.show', ['asesor' => $row['id'], 'period' => $period, 'load' => $load]) }}" class="menu-link px-3 d-flex align-items-center gap-2">
                                            <i class="ki-outline ki-chart-line fs-4"></i>
                                            <span>Lihat Workload</span>
                                        </a>
                                    </div>
                                    @if($latestAkreditasi)
                                        <div class="menu-item px-3">
                                            <a href="{{ route('superadmin.akreditasi.show', $latestAkreditasi) }}" class="menu-link px-3 d-flex align-items-center gap-2">
                                                <i class="ki-outline ki-document fs-4"></i>
                                                <span>Detail Akreditasi</span>
                                            </a>
                                        </div>
                                    @else
                                        <div class="menu-item px-3">
                                            <a href="{{ route('superadmin.akreditasi.index', ['status' => \App\Models\Akreditasi::STATUS_ASSESSOR_ASSIGNMENT, 'period' => $period]) }}" class="menu-link px-3 d-flex align-items-center gap-2">
                                                <i class="ki-outline ki-user-tick fs-4"></i>
                                                <span>Buka Queue Assignment</span>
                                            </a>
                                        </div>
                                    @endif
                                </x-superadmin.action-menu>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="text-center py-12 text-muted border rounded bg-light">Tidak ada asesor sesuai filter ini.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection



