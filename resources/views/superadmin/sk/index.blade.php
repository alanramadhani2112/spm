@extends('layouts.metronic.app')

@section('title', 'Super Admin — SK Management')
@section('pageTitle', 'SK Management')

@section('toolbar')
<div class="d-flex flex-wrap align-items-center gap-2 gap-lg-3">
    <a href="{{ route('superadmin.akreditasi.index') }}" class="btn btn-sm btn-light">
        <i class="ki-outline ki-document fs-2"></i>Workflow Console
    </a>
    <a href="{{ route('superadmin.sk.export', array_filter(['period' => $period, 'status' => $status, 'certificate' => $certificate, 'q' => $search], fn($value) => filled($value))) }}" class="btn btn-sm btn-light-primary">
        <i class="ki-outline ki-exit-up fs-2"></i>Export CSV
    </a>
</div>
@endsection

@section('content')
@php
    use App\Models\Akreditasi;

    $activeFilterCount = collect([
        $period !== 'all',
        $status !== 'all',
        $certificate !== 'all',
        $search !== '',
    ])->filter()->count();
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-start gap-4 mb-8">
    <div class="mw-lg-650px">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <span class="badge badge-light-primary">Command Center SK</span>
            <span class="badge badge-light-secondary">{{ $stats['displayed'] ?? $skRows->count() }} item ditampilkan</span>
        </div>
        <h2 class="fs-2 fw-bold text-gray-900 mb-2">SK Management</h2>
        <p class="fs-7 text-muted mb-4">Pantau antrean penerbitan SK, kelengkapan sertifikat digital, dan masa berlaku akreditasi dari satu pusat kendali.</p>
        <a href="{{ route('superadmin.sk.index', ['status' => 'ready']) }}" class="btn btn-sm btn-warning">
            <i class="ki-outline ki-medal-star fs-4"></i>Fokus Siap Terbit
        </a>
    </div>
    <div class="d-flex flex-column align-items-xl-end gap-2 mw-lg-400px">
        <span class="fs-8 fw-semibold text-gray-700 text-xl-end">Status SK selalu dipasangkan dengan langkah berikutnya agar penerbitan, sertifikat, dan masa berlaku tidak luput dari pemantauan.</span>
        <div class="d-flex flex-wrap justify-content-xl-end gap-2">
            <span class="badge badge-light-warning">{{ $stats['ready'] }} siap terbit</span>
            <span class="badge badge-light-danger">{{ $stats['expired'] }} kedaluwarsa</span>
            <span class="badge badge-light-primary">{{ $stats['missingCertificatePublished'] ?? 0 }} sertifikat belum lengkap</span>
        </div>
    </div>
</div>

<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl col-md-6">
        <x-metronic.stat-card value="{{ $stats['ready'] }}" label="Siap Terbit" icon="ki-medal-star" color="warning">
            <span class="fs-8 fw-semibold text-warning">Perlu penerbitan SK</span>
        </x-metronic.stat-card>
    </div>
    <div class="col-xl col-md-6">
        <x-metronic.stat-card value="{{ $stats['published'] }}" label="SK Terbit" icon="ki-check-circle" color="success">
            <span class="fs-8 text-muted">Selesai dipublikasikan</span>
        </x-metronic.stat-card>
    </div>
    <div class="col-xl col-md-6">
        <x-metronic.stat-card value="{{ $stats['missingCertificatePublished'] ?? 0 }}" label="Belum Ada Sertifikat" icon="ki-file-deleted" color="danger">
            <span class="fs-8 fw-semibold text-danger">SK terbit tanpa sertifikat digital</span>
        </x-metronic.stat-card>
    </div>
    <div class="col-xl col-md-6">
        <x-metronic.stat-card value="{{ $stats['expired'] }}" label="Kedaluwarsa" icon="ki-warning" color="danger">
            <span class="fs-8 {{ ($stats['expiringSoon'] ?? 0) > 0 ? 'text-warning fw-semibold' : 'text-muted' }}">
                {{ ($stats['expiringSoon'] ?? 0) > 0 ? ($stats['expiringSoon'].' segera berakhir') : 'Pantau pembaruan berkala' }}
            </span>
        </x-metronic.stat-card>
    </div>
</div>

<x-metronic.card title="Command Center SK" flush>
    <x-slot:header>
        <div class="d-flex flex-wrap align-items-center justify-content-end gap-2">
            @if($activeFilterCount > 0)
                <span class="badge badge-light-secondary">{{ $activeFilterCount }} filter aktif</span>
                @if($period !== 'all')<span class="badge badge-light-info">Periode: {{ $period }}</span>@endif
                @if($status !== 'all')<span class="badge badge-light-warning">Status: {{ $statusOptions[$status] ?? $status }}</span>@endif
                @if($certificate !== 'all')<span class="badge badge-light-primary">Sertifikat: {{ $certificateOptions[$certificate] ?? $certificate }}</span>@endif
                @if($search !== '')<span class="badge badge-light-success">Cari: {{ $search }}</span>@endif
            @else
                <span class="badge badge-light">Tanpa filter aktif</span>
            @endif
        </div>
    </x-slot:header>

    @if(($stats['ready'] ?? 0) > 0)
        <div class="rounded border border-warning border-dashed bg-light-warning p-5 mb-8">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-4">
                <div class="d-flex align-items-start gap-4 mw-lg-650px">
                    <span class="symbol symbol-45px flex-shrink-0">
                        <span class="symbol-label bg-warning"><i class="ki-outline ki-medal-star fs-2 text-white"></i></span>
                    </span>
                    <div>
                        <div class="fw-bold text-gray-900 mb-1">Antrian siap terbit perlu tindakan</div>
                        <div class="fs-7 text-gray-700">{{ $stats['ready'] }} pengajuan sudah final approved dan menunggu nomor SK, masa berlaku, serta sertifikat digital bila tersedia.</div>
                    </div>
                </div>
                <a href="{{ route('superadmin.sk.index', ['status' => 'ready']) }}" class="btn btn-sm btn-warning">Fokus Siap Terbit</a>
            </div>
        </div>
    @elseif(($stats['expired'] ?? 0) > 0)
        <div class="rounded border border-danger border-dashed bg-light-danger p-5 mb-8">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-4">
                <div class="d-flex align-items-start gap-4 mw-lg-650px">
                    <span class="symbol symbol-45px flex-shrink-0">
                        <span class="symbol-label bg-danger"><i class="ki-outline ki-warning fs-2 text-white"></i></span>
                    </span>
                    <div>
                        <div class="fw-bold text-gray-900 mb-1">Masa berlaku SK perlu ditinjau</div>
                        <div class="fs-7 text-gray-700">{{ $stats['expired'] }} SK sudah melewati masa berlaku. Prioritaskan review pembaruan atau tindak lanjut status akreditasi.</div>
                    </div>
                </div>
                <a href="{{ route('superadmin.sk.index', ['status' => 'published']) }}" class="btn btn-sm btn-danger">Tinjau SK Terbit</a>
            </div>
        </div>
    @elseif(($stats['missingCertificatePublished'] ?? 0) > 0)
        <div class="rounded border border-primary border-dashed bg-light-primary p-5 mb-8">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-4">
                <div class="d-flex align-items-start gap-4 mw-lg-650px">
                    <span class="symbol symbol-45px flex-shrink-0">
                        <span class="symbol-label bg-primary"><i class="ki-outline ki-file-added fs-2 text-white"></i></span>
                    </span>
                    <div>
                        <div class="fw-bold text-gray-900 mb-1">Kelengkapan sertifikat digital perlu follow-up</div>
                        <div class="fs-7 text-gray-700">{{ $stats['missingCertificatePublished'] }} SK sudah terbit tetapi belum memiliki sertifikat digital untuk diunduh.</div>
                    </div>
                </div>
                <a href="{{ route('superadmin.sk.index', ['status' => 'published', 'certificate' => 'without']) }}" class="btn btn-sm btn-primary">Tampilkan Tanpa Sertifikat</a>
            </div>
        </div>
    @else
        <div class="rounded border border-success border-dashed bg-light-success p-5 mb-8">
            <div class="d-flex align-items-start gap-4">
                <span class="symbol symbol-45px flex-shrink-0">
                    <span class="symbol-label bg-success"><i class="ki-outline ki-check-circle fs-2 text-white"></i></span>
                </span>
                <div>
                    <div class="fw-bold text-gray-900 mb-1">Board SK terkendali</div>
                    <div class="fs-7 text-gray-700">Tidak ada antrean siap terbit, sinyal kedaluwarsa, atau sertifikat digital tertunda pada ringkasan saat ini.</div>
                </div>
            </div>
        </div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="fw-bold text-gray-900">Filter aktif</div>
            <div class="fs-8 text-muted">Cari berdasarkan UUID, nomor SK, nama pesantren, NSP, atau email.</div>
        </div>
        @if($activeFilterCount > 0)
            <a href="{{ route('superadmin.sk.index') }}" class="btn btn-sm btn-light">Reset Semua Filter</a>
        @endif
    </div>

    <form method="GET" action="{{ route('superadmin.sk.index') }}" class="row g-3 align-items-end mb-8">
        <div class="col-lg-2 col-md-6">
            <label class="form-label" for="filter_sk_period">Periode</label>
            <select id="filter_sk_period" name="period" class="form-select form-select-solid">
                @foreach($periodOptions as $value => $label)
                    <option value="{{ $value }}" @selected($period === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2 col-md-6">
            <label class="form-label" for="filter_sk_status">Status SK</label>
            <select id="filter_sk_status" name="status" class="form-select form-select-solid">
                @foreach($statusOptions as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2 col-md-6">
            <label class="form-label" for="filter_sk_certificate">Sertifikat</label>
            <select id="filter_sk_certificate" name="certificate" class="form-select form-select-solid">
                @foreach($certificateOptions as $value => $label)
                    <option value="{{ $value }}" @selected($certificate === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-4 col-md-8">
            <label class="form-label" for="filter_sk_search">Cari</label>
            <input id="filter_sk_search" type="search" name="q" value="{{ $search }}" class="form-control form-control-solid" placeholder="UUID, nomor SK, pesantren, NSP, email...">
        </div>
        <div class="col-lg-2 col-md-4 d-flex gap-2">
            <button class="btn btn-primary flex-grow-1">Terapkan</button>
            <a href="{{ route('superadmin.sk.index') }}" class="btn btn-light">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-row-bordered align-middle gs-0 gy-4">
            <thead>
                <tr class="fw-bold text-muted bg-light">
                    <th class="ps-4 min-w-260px">Pesantren</th>
                    <th class="min-w-170px">Status & Langkah</th>
                    <th class="min-w-170px">Nomor SK</th>
                    <th class="min-w-140px">Nilai</th>
                    <th class="min-w-190px">Masa Berlaku</th>
                    <th class="min-w-170px">Sertifikat</th>
                    <th class="text-end min-w-160px pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($skRows as $akreditasi)
                    @php
                        $expiryDate = $akreditasi->masa_berlaku_akhir;
                        $isExpired = $expiryDate && $expiryDate->isPast();
                        $isExpiringSoon = $expiryDate && ! $isExpired && now()->diffInDays($expiryDate, false) <= 60;
                        $missingCertificate = blank($akreditasi->sertifikat_path);
                        $isReady = $akreditasi->status === Akreditasi::STATUS_FINAL_APPROVED;
                        $rowStatusColor = $isReady ? 'warning' : ($isExpired || ($missingCertificate && $akreditasi->status === Akreditasi::STATUS_COMPLETED) ? 'danger' : ($isExpiringSoon ? 'warning' : 'success'));
                        $rowStatusHint = $isReady
                            ? 'Terbitkan SK sekarang'
                            : ($isExpired
                                ? 'Masa berlaku berakhir'
                                : ($isExpiringSoon
                                    ? 'Segera berakhir'
                                    : ($missingCertificate ? 'Lengkapi sertifikat digital' : 'Dokumen SK lengkap')));
                    @endphp
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-gray-900">{{ $akreditasi->user?->pesantren?->nama_pesantren ?? $akreditasi->user?->name ?? 'Pesantren' }}</div>
                            <div class="text-muted fs-8">{{ $akreditasi->user?->email ?? '—' }}</div>
                            <div class="text-muted fs-8 font-monospace">{{ \Illuminate\Support\Str::limit($akreditasi->uuid, 28) }}</div>
                        </td>
                        <td>
                            <div class="d-flex flex-column gap-2">
                                <span class="badge badge-light-{{ $rowStatusColor }} align-self-start">{{ $akreditasi->getStatusLabel() }}</span>
                                <span class="fs-8 fw-semibold text-{{ $rowStatusColor }}">{{ $rowStatusHint }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold text-gray-900">{{ $akreditasi->nomor_sk ?: 'Belum diterbitkan' }}</div>
                            <div class="fs-8 text-muted mt-1">{{ $isReady ? 'Nomor dibuat saat publish SK' : 'Nomor SK sudah terdokumentasi' }}</div>
                        </td>
                        <td>
                            <div class="fw-bold text-gray-900">{{ $akreditasi->nilai ?? '—' }}</div>
                            <div class="fs-8 text-muted">Peringkat: {{ $akreditasi->peringkat ?? '—' }}</div>
                        </td>
                        <td>
                            <div class="fs-8 text-muted">Mulai</div>
                            <div class="fw-semibold text-gray-900">{{ $akreditasi->masa_berlaku?->format('d M Y') ?? '—' }}</div>
                            <div class="fs-8 mt-1 {{ $isExpired ? 'text-danger fw-bold' : ($isExpiringSoon ? 'text-warning fw-semibold' : 'text-muted') }}">
                                Akhir: {{ $expiryDate?->format('d M Y') ?? '—' }}
                                @if($isExpired)
                                    · Kedaluwarsa
                                @elseif($isExpiringSoon)
                                    · ≤ 60 hari
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($akreditasi->sertifikat_path)
                                <span class="badge badge-light-success mb-2">Tersedia</span>
                                <div><a href="{{ route('superadmin.akreditasi.sertifikat.download', $akreditasi) }}" class="fs-8 fw-semibold text-primary">Unduh Sertifikat</a></div>
                            @else
                                <span class="badge badge-light-danger mb-2">Belum tersedia</span>
                                <div class="fs-8 text-muted">{{ $isReady ? 'Dapat diunggah saat publish SK.' : 'Lengkapi sertifikat digital.' }}</div>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <x-superadmin.action-menu label="Buka aksi SK {{ $akreditasi->uuid }}">
                                <div class="menu-item px-3">
                                    <a href="{{ route('superadmin.akreditasi.show', $akreditasi) }}" class="menu-link px-3 d-flex align-items-center gap-2">
                                        <i class="ki-outline ki-eye fs-4"></i>
                                        <span>Lihat Detail</span>
                                    </a>
                                </div>
                                @if($akreditasi->status === Akreditasi::STATUS_FINAL_APPROVED)
                                    <div class="menu-item px-3">
                                        <a href="{{ route('superadmin.akreditasi.form-terbitkan-sk', $akreditasi) }}" class="menu-link px-3 d-flex align-items-center gap-2 text-success">
                                            <i class="ki-outline ki-medal-star fs-4"></i>
                                            <span>Terbitkan SK</span>
                                        </a>
                                    </div>
                                @endif
                                @if($akreditasi->sertifikat_path)
                                    <div class="menu-item px-3">
                                        <a href="{{ route('superadmin.akreditasi.sertifikat.download', $akreditasi) }}" class="menu-link px-3 d-flex align-items-center gap-2 text-primary">
                                            <i class="ki-outline ki-file-down fs-4"></i>
                                            <span>Unduh Sertifikat</span>
                                        </a>
                                    </div>
                                @endif
                            </x-superadmin.action-menu>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="text-center py-12 text-muted border rounded bg-light">
                                <div class="fw-bold text-gray-900 mb-2">Tidak ada data SK yang sesuai dengan filter saat ini.</div>
                                <div class="fs-7 text-muted">Reset filter, fokus ke antrean siap terbit, atau buka workflow console untuk menemukan pengajuan final approved.</div>
                                <div class="mt-4 d-flex flex-wrap justify-content-center gap-2">
                                    <a href="{{ route('superadmin.sk.index') }}" class="btn btn-sm btn-light">Reset Filter</a>
                                    <a href="{{ route('superadmin.sk.index', ['status' => 'ready']) }}" class="btn btn-sm btn-light-warning">Lihat Siap Terbit</a>
                                    <a href="{{ route('superadmin.akreditasi.index', ['status' => Akreditasi::STATUS_FINAL_APPROVED]) }}" class="btn btn-sm btn-primary">Buka Workflow Console</a>
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
