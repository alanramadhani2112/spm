@extends('layouts.metronic.app')

@section('title', 'Dashboard Akreditasi')
@section('pageTitle', 'Dashboard Akreditasi')

@section('toolbar')
<div class="d-flex align-items-center gap-2 gap-lg-3">
    <div class="d-flex align-items-center" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
        <button type="button" class="btn btn-sm btn-light-primary">
            <i class="ki-outline ki-filter fs-2"></i>Filter Status
        </button>
        <div class="menu menu-sub menu-sub-dropdown w-250px w-md-300px" data-kt-menu="true">
            <div class="px-7 py-5">
                <div class="fs-5 text-gray-900 fw-bold">Filter Opsi</div>
            </div>
            <div class="separator border-gray-200"></div>
            <div class="px-7 py-5">
                <div class="mb-3">
                    <label class="form-label fs-6 fw-semibold">Status</label>
                    <select class="form-select form-select-solid" data-kt-filter="status">
                        <option value="all">Semua Status</option>
                        <option value="active">Proses Aktif</option>
                        <option value="correction">Perlu Koreksi</option>
                        <option value="completed">Selesai</option>
                    </select>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-sm btn-light" data-kt-filter="reset">Reset</button>
                    <button type="button" class="btn btn-sm btn-primary ms-2" data-kt-filter="apply">Terapkan</button>
                </div>
            </div>
        </div>
    </div>
    <a href="{{ route('pesantren.akreditasi.pengajuan') }}" class="btn btn-sm btn-primary">
        <i class="ki-outline ki-plus-squared fs-2"></i>Pengajuan Baru
    </a>
</div>
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

    $correctionStatuses = Akreditasi::CORRECTION_STATUSES;
    $terminalStatuses = Akreditasi::TERMINAL_STATUSES;
    $activeCount = $akreditasis->whereNotIn('status', $terminalStatuses)->count();
    $correctionCount = $akreditasis->whereIn('status', $correctionStatuses)->count();
    $completedCount = $akreditasis->whereIn('status', $terminalStatuses)->count();
    $latestAkreditasi = $akreditasis->first();
@endphp

<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl-3 col-md-6">
        <x-metronic.stat-card value="{{ $akreditasis->count() }}" label="Total Pengajuan" icon="ki-document" color="primary" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-metronic.stat-card value="{{ $activeCount }}" label="Proses Aktif" icon="ki-timer" color="warning" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-metronic.stat-card value="{{ $correctionCount }}" label="Perlu Koreksi" icon="ki-pencil" color="danger" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-metronic.stat-card value="{{ $completedCount }}" label="Selesai" icon="ki-shield-tick" color="success" />
    </div>
</div>

<div class="row g-5 g-xl-8">
    <div class="col-xl-8">
        <div class="card card-flush">
            <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                <div class="card-title">
                    <div class="d-flex align-items-center position-relative my-1">
                        <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                        <input type="text" data-kt-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Cari akreditasi...">
                    </div>
                </div>
                <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                    <div class="w-150px">
                        <select class="form-select form-select-solid" data-kt-filter="status">
                            <option value="all">Semua Status</option>
                            <option value="active">Proses Aktif</option>
                            <option value="correction">Perlu Koreksi</option>
                            <option value="completed">Selesai</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body pt-0">
                @if($akreditasis->isEmpty())
                    <x-metronic.alert type="primary" message="Belum ada pengajuan akreditasi. Mulai dengan membuat pengajuan baru." />
                @else
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed table-row-gray-300 fs-6 gy-5">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th class="min-w-200px">Akreditasi</th>
                                    <th class="min-w-100px">Status</th>
                                    <th class="min-w-80px">Siklus</th>
                                    <th class="min-w-100px">Tanggal</th>
                                    <th class="text-end min-w-150px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold">
                                @foreach($akreditasis as $akreditasi)
                                    @php $color = $statusColors[$akreditasi->status] ?? 'secondary'; @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-900 fw-bold mb-1">{{ Str::limit($akreditasi->uuid, 16, '...') }}</span>
                                                <span class="text-muted fs-7">#{{ $akreditasi->id }}</span>
                                            </div>
                                        </td>
                                        <td><span class="badge badge-light-{{ $color }}">{{ $akreditasi->getStatusLabel() }}</span></td>
                                        <td>{{ $akreditasi->correction_cycle }}</td>
                                        <td>{{ $akreditasi->created_at->format('d M Y') }}</td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-2">
                                                @if($akreditasi->status === Akreditasi::STATUS_ASSESSMENT_OPEN)
                                                    <a href="{{ route('pesantren.akreditasi.assessment', $akreditasi->id) }}" class="btn btn-sm btn-light-primary flex-shrink-0">Isi Asesmen</a>
                                                @endif
                                                @if(in_array($akreditasi->status, $correctionStatuses, true))
                                                    <a href="{{ route('pesantren.akreditasi.koreksi', $akreditasi->id) }}" class="btn btn-sm btn-light-warning flex-shrink-0">Koreksi</a>
                                                @endif
                                                @if($akreditasi->kartu_kendali && $akreditasi->status === Akreditasi::STATUS_VISITASI_SCHEDULED)
                                                    <a href="{{ asset('storage/' . $akreditasi->kartu_kendali) }}" target="_blank" class="btn btn-sm btn-light-info flex-shrink-0">Kartu Kendali</a>
                                                @endif
                                                @if(in_array($akreditasi->status, $terminalStatuses, true) || $akreditasi->status === Akreditasi::STATUS_FINAL_REJECTED)
                                                    <a href="{{ route('pesantren.akreditasi.hasil', $akreditasi->id) }}" class="btn btn-sm btn-light-success flex-shrink-0">Hasil</a>
                                                @endif
                                                @if($akreditasi->status === Akreditasi::STATUS_FINAL_REJECTED)
                                                    <a href="{{ route('pesantren.akreditasi.koreksi', $akreditasi->id) }}" class="btn btn-sm btn-light-warning flex-shrink-0">Koreksi</a>
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
    </div>

    <div class="col-xl-4">
        <div class="card card-flush bg-light-primary h-100">
            <div class="card-header border-0 pt-8">
                <h3 class="card-title fw-bold text-gray-900">Status Terkini</h3>
            </div>
            <div class="card-body pt-3 pb-8">
                @if($latestAkreditasi)
                    <div class="d-flex align-items-center mb-6">
                        <span class="badge badge-light-{{ $statusColors[$latestAkreditasi->status] ?? 'secondary' }} me-3">{{ $latestAkreditasi->getStatusLabel() }}</span>
                    </div>
                    <div class="mb-4">
                        <div class="text-muted fs-7 mb-1">Tanggal Pengajuan</div>
                        <div class="fs-6 fw-bold text-gray-900">{{ $latestAkreditasi->created_at->format('d M Y, H:i') }}</div>
                    </div>
                    <div class="mb-4">
                        <div class="text-muted fs-7 mb-1">UUID</div>
                        <div class="fs-7 fw-semibold font-monospace text-gray-800">{{ $latestAkreditasi->uuid }}</div>
                    </div>
                    <div class="separator separator-dashed my-5"></div>
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-30px me-3">
                                <span class="symbol-label bg-light-success"><i class="ki-outline ki-verify fs-6 text-success"></i></span>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fw-semibold text-gray-900 fs-7">Pengajuan</span>
                                <span class="text-muted fs-8">{{ $latestAkreditasi->status >= Akreditasi::STATUS_INITIAL_SUBMITTED ? 'Terkirim' : 'Belum' }}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-30px me-3">
                                <span class="symbol-label {{ $latestAkreditasi->status >= Akreditasi::STATUS_ASSESSMENT_OPEN ? 'bg-light-success' : 'bg-light-secondary' }}">
                                    <i class="ki-outline ki-clipboard fs-6 {{ $latestAkreditasi->status >= Akreditasi::STATUS_ASSESSMENT_OPEN ? 'text-success' : 'text-muted' }}"></i>
                                </span>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fw-semibold text-gray-900 fs-7">Asesmen</span>
                                <span class="text-muted fs-8">{{ $latestAkreditasi->status >= Akreditasi::STATUS_ASSESSMENT_OPEN ? 'Terbuka' : 'Menunggu' }}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-30px me-3">
                                <span class="symbol-label {{ in_array($latestAkreditasi->status, $terminalStatuses) ? 'bg-light-success' : 'bg-light-secondary' }}">
                                    <i class="ki-outline ki-shield-tick fs-6 {{ in_array($latestAkreditasi->status, $terminalStatuses) ? 'text-success' : 'text-muted' }}"></i>
                                </span>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fw-semibold text-gray-900 fs-7">Hasil</span>
                                <span class="text-muted fs-8">{{ in_array($latestAkreditasi->status, $terminalStatuses) ? 'Tersedia' : 'Menunggu' }}</span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="ki-outline ki-document fs-5x text-primary mb-4 d-block"></i>
                        <span class="text-muted fw-semibold fs-6">Belum ada pengajuan</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
