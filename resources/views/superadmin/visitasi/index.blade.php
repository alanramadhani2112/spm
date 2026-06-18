@extends('layouts.metronic.app')

@section('title', 'Superadmin — Overview Visitasi')
@section('pageTitle', 'Overview Visitasi')

@section('toolbar')
<div class="d-flex flex-wrap align-items-center gap-2 gap-lg-3">
    <a href="{{ route('superadmin.dashboard', ['period' => $period]) }}" class="btn btn-sm btn-light">
        <i class="ki-outline ki-chart-pie-4 fs-2"></i>Dashboard
    </a>
    <a href="{{ route('superadmin.akreditasi.index', ['period' => $period]) }}" class="btn btn-sm btn-light-primary">
        <i class="ki-outline ki-document fs-2"></i>Workflow Console
    </a>
</div>
@endsection

@section('content')
@php
    use App\Models\Akreditasi;
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-start gap-4 mb-8">
    <div>
        <h2 class="fs-2 fw-bold text-gray-900 mb-2">Operational Board Visitasi</h2>
        <p class="fs-7 text-muted mb-0">Pantau kesiapan jadwal visitasi, item yang lewat jadwal, scoring pasca visitasi, dan pengajuan yang siap divalidasi akhir dari satu halaman operasional.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('superadmin.akreditasi.index', ['status' => Akreditasi::STATUS_VISITASI_SCHEDULED, 'period' => $period]) }}" class="btn btn-sm btn-light">
            <i class="ki-outline ki-calendar-tick fs-3"></i>Console Visitasi
        </a>
        <a href="{{ route('superadmin.audit.index') }}" class="btn btn-sm btn-light">
            <i class="ki-outline ki-time fs-3"></i>Audit Log
        </a>
    </div>
</div>

<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl col-md-4"><x-metronic.stat-card value="{{ $summary['ready'] }}" label="Siap Dijadwalkan" icon="ki-calendar-add" color="warning" /></div>
    <div class="col-xl col-md-4"><x-metronic.stat-card value="{{ $summary['scheduled'] }}" label="Terjadwal" icon="ki-calendar-tick" color="info" /></div>
    <div class="col-xl col-md-4"><x-metronic.stat-card value="{{ $summary['ongoing'] }}" label="Sedang Berjalan" icon="ki-calendar-2" color="primary" /></div>
    <div class="col-xl col-md-4"><x-metronic.stat-card value="{{ $summary['past_due'] }}" label="Lewat Jadwal" icon="ki-warning" color="danger" /></div>
    <div class="col-xl col-md-4"><x-metronic.stat-card value="{{ $summary['scoring'] }}" label="Menunggu Scoring" icon="ki-chart-line" color="danger" /></div>
    <div class="col-xl col-md-4"><x-metronic.stat-card value="{{ $summary['validation'] }}" label="Siap Validasi" icon="ki-shield-tick" color="success" /></div>
</div>

<div class="card card-flush bg-light-primary border border-primary border-dashed mb-8">
    <div class="card-body p-7">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-5">
            <div class="d-flex align-items-start gap-4">
                <div class="symbol symbol-45px">
                    <span class="symbol-label bg-primary"><i class="ki-outline ki-compass fs-2 text-white"></i></span>
                </div>
                <div>
                    <h3 class="fw-bold text-gray-900 mb-1">Apa yang perlu dipantau di pipeline visitasi?</h3>
                    <div class="fs-7 text-muted">Gunakan filter status dan window jadwal untuk memisahkan item yang belum dijadwalkan, sedang berjalan, lewat jadwal, atau sudah masuk scoring/validasi akhir.</div>
                </div>
            </div>
            <span class="badge badge-light-primary">{{ $displayedCount }} item ditampilkan</span>
        </div>
    </div>
</div>

<x-metronic.card title="Filter & Daftar Visitasi" flush>
    <x-slot:header>
        <div class="d-flex flex-wrap align-items-center gap-2">
            @if(($period ?? 'all') !== 'all')
                <span class="badge badge-light-info">Periode: {{ $period }}</span>
            @endif
            @if(($status ?? 'all') !== 'all')
                <span class="badge badge-light-warning">Status: {{ $statusOptions[$status] ?? $status }}</span>
            @endif
            @if(($schedule ?? 'all') !== 'all')
                <span class="badge badge-light-primary">Window: {{ $scheduleOptions[$schedule] ?? $schedule }}</span>
            @endif
            @if(($search ?? '') !== '')
                <span class="badge badge-light-success">Cari: {{ $search }}</span>
            @endif
        </div>
    </x-slot:header>

    <form method="GET" action="{{ route('superadmin.visitasi.index') }}" class="row g-3 align-items-end mb-8">
        <div class="col-lg-2 col-md-6">
            <label for="filter_visitasi_period" class="form-label">Periode</label>
            <select id="filter_visitasi_period" name="period" class="form-select form-select-solid">
                @foreach($periodOptions as $value => $label)
                    <option value="{{ $value }}" @selected($period === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <div class="fs-8 text-muted mt-1">Tahun pengajuan akreditasi.</div>
        </div>
        <div class="col-lg-3 col-md-6">
            <label for="filter_visitasi_status" class="form-label">Status Visitasi</label>
            <select id="filter_visitasi_status" name="status" class="form-select form-select-solid">
                <option value="all" @selected($status === 'all')>Semua Status</option>
                @foreach($statusOptions as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <div class="fs-8 text-muted mt-1">Fokus pada tahapan visitasi tertentu.</div>
        </div>
        <div class="col-lg-3 col-md-6">
            <label for="filter_visitasi_schedule" class="form-label">Window Jadwal</label>
            <select id="filter_visitasi_schedule" name="schedule" class="form-select form-select-solid">
                @foreach($scheduleOptions as $value => $label)
                    <option value="{{ $value }}" @selected($schedule === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <div class="fs-8 text-muted mt-1">Pisahkan upcoming, ongoing, dan lewat jadwal.</div>
        </div>
        <div class="col-lg-3 col-md-8">
            <label for="filter_visitasi_search" class="form-label">Cari Visitasi</label>
            <input id="filter_visitasi_search" type="search" name="q" value="{{ $search }}" class="form-control form-control-solid" placeholder="UUID, pesantren, NSP, email...">
            <div class="fs-8 text-muted mt-1">Cari pesantren, UUID, NSP, atau email.</div>
        </div>
        <div class="col-lg-1 col-md-4 d-flex gap-2">
            <button class="btn btn-primary flex-grow-1">Terapkan</button>
            <a href="{{ route('superadmin.visitasi.index') }}" class="btn btn-light" aria-label="Reset filter visitasi">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-row-bordered align-middle gs-0 gy-4">
            <thead>
                <tr class="fw-bold text-muted bg-light">
                    <th class="ps-4 min-w-250px">Pesantren</th>
                    <th class="min-w-220px">Status & Langkah Berikutnya</th>
                    <th class="min-w-180px">Jadwal Visitasi</th>
                    <th class="min-w-190px">Tim Asesor</th>
                    <th class="min-w-220px">Catatan</th>
                    <th class="min-w-220px">Progress Pasca Visitasi</th>
                    <th class="text-end min-w-90px pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($visitasiRows as $row)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-gray-900">{{ $row['pesantren_name'] }}</div>
                            <div class="text-muted fs-8">{{ $row['pesantren_email'] ?? '—' }} @if($row['pesantren_nsp']) · {{ $row['pesantren_nsp'] }} @endif</div>
                            <div class="text-muted fs-8">{{ \Illuminate\Support\Str::limit($row['akreditasi']->uuid, 24) }}</div>
                        </td>
                        <td>
                            <div class="d-flex flex-column gap-1">
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <span class="badge badge-light-{{ $row['status_color'] }}">{{ $row['status_label'] }}</span>
                                    <span class="badge badge-light-{{ $row['schedule_state']['color'] }}">{{ $row['schedule_state']['label'] }}</span>
                                </div>
                                <span class="fs-8 text-gray-700">{{ $row['next_step'] }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold text-gray-900">{{ $row['schedule_range'] }}</div>
                            <div class="fs-8 text-muted">{{ $row['akreditasi']->status_changed_at?->diffForHumans() ?? $row['akreditasi']->created_at?->diffForHumans() }}</div>
                        </td>
                        <td>
                            <div class="fs-8 mb-1"><span class="badge badge-light-info me-1">KETUA</span>{{ $row['team']['ketua'] ?? 'Belum ditetapkan' }}</div>
                            <div class="fs-8 text-muted">Anggota: {{ $row['team']['anggota']->implode(', ') ?: 'Belum ditetapkan' }}</div>
                        </td>
                        <td>
                            <div class="fs-8 text-gray-700">{{ $row['catatan_preview'] }}</div>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($row['progress'] as $progress)
                                    <span class="badge badge-light-{{ $progress['done'] ? 'success' : 'secondary' }}">{{ $progress['label'] }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="text-end pe-4">
                            @php
                                $primaryAction = $row['actions'][0] ?? null;
                                $secondaryActions = array_slice($row['actions'], 1);
                            @endphp
                            <div class="d-flex flex-wrap justify-content-end gap-2">
                                @if($primaryAction)
                                    <a href="{{ $primaryAction['route'] }}"
                                       class="btn btn-sm btn-{{ $primaryAction['color'] === 'warning' ? 'warning' : ($primaryAction['color'] === 'danger' ? 'danger' : ($primaryAction['color'] === 'success' ? 'success' : 'primary')) }}"
                                       data-swal-confirm="true"
                                       data-swal-title="Buka aksi {{ $primaryAction['label'] }}?"
                                       data-swal-text="Anda akan masuk ke halaman {{ $primaryAction['label'] }} untuk pengajuan {{ $row['akreditasi']->uuid }}."
                                       data-swal-icon="question"
                                       data-swal-confirm-button="Ya, buka">{{ $primaryAction['label'] }}</a>
                                @else
                                    <span class="badge badge-light-secondary">Tidak ada aksi</span>
                                @endif
                                <a href="{{ route('superadmin.akreditasi.show', $row['akreditasi']) }}" class="btn btn-sm btn-light">Detail</a>
                                @if(! empty($secondaryActions))
                                    <x-superadmin.action-menu label="Buka aksi tambahan visitasi {{ $row['akreditasi']->uuid }}">
                                        @foreach($secondaryActions as $action)
                                            <div class="menu-item px-3">
                                                <a href="{{ $action['route'] }}"
                                                   class="menu-link px-3 d-flex align-items-center gap-2 text-{{ $action['color'] }}"
                                                   data-swal-confirm="true"
                                                   data-swal-title="Buka aksi {{ $action['label'] }}?"
                                                   data-swal-text="Anda akan masuk ke halaman {{ $action['label'] }} untuk pengajuan {{ $row['akreditasi']->uuid }}."
                                                   data-swal-icon="question"
                                                   data-swal-confirm-button="Ya, buka">
                                                    <i class="ki-outline ki-right-square fs-4"></i>
                                                    <span>{{ $action['label'] }}</span>
                                                </a>
                                            </div>
                                        @endforeach
                                    </x-superadmin.action-menu>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="text-center py-12 text-muted border rounded bg-light">
                                Belum ada item visitasi yang cocok dengan filter ini. Coba reset filter atau kembali ke dashboard untuk melihat antrian lain.
                                <div class="mt-4">
                                    <a href="{{ route('superadmin.visitasi.index') }}" class="btn btn-sm btn-light">Reset Filter</a>
                                    <a href="{{ route('superadmin.dashboard', ['period' => $period]) }}" class="btn btn-sm btn-primary">Kembali ke Dashboard</a>
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
