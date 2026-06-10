@extends('layouts.metronic.app')

@section('title', 'Dashboard Ketua Asesor')
@section('pageTitle', 'Dashboard Ketua Asesor')

@section('toolbar')
<div class="d-flex align-items-center gap-2 gap-lg-3">
    <div class="d-flex align-items-center position-relative">
        <button type="button" class="btn btn-sm btn-light-primary">
            <i class="ki-outline ki-calendar fs-2"></i>Jadwal Visitasi
        </button>
    </div>
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

    $reviewCount = $akreditasis->whereIn('status', [Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW, Akreditasi::STATUS_ASSESSOR_STAGE_2_CORRECTION, Akreditasi::STATUS_ASSESSOR_STAGE_2_LIMIT_REVIEW])->count();
    $visitasiCount = $akreditasis->where('status', Akreditasi::STATUS_VISITASI_SCHEDULED)->count();
    $scoringCount = $akreditasis->whereIn('status', [Akreditasi::STATUS_POST_VISITASI_SCORING, Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW, Akreditasi::STATUS_ASSESSOR_STAGE_2_CORRECTION])->count();
    $laporanCount = $akreditasis->where('status', Akreditasi::STATUS_POST_VISITASI_SCORING)->count();
@endphp

<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $akreditasis->count() }}" label="Total Penugasan" icon="ki-profile-user" color="primary" /></div>
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $reviewCount }}" label="Review Tahap 2" icon="ki-notepad-edit" color="warning" /></div>
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $visitasiCount }}" label="Visitasi Terjadwal" icon="ki-calendar" color="info" /></div>
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $scoringCount }}" label="Perlu Penilaian" icon="ki-chart-simple" color="danger" /></div>
</div>

<div class="row g-5 g-xl-8">
    <div class="col-xl-9">
        <div class="card card-flush">
            <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                <div class="card-title">
                    <div class="d-flex align-items-center position-relative my-1">
                        <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                        <input type="text" data-kt-ketua-search="search" class="form-control form-control-solid w-250px ps-12" placeholder="Cari penugasan...">
                    </div>
                </div>
                <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                    <div class="w-150px">
                        <select class="form-select form-select-solid" data-kt-ketua-search="status">
                            <option value="all">Semua Status</option>
                            <option value="review">Perlu Review</option>
                            <option value="visitasi">Terjadwal Visitasi</option>
                            <option value="scoring">Perlu Penilaian</option>
                            <option value="laporan">Siap Submit</option>
                        </select>
                    </div>
                    <span class="badge badge-light-primary">{{ $laporanCount }} laporan siap submit</span>
                </div>
            </div>
            <div class="card-body pt-0">
                @if($akreditasis->isEmpty())
                    <x-metronic.alert type="primary" message="Anda belum ditugaskan sebagai Ketua Asesor untuk akreditasi manapun." />
                @else
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed table-row-gray-300 fs-6 gy-5">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th class="min-w-200px">Akreditasi</th>
                                    <th class="min-w-100px">Status</th>
                                    <th class="min-w-150px">Visitasi</th>
                                    <th class="min-w-120px">Nilai</th>
                                    <th class="text-end min-w-200px">Aksi</th>
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
                                        <td><span class="badge badge-light-{{ $color }}">{{ $akreditasi->getStatusLabel() }}</span></td>
                                        <td>
                                            @if($akreditasi->tanggal_visitasi)
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-900 fw-bold mb-1">{{ \Carbon\Carbon::parse($akreditasi->tanggal_visitasi)->format('d M Y') }}</span>
                                                    <span class="text-muted fs-8">Lokasi: {{ $akreditasi->lokasi_visitasi ?? 'Belum ditentukan' }}</span>
                                                </div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column gap-1">
                                                @if($akreditasi->na1_score !== null)
                                                    <span class="badge badge-light-info">NA1: {{ $akreditasi->na1_score }}</span>
                                                @endif
                                                @if($akreditasi->na2_score !== null)
                                                    <span class="badge badge-light-info">NA2: {{ $akreditasi->na2_score }}</span>
                                                @endif
                                                @if($akreditasi->nk_score !== null)
                                                    <span class="badge badge-light-{{ $akreditasi->is_nk_final ? 'success' : 'warning' }}">NK: {{ $akreditasi->nk_score }}</span>
                                                @endif
                                                @if($akreditasi->na1_score === null && $akreditasi->nk_score === null)
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-2 flex-wrap">
                                                @if(in_array($akreditasi->status, [Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW, Akreditasi::STATUS_ASSESSOR_STAGE_2_CORRECTION, Akreditasi::STATUS_ASSESSOR_STAGE_2_LIMIT_REVIEW]))
                                                    <a href="{{ route('asesor.ketua.review-tahap2', $akreditasi->id) }}" class="btn btn-sm btn-light-warning flex-shrink-0">Review</a>
                                                    <a href="{{ route('asesor.ketua.jadwalkan-visitasi', $akreditasi->id) }}" class="btn btn-sm btn-light-info flex-shrink-0">Jadwalkan</a>
                                                @endif
                                                @if($akreditasi->status === Akreditasi::STATUS_VISITASI_SCHEDULED)
                                                    <a href="{{ route('asesor.ketua.jadwalkan-visitasi', $akreditasi->id) }}" class="btn btn-sm btn-light-info flex-shrink-0">Edit Jadwal</a>
                                                @endif
                                                @if(in_array($akreditasi->status, [Akreditasi::STATUS_VISITASI_COMPLETED, Akreditasi::STATUS_POST_VISITASI_SCORING]))
                                                    <a href="{{ route('asesor.ketua.input-na1', $akreditasi->id) }}" class="btn btn-sm btn-light-primary flex-shrink-0">Input NA1</a>
                                                    <a href="{{ route('asesor.ketua.input-nk', $akreditasi->id) }}" class="btn btn-sm btn-light-warning flex-shrink-0">Input NK</a>
                                                @endif
                                                @if(in_array($akreditasi->status, [Akreditasi::STATUS_VISITASI_COMPLETED, Akreditasi::STATUS_POST_VISITASI_SCORING]))
                                                    <a href="{{ route('asesor.ketua.upload-laporan', $akreditasi->id) }}" class="btn btn-sm btn-light-success flex-shrink-0">Upload Laporan</a>
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

    <div class="col-xl-3">
        <div class="card card-flush bg-light-primary h-100">
            <div class="card-header border-0 pt-8">
                <h3 class="card-title fw-bold text-gray-900">Fokus Ketua</h3>
            </div>
            <div class="card-body pt-2 pb-8">
                <div class="d-flex flex-column gap-6">
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-35px me-3">
                            <span class="symbol-label bg-warning"><i class="ki-outline ki-notepad-edit fs-5 text-warning"></i></span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-gray-900 fs-6">{{ $reviewCount }}</span>
                            <span class="text-muted fs-8">Review Tahap 2</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-35px me-3">
                            <span class="symbol-label bg-info"><i class="ki-outline ki-calendar fs-5 text-info"></i></span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-gray-900 fs-6">{{ $visitasiCount }}</span>
                            <span class="text-muted fs-8">Visitasi Terjadwal</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-35px me-3">
                            <span class="symbol-label bg-danger"><i class="ki-outline ki-chart-simple fs-5 text-danger"></i></span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-gray-900 fs-6">{{ $scoringCount }}</span>
                            <span class="text-muted fs-8">Perlu Penilaian</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
