@extends('layouts.metronic.app')

@section('title', 'Superadmin — Pengajuan Baru')
@section('pageTitle', 'Pengajuan Akreditasi Baru')

@section('toolbar')
<div class="d-flex flex-wrap align-items-center gap-2 gap-lg-3">
    <a href="{{ route('superadmin.akreditasi.index') }}" class="btn btn-sm btn-light">
        <i class="ki-outline ki-arrow-left fs-2"></i>Kembali ke Console
    </a>
</div>
@endsection

@section('content')
@php
    use App\Models\Akreditasi;
    $totalEligible = count($eligible);
    $totalPending  = count($pendingPrerequisites);
    $totalBlocked  = count($hasActiveAkreditasi);
    $totalAll      = $totalEligible + $totalPending + $totalBlocked;
@endphp

{{-- Header & Context --}}
<div class="d-flex flex-wrap justify-content-between align-items-start gap-4 mb-8">
    <div class="mw-lg-650px">
        <h2 class="fs-2 fw-bold text-gray-900 mb-2">Pengajuan Akreditasi Baru</h2>
        <p class="fs-7 text-muted mb-3">Pilih pesantren yang datanya sudah lengkap untuk diajukan akreditasi. Proses ini akan membuat antrian akreditasi baru dan mengunci profil pesantren.</p>
        <div class="d-flex flex-wrap gap-2">
            <span class="badge badge-light-primary">{{ $totalAll }} pesantren terdaftar</span>
            @if($totalEligible)
                <span class="badge badge-light-success">{{ $totalEligible }} siap diajukan</span>
            @endif
            @if($totalPending)
                <span class="badge badge-light-warning">{{ $totalPending }} data belum lengkap</span>
            @endif
            @if($totalBlocked)
                <span class="badge badge-light-danger">{{ $totalBlocked }} pengajuan aktif</span>
            @endif
        </div>
    </div>
</div>

{{-- Prasyarat Callout --}}
<div class="card card-flush bg-light-info border border-info border-dashed mb-8">
    <div class="card-body p-7">
        <div class="d-flex flex-wrap align-items-start gap-5">
            <div class="symbol symbol-45px">
                <span class="symbol-label bg-info"><i class="ki-outline ki-information-2 fs-2 text-white"></i></span>
            </div>
            <div>
                <h3 class="fw-bold text-gray-900 mb-2">Prasyarat Pengajuan</h3>
                <p class="fs-7 text-muted mb-3">Sebelum mengajukan akreditasi, pastikan data pesantren sudah lengkap:</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex flex-column gap-1">
                            <span class="fw-semibold fs-7 text-gray-700">Profil Pesantren (minimum)</span>
                            <ul class="list-unstyled fs-8 text-muted ms-0 mb-0">
                                @foreach($prerequisiteFields as $field)
                                    <li class="d-flex align-items-center gap-1">
                                        <i class="ki-outline ki-double-check fs-7 text-info"></i>{{ $field }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex flex-column gap-1">
                            <span class="fw-semibold fs-7 text-gray-700">Modul Data Lengkap</span>
                            <ul class="list-unstyled fs-8 text-muted ms-0 mb-0">
                                @foreach($prerequisiteModules as $key => $label)
                                    <li class="d-flex align-items-center gap-1">
                                        <i class="ki-outline ki-double-check fs-7 text-info"></i>{{ $label }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="mt-3 fs-8 text-muted">
                    <i class="ki-outline ki-warning-2 fs-7 text-warning me-1"></i>
                    Setelah pengajuan berhasil, status menjadi <strong>Review pengajuan awal</strong> dan profil pesantren akan dikunci dari perubahan.
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Empty State: No Pesantren --}}
@if($totalAll === 0)
    <x-metronic.card>
        <div class="text-center py-12">
            <div class="symbol symbol-60px mb-5">
                <span class="symbol-label bg-light"><i class="ki-outline ki-building-4 fs-1 text-muted"></i></span>
            </div>
            <h3 class="fw-bold text-gray-900 mb-2">Belum ada pesantren terdaftar</h3>
            <p class="fs-7 text-muted mb-6">Tambahkan pesantren melalui menu Master Data sebelum membuat pengajuan akreditasi.</p>
            <a href="{{ route('superadmin.master-data.users.index') }}" class="btn btn-sm btn-primary">
                <i class="ki-outline ki-profile-circle fs-2"></i>Kelola Pengguna
            </a>
        </div>
    </x-metronic.card>
@else
    {{-- Eligible Pesantren — can submit --}}
    @if(count($eligible))
        <div class="card card-flush mb-8">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title">
                    <span class="bullet bullet-vertical bg-success h-25px me-3"></span>
                    <span class="fw-bold fs-4 text-gray-900">Siap Diajukan ({{ $totalEligible }})</span>
                </h3>
            </div>
            <div class="card-body pt-0">
                <form method="POST" action="{{ route('superadmin.akreditasi.submit-pengajuan') }}"
                      data-swal-confirm="true"
                      data-swal-title="Konfirmasi pengajuan akreditasi"
                      data-swal-text="Profil pesantren akan dikunci dan tidak dapat diubah setelah pengajuan dibuat. Lanjutkan?"
                      data-swal-icon="question"
                      data-swal-confirm-button="Ya, ajukan"
                      data-swal-confirm-class="btn btn-primary">
                    @csrf
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-4">
                            <thead>
                                <tr class="text-gray-400 fw-bold fs-7 text-uppercase">
                                    <th class="w-25px"></th>
                                    <th>Pesantren</th>
                                    <th>NSP</th>
                                    <th>Status Data</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($eligible as $entry)
                                    <tr>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="pesantren_id"
                                                       value="{{ $entry['id'] }}" id="pesantren_{{ $entry['id'] }}"
                                                       @if($loop->first && $totalEligible === 1) checked @endif>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-gray-900">{{ $entry['name'] }}</div>
                                            <span class="text-muted fs-7">{{ $entry['email'] }}</span>
                                        </td>
                                        <td><span class="badge badge-light-primary">{{ $entry['nsp'] }}</span></td>
                                        <td><span class="badge badge-light-success">Data Lengkap</span></td>
                                        <td>
                                            <a href="{{ route('superadmin.master-data.users.index') }}" target="_blank" class="btn btn-sm btn-light">
                                                <i class="ki-outline ki-eye fs-2"></i>Detail
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-4 mt-4 pt-4 border-top">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="confirm_prerequisites" value="1" id="confirm_prerequisites" required>
                            <label class="form-check-label fw-semibold fs-7 text-gray-700" for="confirm_prerequisites">
                                Saya sudah memeriksa kelengkapan data profil, IPM, SDM, dan EDPM pesantren yang dipilih.
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="ki-outline ki-verify fs-2 me-1"></i>Ajukan Akreditasi
                        </button>
                        <a href="{{ route('superadmin.akreditasi.index') }}" class="btn btn-light">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Pending Prerequisites — must complete data first --}}
    @if(count($pendingPrerequisites))
        <div class="card card-flush mb-8">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title">
                    <span class="bullet bullet-vertical bg-warning h-25px me-3"></span>
                    <span class="fw-bold fs-4 text-gray-900">Data Belum Lengkap ({{ $totalPending }})</span>
                </h3>
            </div>
            <div class="card-body pt-0">
                <div class="notice d-flex bg-light-warning rounded border border-warning border-dashed p-6 mb-4">
                    <i class="ki-outline ki-warning-2 fs-2tx text-warning me-4"></i>
                    <div class="d-flex flex-stack flex-grow-1">
                        <div class="fw-semibold">
                            <div class="fs-6 text-gray-900">Pesantren berikut belum memenuhi prasyarat data.</div>
                            <div class="fs-7 text-muted">Lengkapi profil dan modul IPM, SDM, EDPM sebelum mengajukan.</div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-4">
                        <thead>
                            <tr class="text-gray-400 fw-bold fs-7 text-uppercase">
                                <th>Pesantren</th>
                                <th>NSP</th>
                                <th>Kekurangan Data</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingPrerequisites as $entry)
                                @php
                                    $missing = [];
                                    if (!$entry['completeness']['profilMinimum']) {
                                        $missing = array_merge($missing, $entry['completeness']['missingFields'] ?? []);
                                    }
                                    if (!($entry['completeness']['assessmentReady'] ?? false)) {
                                        if (!($entry['completeness']['hasUnits'] ?? false)) $missing[] = 'Unit Pesantren';
                                        if (!($entry['completeness']['hasIpm'] ?? false)) $missing[] = 'Data IPM';
                                        if (!($entry['completeness']['hasEdpm'] ?? false)) $missing[] = 'Data EDPM';
                                        if (!($entry['completeness']['hasSdm'] ?? false)) $missing[] = 'Data SDM';
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-bold text-gray-900">{{ $entry['name'] }}</div>
                                        <span class="text-muted fs-7">{{ $entry['email'] }}</span>
                                    </td>
                                    <td><span class="badge badge-light-primary">{{ $entry['nsp'] }}</span></td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($missing as $m)
                                                <span class="badge badge-light-danger">{{ $m }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('superadmin.master-data.users.index') }}" target="_blank" class="btn btn-sm btn-light-warning">
                                            <i class="ki-outline ki-pencil fs-2"></i>Lengkapi
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Active Akreditasi — already in workflow --}}
    @if(count($hasActiveAkreditasi))
        <div class="card card-flush mb-8">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title">
                    <span class="bullet bullet-vertical bg-danger h-25px me-3"></span>
                    <span class="fw-bold fs-4 text-gray-900">Masih Dalam Proses Akreditasi ({{ $totalBlocked }})</span>
                </h3>
            </div>
            <div class="card-body pt-0">
                <div class="notice d-flex bg-light-danger rounded border border-danger border-dashed p-6 mb-4">
                    <i class="ki-outline ki-information-2 fs-2tx text-danger me-4"></i>
                    <div class="fw-semibold">
                        <div class="fs-6 text-gray-900">Pesantren dengan pengajuan akreditasi yang masih aktif.</div>
                        <div class="fs-7 text-muted">Selesaikan atau batalkan pengajuan yang sedang berjalan sebelum membuat pengajuan baru.</div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-4">
                        <thead>
                            <tr class="text-gray-400 fw-bold fs-7 text-uppercase">
                                <th>Pesantren</th>
                                <th>NSP</th>
                                <th>Status Saat Ini</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hasActiveAkreditasi as $entry)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-gray-900">{{ $entry['name'] }}</div>
                                        <span class="text-muted fs-7">{{ $entry['email'] }}</span>
                                    </td>
                                    <td><span class="badge badge-light-primary">{{ $entry['nsp'] }}</span></td>
                                    <td><span class="badge badge-light-danger">{{ $entry['active_akreditasi_label'] }}</span></td>
                                    <td>
                                        <a href="{{ route('superadmin.akreditasi.show', $entry['active_akreditasi_id']) }}" class="btn btn-sm btn-light">
                                            <i class="ki-outline ki-eye fs-2"></i>Lihat
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Submit area for eligible list (when form is above) --}}
    @if(!count($eligible) && count($pendingPrerequisites))
        <div class="d-flex gap-3 mt-4">
            <a href="{{ route('superadmin.akreditasi.index') }}" class="btn btn-light">
                <i class="ki-outline ki-arrow-left fs-2"></i>Kembali ke Console
            </a>
            <a href="{{ route('superadmin.master-data.users.index') }}" class="btn btn-primary">
                <i class="ki-outline ki-profile-circle fs-2"></i>Kelola Data Pesantren
            </a>
        </div>
    @endif
@endif
@endsection
