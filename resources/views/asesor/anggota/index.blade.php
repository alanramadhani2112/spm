@extends('layouts.metronic.app')

@section('title', 'Dashboard Anggota Asesor')
@section('pageTitle', 'Dashboard Anggota Asesor')

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

    $visitasiCount = $akreditasis->where('status', Akreditasi::STATUS_VISITASI_SCHEDULED)->count();
    $scoringCount = $akreditasis->where('status', Akreditasi::STATUS_POST_VISITASI_SCORING)->count();
    $laporanCount = $akreditasis->whereIn('status', [Akreditasi::STATUS_POST_VISITASI_SCORING, Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED])->count();
    $finalNa2Count = $akreditasis->where('is_na2_final', true)->count();
@endphp

<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $akreditasis->count() }}" label="Total Penugasan" icon="ki-profile-user" color="primary" /></div>
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $visitasiCount }}" label="Visitasi" icon="ki-calendar" color="info" /></div>
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $scoringCount }}" label="Input NA2" icon="ki-chart-simple" color="danger" /></div>
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $finalNa2Count }}" label="NA2 Final" icon="ki-shield-tick" color="success" /></div>
</div>

<div class="card card-flush">
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                <input type="text" data-kt-anggota-search="search" class="form-control form-control-solid w-250px ps-12" placeholder="Cari penugasan...">
            </div>
        </div>
        <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
            <div class="w-150px">
                <select class="form-select form-select-solid" data-kt-anggota-search="status">
                    <option value="all">Semua Status</option>
                    <option value="visitasi">Visitasi Terjadwal</option>
                    <option value="scoring">Perlu NA2</option>
                    <option value="final">NA2 Final</option>
                </select>
            </div>
            <span class="badge badge-light-primary">{{ $laporanCount }} laporan aktif</span>
        </div>
    </div>
    <div class="card-body pt-0">
        @if($akreditasis->isEmpty())
            <x-metronic.alert type="primary" message="Anda belum ditugaskan sebagai Anggota Asesor untuk akreditasi manapun." />
        @else
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed table-row-gray-300 fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-200px">Akreditasi</th>
                            <th class="min-w-100px">Status</th>
                            <th class="min-w-150px">Visitasi</th>
                            <th class="min-w-100px">NA2</th>
                            <th class="text-end min-w-150px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        @foreach($akreditasis as $akreditasi)
                            @php $color = $statusColors[$akreditasi->status] ?? 'secondary'; @endphp
                            <tr>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-900 fw-bold mb-1">{{ Str::limit($akreditasi->uuid, 12, '...') }}</span>
                                        <span class="text-muted fs-7">{{ $akreditasi->user?->pesantren?->nama_pesantren ?? '—' }}</span>
                                    </div>
                                </td>
                                <td><span class="badge badge-light-{{ $color }}">{{ Akreditasi::STATUS_LABELS[$akreditasi->status] ?? $akreditasi->status }}</span></td>
                                <td>
                                    @if($akreditasi->tgl_visitasi)
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-900 fw-bold mb-1">{{ $akreditasi->tgl_visitasi->format('d M Y') }}</span>
                                            @if($akreditasi->tgl_visitasi_akhir)
                                                <span class="text-muted fs-8">s/d {{ $akreditasi->tgl_visitasi_akhir->format('d M Y') }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($akreditasi->na2 !== null)
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-bold text-gray-900">{{ number_format($akreditasi->na2, 2) }}</span>
                                            @if($akreditasi->is_na2_final)
                                                <span class="badge badge-light-success">Final</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        @if(in_array($akreditasi->status, [Akreditasi::STATUS_VISITASI_COMPLETED, Akreditasi::STATUS_POST_VISITASI_SCORING, Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED]))
                                            <a href="{{ route('asesor.anggota.input-na2', $akreditasi->id) }}" class="btn btn-sm btn-light-primary flex-shrink-0">Input NA2</a>
                                        @endif
                                        @if(in_array($akreditasi->status, [Akreditasi::STATUS_VISITASI_COMPLETED, Akreditasi::STATUS_POST_VISITASI_SCORING]))
                                            <a href="{{ route('asesor.anggota.upload-laporan-individu', $akreditasi->id) }}" class="btn btn-sm btn-light-success flex-shrink-0">Upload Laporan</a>
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
