@extends('layouts.metronic.app')

@section('title', 'Detail Akreditasi — Super Admin')
@section('pageTitle', 'Detail Akreditasi')

@section('toolbar')
<a href="{{ route('superadmin.akreditasi.index') }}" class="btn btn-sm btn-light"><i class="ki-outline ki-left fs-4"></i>Kembali</a>
@endsection

@section('content')
@php
    use App\Models\Akreditasi;
    use App\Models\AkreditasiAuditLog;
    use Illuminate\Support\Str;

    $statusColor = $statusColors[$akreditasi->status] ?? 'secondary';
    $workflowSteps = [
        ['label' => 'Pengajuan', 'statuses' => [Akreditasi::STATUS_DRAFT_PROFILE, Akreditasi::STATUS_INITIAL_SUBMITTED, Akreditasi::STATUS_INITIAL_REJECTED]],
        ['label' => 'Assessment', 'statuses' => [Akreditasi::STATUS_ASSESSMENT_OPEN]],
        ['label' => 'Review & Asesor', 'statuses' => [Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW, Akreditasi::STATUS_ADMIN_STAGE_1_CORRECTION, Akreditasi::STATUS_ADMIN_STAGE_1_LIMIT_REVIEW, Akreditasi::STATUS_ASSESSOR_ASSIGNMENT, Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW, Akreditasi::STATUS_ASSESSOR_STAGE_2_CORRECTION, Akreditasi::STATUS_ASSESSOR_STAGE_2_LIMIT_REVIEW]],
        ['label' => 'Visitasi & Scoring', 'statuses' => [Akreditasi::STATUS_VISITASI_SCHEDULED, Akreditasi::STATUS_VISITASI_COMPLETED, Akreditasi::STATUS_POST_VISITASI_SCORING, Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED]],
        ['label' => 'Validasi & SK', 'statuses' => [Akreditasi::STATUS_ADMIN_FINAL_VALIDATION, Akreditasi::STATUS_ADMINISTRATIVE_REJECTED, Akreditasi::STATUS_FINAL_APPROVED, Akreditasi::STATUS_FINAL_REJECTED, Akreditasi::STATUS_APPEAL_SUBMITTED, Akreditasi::STATUS_COMPLETED]],
    ];
    $activeStep = collect($workflowSteps)->search(fn($step) => in_array($akreditasi->status, $step['statuses'], true));
    $activeStep = $activeStep === false ? 0 : $activeStep;
    $hasBanding = $akreditasi->bandings->isNotEmpty();
    $tabs = [
        ['key' => 'ringkasan', 'label' => 'Ringkasan', 'icon' => 'ki-category'],
        ['key' => 'dokumen', 'label' => 'Dokumen', 'icon' => 'ki-document'],
        ['key' => 'nilai', 'label' => 'Nilai', 'icon' => 'ki-chart-line'],
    ];
    if ($hasBanding) { $tabs[] = ['key' => 'banding', 'label' => 'Banding', 'icon' => 'ki-message-question']; }
    $tabs[] = ['key' => 'audit', 'label' => 'Audit', 'icon' => 'ki-time'];

    $showUploadKkAction = $akreditasi->status === Akreditasi::STATUS_ASSESSMENT_OPEN;
    $showMarkVisitasiDoneAction = $akreditasi->status === Akreditasi::STATUS_VISITASI_SCHEDULED;
    $showSubmitVisitasiResultAction = $akreditasi->status === Akreditasi::STATUS_POST_VISITASI_SCORING;
    $needsAttention = in_array($akreditasi->status, [Akreditasi::STATUS_INITIAL_SUBMITTED, Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW, Akreditasi::STATUS_ADMIN_STAGE_1_LIMIT_REVIEW, Akreditasi::STATUS_ASSESSOR_ASSIGNMENT, Akreditasi::STATUS_POST_VISITASI_SCORING, Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED, Akreditasi::STATUS_ADMIN_FINAL_VALIDATION, Akreditasi::STATUS_FINAL_APPROVED, Akreditasi::STATUS_APPEAL_SUBMITTED], true);

    $profileDocs = collect($documentFields)->map(fn($label, $field) => ['label' => $label, 'path' => $pesantren?->{$field}])->filter(fn($doc) => filled($doc['path']));
    $dataItems = ['Profil' => $dataCompleteness['profil'], 'Unit' => $dataCompleteness['unit'], 'IPM' => $dataCompleteness['ipm'], 'SDM' => $dataCompleteness['sdm'], 'EDPM/IPR' => $dataCompleteness['edpm']];

    $alerts = [];
    if ($showUploadKkAction) { $alerts[] = 'Kartu kendali perlu diunggah sebelum alur berlanjut.'; }
    if ($showSubmitVisitasiResultAction && (! $akreditasi->is_na1_final || ! $akreditasi->is_na2_final || ! $akreditasi->is_nk_final)) { $alerts[] = 'Finalisasi NA1, NA2, dan NK sebelum submit hasil visitasi.'; }
    if ($needsAttention && count($primaryAction ? [$primaryAction] : []) === 0) { $alerts[] = 'Status ini memerlukan perhatian — periksa detail berikutnya.'; }
@endphp

<x-superadmin.breadcrumb :items="[['label' => 'Dashboard', 'route' => 'superadmin.dashboard'], ['label' => 'Konsol Akreditasi', 'route' => 'superadmin.akreditasi.index'], ['label' => $akreditasi->uuid, 'active' => true]]" />

@if(count($alerts) > 0)
    <div class="alert alert-dismissible bg-light-warning border border-warning border-dashed d-flex align-items-center gap-3 p-4 mb-6">
        <i class="ki-outline ki-information-4 fs-2 text-warning"></i>
        <div class="flex-grow-1">
            <div class="fw-bold text-gray-900 mb-1">Perhatian</div>
            @foreach($alerts as $alert)<div class="fs-8 text-gray-700">{{ $alert }}</div>@endforeach
        </div>
    </div>
@endif

<div class="row g-5 g-xl-8 mb-6">
    <div class="col-xl-8">
        <x-metronic.card title="Proses Akreditasi">
            <div class="d-flex flex-wrap align-items-center gap-2">
                @foreach($workflowSteps as $index => $step)
                    @php $isComplete = $index < $activeStep || $akreditasi->status === Akreditasi::STATUS_COMPLETED; $isActive = $index === $activeStep && $akreditasi->status !== Akreditasi::STATUS_COMPLETED; @endphp
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge badge-light-{{ $isComplete ? 'success' : ($isActive ? $statusColor : 'secondary') }} px-3 py-2">{{ $index + 1 }}. {{ $step['label'] }}</span>
                        @if($index < count($workflowSteps) - 1)<span class="text-muted mx-1">→</span>@endif
                    </div>
                @endforeach
            </div>
            <div class="mt-4 d-flex flex-wrap align-items-center gap-3">
                <div class="fw-bold text-gray-900">Status saat ini:</div>
                <span class="badge badge-light-{{ $statusColor }} fs-7 px-3 py-2">{{ $akreditasi->getStatusLabel() ?? $akreditasi->status }}</span>
                <span class="fs-8 text-muted">UUID: {{ $akreditasi->uuid }}</span>
            </div>
            <div class="row g-3 mt-4">
                <div class="col-md-3"><div class="border rounded p-3 text-center"><div class="fs-8 text-muted">NA1</div><div class="fw-bold text-gray-900">{{ $akreditasi->na1 ?? '—' }}</div></div></div>
                <div class="col-md-3"><div class="border rounded p-3 text-center"><div class="fs-8 text-muted">NA2</div><div class="fw-bold text-gray-900">{{ $akreditasi->na2 ?? '—' }}</div></div></div>
                <div class="col-md-3"><div class="border rounded p-3 text-center"><div class="fs-8 text-muted">NK</div><div class="fw-bold text-gray-900">{{ $akreditasi->nilai ?? '—' }}</div></div></div>
                <div class="col-md-3"><div class="border rounded p-3 text-center"><div class="fs-8 text-muted">Peringkat</div><div class="fw-bold text-gray-900">{{ $akreditasi->peringkat ?? '—' }}</div></div></div>
            </div>
        </x-metronic.card>
    </div>

    <div class="col-xl-4">
        <x-metronic.card title="Tindakan">
            @if(! $primaryAction)
                <div class="text-center py-6 text-muted">Tidak ada tindakan untuk status ini.</div>
            @else
                <div class="rounded bg-light-primary p-4 mb-4">
                    <div class="fw-bold text-gray-900 mb-1">Langkah berikutnya</div>
                    <div class="fs-7 text-gray-700">{{ $nextStepLabel }}</div>
                </div>
                <a href="{{ $primaryAction['route'] }}"
                   class="btn btn-{{ $primaryAction['color'] === 'warning' ? 'warning' : ($primaryAction['color'] === 'danger' ? 'danger' : ($primaryAction['color'] === 'success' ? 'success' : 'primary')) }} w-100 mb-3"
                   data-swal-confirm="true"
                   data-swal-title="Buka {{ $primaryAction['label'] }}?"
                   data-swal-text="Lanjutkan ke halaman {{ $primaryAction['label'] }} untuk {{ $akreditasi->uuid }}."
                   data-swal-icon="question"
                   data-swal-confirm-button="Ya, buka">
                    <i class="ki-outline ki-right-square fs-3"></i>{{ $primaryAction['label'] }}
                </a>
            @endif

            @if($showUploadKkAction || $showMarkVisitasiDoneAction || $showSubmitVisitasiResultAction || count($secondaryActions) > 0)
                <x-superadmin.action-menu label="Tindakan Lain untuk {{ $akreditasi->uuid }}" buttonClass="btn-light w-100">
                    @if($showUploadKkAction)
                        <div class="menu-item px-3">
                            <form method="POST" action="{{ route('superadmin.akreditasi.upload-kk', $akreditasi) }}" enctype="multipart/form-data" class="px-3 py-2" data-swal-confirm="true" data-swal-title="Upload kartu kendali?" data-swal-text="Dokumen kartu kendali akan disimpan." data-swal-icon="question" data-swal-confirm-button="Ya, upload" data-swal-confirm-class="btn btn-primary">
                                @csrf
                                <div class="fw-bold fs-7 mb-2">Upload Kartu Kendali</div>
                                <input type="file" name="file" class="form-control form-control-sm mb-2" required>
                                <button type="submit" class="btn btn-sm btn-primary w-100">Upload</button>
                            </form>
                        </div>
                    @endif
                    @if($showMarkVisitasiDoneAction)
                        <div class="menu-item px-3">
                            <form method="POST" action="{{ route('superadmin.akreditasi.tandai-visitasi-selesai', $akreditasi) }}" class="px-3 py-2" data-swal-confirm="true" data-swal-title="Tandai visitasi selesai?" data-swal-text="Status akan masuk ke penilaian pasca visitasi." data-swal-icon="warning" data-swal-confirm-button="Ya, tandai selesai" data-swal-confirm-class="btn btn-warning">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-warning w-100">Tandai Visitasi Selesai</button>
                            </form>
                        </div>
                    @endif
                    @if($showSubmitVisitasiResultAction)
                        <div class="menu-item px-3">
                            <form method="POST" action="{{ route('superadmin.akreditasi.submit-hasil-visitasi', $akreditasi) }}" class="px-3 py-2" data-swal-confirm="true" data-swal-title="Submit hasil visitasi?" data-swal-text="Pastikan nilai dan laporan sudah final." data-swal-icon="warning" data-swal-confirm-button="Ya, submit" data-swal-confirm-class="btn btn-success">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success w-100">Submit Hasil Visitasi</button>
                            </form>
                        </div>
                    @endif
                    @foreach($secondaryActions as $action)
                        <div class="menu-item px-3">
                            <a href="{{ $action['route'] }}" class="menu-link px-3 d-flex align-items-center gap-2 text-{{ $action['color'] }}" data-swal-confirm="true" data-swal-title="Buka {{ $action['label'] }}?" data-swal-text="Lanjutkan ke halaman {{ $action['label'] }}" data-swal-icon="question" data-swal-confirm-button="Ya, buka">
                                <i class="ki-outline ki-right-square fs-4"></i><span>{{ $action['label'] }}</span>
                            </a>
                        </div>
                    @endforeach
                </x-superadmin.action-menu>
            @endif
        </x-metronic.card>
    </div>
</div>

<div x-data="{ activeTab: 'ringkasan' }">
    <ul class="nav nav-tabs nav-line-tabs mb-5">
        @foreach($tabs as $tab)
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2" :class="{ active: activeTab === '{{ $tab['key'] }}' }" href="#" @click.prevent="activeTab = '{{ $tab['key'] }}'">
                    <i class="ki-outline {{ $tab['icon'] }} fs-4"></i>{{ $tab['label'] }}
                </a>
            </li>
        @endforeach
    </ul>

    <div x-show="activeTab === 'ringkasan'">
        <div class="row g-5 g-xl-8">
            <div class="col-xl-6">
                <x-metronic.card title="Data Pesantren">
                    <div class="fw-bold text-gray-900 mb-3">{{ $pesantren?->nama_pesantren ?? '—' }}</div>
                    <div class="d-grid gap-2 fs-7">
                        <div class="d-flex justify-content-between"><span class="text-muted">NSPP</span><span>{{ $pesantren?->nspp ?? '—' }}</span></div>
                        <div class="d-flex justify-content-between"><span class="text-muted">Alamat</span><span>{{ $pesantren?->alamat ?? '—' }}</span></div>
                        <div class="d-flex justify-content-between"><span class="text-muted">Telepon</span><span>{{ $pesantren?->telepon ?? '—' }}</span></div>
                    </div>
                    <div class="separator separator-dashed my-4"></div>
                    <div class="fw-bold text-gray-900 mb-2">Kelengkapan Data</div>
                    @foreach($dataItems as $label => $ok)
                        <div class="d-flex justify-content-between align-items-center py-1">
                            <span class="fs-7">{{ $label }}</span>
                            <span class="badge badge-light-{{ $ok ? 'success' : 'warning' }}">{{ $ok ? 'Lengkap' : 'Belum' }}</span>
                        </div>
                    @endforeach
                </x-metronic.card>
            </div>
            <div class="col-xl-6">
                <x-metronic.card title="Dokumen Profil">
                    @forelse($profileDocs as $doc)
                        <div class="d-flex align-items-center gap-3 py-2">
                            <i class="ki-outline ki-document fs-4 text-primary"></i>
                            <div><div class="fw-semibold fs-7">{{ $doc['label'] }}</div><div class="fs-8 text-muted text-truncate" style="max-width:200px">{{ basename($doc['path']) }}</div></div>
                        </div>
                    @empty
                        <div class="text-muted fs-7">Belum ada dokumen profil.</div>
                    @endforelse
                </x-metronic.card>
            </div>
        </div>
    </div>

    <div x-show="activeTab === 'dokumen'" hidden>
        <x-metronic.card title="Dokumen Akreditasi">
            <div class="d-grid gap-4">
                @forelse($documents as $document)
                    <div class="d-flex justify-content-between align-items-start border rounded p-4">
                        <div>
                            <div class="fw-semibold text-gray-900">{{ $document->category?->name ?? 'Dokumen' }}</div>
                            <div class="fs-8 text-muted">{{ basename($document->file_path ?? '') }}</div>
                            <div class="fs-8 text-muted mt-1">{{ $document->uploader?->name ?? '—' }} · {{ $document->created_at?->format('d M Y') }}</div>
                        </div>
                        <span class="badge badge-light-{{ $document->status === 'approved' ? 'success' : 'warning' }}">{{ $document->status ?? 'menunggu' }}</span>
                    </div>
                @empty
                    <x-metronic.empty-state icon="ki-document" title="Belum ada dokumen" description="Dokumen akan muncul setelah diunggah oleh pesantren atau asesor." />
                @endforelse
            </div>
        </x-metronic.card>
    </div>

    <div x-show="activeTab === 'nilai'" hidden>
        <x-metronic.card title="Nilai & Skor">
            <div class="row g-4 mb-6">
                @foreach(['na1' => 'NA1', 'na2' => 'NA2', 'nk' => 'NK', 'nv' => 'NV'] as $field => $label)
                    <div class="col-md-3"><div class="border rounded p-4 text-center"><div class="fs-8 text-muted">{{ $label }}</div><div class="fs-4 fw-bold text-gray-900">{{ $akreditasi->{$field} ?? '—' }}</div></div></div>
                @endforeach
            </div>
            @if(!empty($edpmScores) && (is_array($edpmScores) || $edpmScores instanceof \Countable))
                <div class="d-flex flex-wrap gap-2">
                    @foreach($edpmScores as $type => $scores)
                        <span class="badge badge-light-info">{{ strtoupper($type) }}: {{ $scores->count() }} butir</span>
                    @endforeach
                </div>
            @endif
        </x-metronic.card>
    </div>

    @if($hasBanding)
    <div x-show="activeTab === 'banding'" hidden>
        <x-metronic.card title="Banding">
            @foreach($akreditasi->bandings as $banding)
                <div class="border rounded p-4 mb-3">
                    <div class="d-flex justify-content-between gap-4 mb-3">
                        <span class="badge badge-light-{{ $banding->status === 'pending' ? 'warning' : ($banding->status === 'accepted' ? 'success' : 'danger') }}">{{ $banding->status }}</span>
                        <span class="fs-8 text-muted">{{ $banding->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="fw-semibold text-gray-900 mb-1">Alasan</div><div class="fs-7 text-muted mb-3">{{ $banding->reason ?? '—' }}</div>
                    <div class="fw-semibold text-gray-900 mb-1">Respon</div><div class="fs-7 text-muted">{{ $banding->admin_response ?? 'Belum diproses' }}</div>
                    @if($banding->processor)<div class="fs-8 text-muted mt-3">Diproses: {{ $banding->processor->name }}</div>@endif
                </div>
            @endforeach
        </x-metronic.card>
    </div>
    @endif

    <div x-show="activeTab === 'audit'" hidden>
        <x-metronic.card title="Log Audit">
            <div class="timeline-label">
                @forelse($akreditasi->auditLogs->sortByDesc('created_at') as $log)
                    @php $actor = $log->user ?? $actorUsers->get($log->actor_user_id); @endphp
                    <div class="timeline-item mb-6">
                        <div class="timeline-label fw-bold text-gray-800 fs-8">{{ $log->created_at?->format('d M H:i') ?? '—' }}</div>
                        <div class="timeline-badge"><i class="fa fa-genderless text-primary fs-1"></i></div>
                        <div class="timeline-content fw-semibold text-gray-800 ps-3">
                            <div>{{ AkreditasiAuditLog::getActionTypeLabel($log->action_type ?? 'status_changed') }}</div>
                            @if($log->from_status || $log->to_status)<div class="fs-8 text-muted">{{ $log->from_status ?? '—' }} → {{ $log->to_status ?? '—' }}</div>@endif
                            <div class="fs-8 text-muted">{{ $actor?->name ?? '—' }}</div>
                            @if($log->reason)<div class="rounded bg-light p-3 fs-8 text-gray-700 mt-2">{{ $log->reason }}</div>@endif
                        </div>
                    </div>
                @empty
                    <x-metronic.empty-state icon="ki-time" title="Belum ada log audit" description="Aktivitas akan tercatat di sini seiring berjalannya proses akreditasi." />
                @endforelse
            </div>
        </x-metronic.card>
    </div>
</div>
@endsection


