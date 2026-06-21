@extends('layouts.metronic.app')

@section('title', 'Detail Beban Kerja Asesor')
@section('pageTitle', 'Detail Beban Kerja Asesor')

@section('toolbar')
@php
    $backQuery = ['period' => $period];
    if (($load ?? 'all') !== 'all') {
        $backQuery['load'] = $load;
    }
@endphp
<div class="d-flex flex-wrap align-items-center gap-2 gap-lg-3">
    <a href="{{ route('superadmin.asesor-workload.index', $backQuery) }}" class="btn btn-sm btn-light">
        <i class="ki-outline ki-left fs-4"></i>Kembali
    </a>
    <form method="GET" action="{{ route('superadmin.asesor-workload.show', $asesor) }}" class="d-flex flex-wrap align-items-center gap-2">
        <label for="detail_workload_period" class="visually-hidden">Periode Beban Kerja Asesor</label>
        <select id="detail_workload_period" name="period" class="form-select form-select-solid form-select-sm w-auto" onchange="this.form.submit()">
            @foreach($periodOptions as $value => $label)
                <option value="{{ $value }}" @selected($period === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @if(($load ?? 'all') !== 'all')
            <input type="hidden" name="load" value="{{ $load }}">
        @endif
    </form>
</div>
@endsection

@section('content')
@php
    $initials = collect(explode(' ', $asesor->name))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') ?: 'A';
    $periodLabel = $periodOptions[$period] ?? 'Semua Periode';
    $latestAkreditasi = $workload['latest_assignment']?->akreditasi;
@endphp

<div class="card card-flush bg-light-primary border border-primary border-dashed mb-8">
    <div class="card-body p-7">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-5">
            <div class="d-flex align-items-center gap-4">
                <span class="symbol symbol-60px flex-shrink-0">
                    <span class="symbol-label bg-light-{{ $workload['color'] }} text-{{ $workload['color'] }} fw-bold fs-2">{{ $initials }}</span>
                </span>
                <div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <h2 class="fw-bold text-gray-900 mb-0">{{ $asesor->name }}</h2>
                        <span class="badge badge-light-{{ $workload['color'] }}">{{ $workload['capacity_note'] }}</span>
                        <span class="badge badge-light-secondary">Periode {{ $periodLabel }}</span>
                        @if($heroStats['overdue'] > 0)
                            <span class="badge badge-light-danger">Overdue {{ $heroStats['overdue'] }}</span>
                        @endif
                    </div>
                    <div class="fs-7 text-muted">{{ $asesor->email ?? 'Email belum tersedia' }}</div>
                    <div class="fs-8 text-muted">Assignment aktif {{ $heroStats['total'] }} • Ketua {{ $heroStats['ketua'] }} • Anggota {{ $heroStats['anggota'] }}</div>
                    <div class="fs-8 mt-2 {{ $heroStats['overdue'] > 0 ? 'text-danger fw-semibold' : 'text-gray-700' }}">{{ $heroStats['overdue'] > 0 ? 'Perlu redistribusi atau pemantauan cepat pada assignment overdue.' : 'Kapasitas masih aman untuk pemantauan rutin.' }}</div>
                </div>
            </div>
            <div class="text-end">
                <div class="fs-8 text-muted mb-1">Assignment terbaru</div>
                @if($latestAkreditasi)
                    <div class="fw-bold text-gray-900">{{ $latestAkreditasi->user?->pesantren?->nama_pesantren ?? $latestAkreditasi->user?->name ?? 'Pesantren' }}</div>
                    <div class="fs-8 text-muted">{{ $latestAkreditasi->getStatusLabel() }} • {{ $latestAkreditasi->created_at?->format('d M Y') }}</div>
                @else
                    <div class="fw-bold text-gray-900">Belum ada assignment aktif</div>
                    <div class="fs-8 text-muted">Riwayat tetap bisa dipantau dari audit assignment.</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="alert bg-light-info border border-info border-dashed d-flex align-items-start gap-4 p-6 mb-8">
    <i class="ki-outline ki-information-4 fs-2hx text-info"></i>
    <div>
        <h3 class="fw-bold text-gray-900 mb-1">Ringkasan workload mengikuti periode aktif</h3>
        <div class="fs-7 text-muted">Angka assignment aktif, ketua/anggota, dan overdue mengikuti filter periode <span class="fw-semibold">{{ $periodLabel }}</span>, sedangkan riwayat assignment menampilkan log terbaru yang relevan untuk asesor ini lintas periode.</div>
    </div>
</div>

<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $heroStats['total'] }}" label="Assignment Aktif" icon="ki-briefcase" color="info" /></div>
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $heroStats['ketua'] }}" label="Ketua" icon="ki-profile-user" color="primary" /></div>
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $heroStats['anggota'] }}" label="Anggota" icon="ki-people" color="success" /></div>
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $heroStats['overdue'] }}" label="Overdue" icon="ki-calendar-remove" color="danger" /></div>
</div>

<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl-4">
        <x-metronic.card title="Ringkasan Workload Aktif" class="mb-8">
            <div class="rounded bg-light-{{ $workload['color'] }} p-4 mb-5">
                <div class="fw-bold text-gray-900 mb-1">{{ $workload['capacity_note'] }}</div>
                <div class="fs-7 text-gray-700">
                    @if($workload['level'] === 'high')
                        Beban asesor saat ini berada pada level tinggi. Prioritaskan redistribusi atau hindari assignment baru tanpa konfirmasi overload.
                    @elseif($workload['level'] === 'medium')
                        Beban asesor perlu dipantau sebelum menambah assignment baru agar distribusi tetap seimbang.
                    @else
                        Asesor masih berada pada rentang aman untuk assignment baru berdasarkan workload aktif periode ini.
                    @endif
                </div>
            </div>

            <div class="mb-5">
                <div class="fw-bold text-gray-900 mb-3">Status Aktif</div>
                <div class="d-flex flex-wrap gap-2">
                    @forelse($workload['status_counts'] as $status)
                        <span class="badge badge-light-{{ $status['color'] }}">{{ $status['label'] }}: {{ $status['total'] }}</span>
                    @empty
                        <span class="badge badge-light-success">Tidak ada assignment aktif</span>
                    @endforelse
                </div>
            </div>

            <div class="d-grid gap-3 fs-7">
                <div class="d-flex justify-content-between gap-4">
                    <span class="text-muted">Load saat ini</span>
                    <span class="fw-semibold text-gray-900">{{ $workload['capacity_note'] }}</span>
                </div>
                <div class="d-flex justify-content-between gap-4">
                    <span class="text-muted">Asesor ketua</span>
                    <span class="fw-semibold text-gray-900">{{ $heroStats['ketua'] }}</span>
                </div>
                <div class="d-flex justify-content-between gap-4">
                    <span class="text-muted">Asesor anggota</span>
                    <span class="fw-semibold text-gray-900">{{ $heroStats['anggota'] }}</span>
                </div>
                <div class="d-flex justify-content-between gap-4">
                    <span class="text-muted">Assignment overdue</span>
                    <span class="fw-semibold {{ $heroStats['overdue'] > 0 ? 'text-danger' : 'text-gray-900' }}">{{ $heroStats['overdue'] }}</span>
                </div>
            </div>
        </x-metronic.card>
    </div>

    <div class="col-xl-8">
        <x-metronic.card title="Assignment Aktif" class="mb-8">
            @if($activeAssignments->isEmpty())
                <div class="text-center py-12 text-muted border rounded bg-light">Belum ada assignment aktif pada periode ini.</div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed table-row-gray-300 fs-6 gy-4 mb-0">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-220px">Pesantren</th>
                                <th class="min-w-180px">Status</th>
                                <th class="min-w-130px">Peran</th>
                                <th class="min-w-130px">Deadline</th>
                                <th class="text-end min-w-120px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-700">
                            @foreach($activeAssignments as $assignment)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-gray-900">{{ $assignment['pesantren_name'] }}</div>
                                        <div class="fs-8 text-muted">{{ $assignment['akreditasi']?->uuid ?? 'UUID belum tersedia' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge badge-light-{{ $assignment['status_color'] }}">{{ $assignment['status_label'] }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-light-primary">{{ $assignment['role_label'] }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold {{ $assignment['is_overdue'] ? 'text-danger' : 'text-gray-900' }}">{{ $assignment['deadline_label'] }}</div>
                                        <div class="fs-8 text-muted">{{ $assignment['is_overdue'] ? 'Perlu tindak lanjut' : 'Masih aktif' }}</div>
                                    </td>
                                    <td class="text-end">
                                        @if($assignment['detail_url'])
                                            <a href="{{ $assignment['detail_url'] }}" class="btn btn-sm btn-light-primary">Detail Akreditasi</a>
                                        @else
                                            <span class="text-muted fs-8">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-metronic.card>
    </div>
</div>

<x-metronic.card title="Riwayat Assignment & Reassignment">
    @if($assignmentHistory->isEmpty())
        <div class="text-center py-12 text-muted border rounded bg-light">Belum ada riwayat assignment yang relevan untuk asesor ini.</div>
    @else
        <div class="d-grid gap-4">
            @foreach($assignmentHistory as $history)
                <div class="rounded border border-gray-200 p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="badge badge-light-{{ $history['label'] === 'Reassignment' ? 'warning' : 'primary' }}">{{ $history['label'] }}</span>
                            <span class="badge badge-light-info">Role Asesor: {{ $history['assessor_role_label'] }}</span>
                            <span class="badge badge-light-secondary">Actor: {{ $history['actor_name'] }}</span>
                        </div>
                        <div class="fs-8 text-muted">{{ $history['timestamp']?->format('d M Y H:i') ?? '—' }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="fw-bold text-gray-900">{{ $history['pesantren_name'] }}</div>
                        <div class="fs-8 text-muted d-flex flex-wrap gap-2 align-items-center">
                            <span>{{ $history['akreditasi']?->uuid ?? 'UUID belum tersedia' }}</span>
                            @if($history['detail_url'])
                                <a href="{{ $history['detail_url'] }}" class="link-primary">Detail Akreditasi</a>
                            @endif
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="rounded bg-light-primary p-3 h-100">
                                <div class="fw-bold text-gray-900 mb-2">Tim Saat Ini</div>
                                <div class="fs-7 text-gray-700 mb-2">Ketua: <span class="fw-semibold">{{ $history['current_team']['ketua']?->name ?? '—' }}</span></div>
                                <div class="fs-7 text-gray-700">Anggota:
                                    <span class="fw-semibold">{{ $history['current_team']['anggota']->pluck('name')->implode(', ') ?: '—' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="rounded bg-light-warning p-3 h-100">
                                <div class="fw-bold text-gray-900 mb-2">Tim Sebelumnya</div>
                                @if($history['previous_team']->isEmpty())
                                    <div class="fs-7 text-muted">Tidak ada data tim sebelumnya pada log ini.</div>
                                @else
                                    <div class="d-flex flex-column gap-2">
                                        @foreach($history['previous_team'] as $previous)
                                            <div class="fs-7 text-gray-700">
                                                <span class="fw-semibold">{{ $previous['name'] }}</span>
                                                <span class="text-muted">• {{ $previous['role_label'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($history['reason'])
                        <div class="rounded bg-light mt-3 p-3 fs-7 text-gray-700">
                            <div class="fw-bold text-gray-900 mb-1">Alasan Assignment</div>
                            <div>{{ $history['reason'] }}</div>
                        </div>
                    @endif

                    @if($history['overload_warnings']->isNotEmpty())
                        <div class="rounded bg-light-danger mt-3 p-3 fs-7 text-danger">
                            Overload dikonfirmasi untuk {{ $history['overload_warnings']->pluck('name')->implode(', ') }}.
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</x-metronic.card>
@endsection

