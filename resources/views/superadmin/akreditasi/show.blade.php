@extends('layouts.metronic.app')

@section('title', 'Detail Akreditasi')
@section('pageTitle', 'Detail Akreditasi')

@section('toolbar')
<a href="{{ route('superadmin.akreditasi.index') }}" class="btn btn-sm btn-light"><i class="ki-outline ki-left fs-4"></i>Kembali</a>
@endsection

@section('content')
@php
    use App\Models\Akreditasi;
    use App\Models\AkreditasiAuditLog;

    $statusColor = $statusColors[$akreditasi->status] ?? 'secondary';
    $profileDocs = collect($documentFields)->map(fn($label, $field) => [
        'label' => $label,
        'path' => $pesantren?->{$field},
    ])->filter(fn($doc) => filled($doc['path']));
    $dataItems = [
        'Profil' => $dataCompleteness['profil'],
        'Unit' => $dataCompleteness['unit'],
        'IPM' => $dataCompleteness['ipm'],
        'SDM' => $dataCompleteness['sdm'],
        'EDPM/IPR' => $dataCompleteness['edpm'],
    ];
    $countDataItems = function ($value): int {
        if (is_countable($value)) {
            return count($value);
        }

        if (is_object($value)) {
            return count(get_object_vars($value));
        }

        return filled($value) ? 1 : 0;
    };
    $instrumentItems = [
        ['key' => 'ipm', 'label' => 'IPM', 'data' => $ipm?->data ?? [], 'available' => (bool) $ipm, 'color' => 'primary'],
        ['key' => 'sdm', 'label' => 'SDM', 'data' => $sdm?->data ?? [], 'available' => (bool) $sdm, 'color' => 'info'],
        ['key' => 'edpm', 'label' => 'EDPM/IPR', 'data' => $edpm?->data ?? [], 'available' => (bool) $edpm, 'color' => 'success'],
    ];
    $workflowSteps = [
        ['label' => 'Pengajuan', 'icon' => 'ki-add-files', 'statuses' => [Akreditasi::STATUS_DRAFT_PROFILE, Akreditasi::STATUS_INITIAL_SUBMITTED, Akreditasi::STATUS_INITIAL_REJECTED]],
        ['label' => 'Assessment', 'icon' => 'ki-document', 'statuses' => [Akreditasi::STATUS_ASSESSMENT_OPEN]],
        ['label' => 'Review Admin', 'icon' => 'ki-search-list', 'statuses' => [Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW, Akreditasi::STATUS_ADMIN_STAGE_1_CORRECTION, Akreditasi::STATUS_ADMIN_STAGE_1_LIMIT_REVIEW]],
        ['label' => 'Asesor & Visitasi', 'icon' => 'ki-profile-user', 'statuses' => [Akreditasi::STATUS_ASSESSOR_ASSIGNMENT, Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW, Akreditasi::STATUS_ASSESSOR_STAGE_2_CORRECTION, Akreditasi::STATUS_ASSESSOR_STAGE_2_LIMIT_REVIEW, Akreditasi::STATUS_VISITASI_SCHEDULED, Akreditasi::STATUS_VISITASI_COMPLETED, Akreditasi::STATUS_POST_VISITASI_SCORING, Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED]],
        ['label' => 'Validasi', 'icon' => 'ki-shield-tick', 'statuses' => [Akreditasi::STATUS_ADMIN_FINAL_VALIDATION, Akreditasi::STATUS_ADMINISTRATIVE_REJECTED, Akreditasi::STATUS_FINAL_APPROVED, Akreditasi::STATUS_FINAL_REJECTED]],
        ['label' => 'SK / Selesai', 'icon' => 'ki-medal-star', 'statuses' => [Akreditasi::STATUS_APPEAL_SUBMITTED, Akreditasi::STATUS_COMPLETED]],
    ];
    $activeStep = collect($workflowSteps)->search(fn($step) => in_array($akreditasi->status, $step['statuses'], true));
    $activeStep = $activeStep === false ? 0 : $activeStep;
    $tabs = [
        ['key' => 'ringkasan', 'label' => 'Ringkasan', 'icon' => 'ki-category'],
        ['key' => 'pesantren', 'label' => 'Data Pesantren', 'icon' => 'ki-bank'],
        ['key' => 'dokumen', 'label' => 'Dokumen', 'icon' => 'ki-document'],
        ['key' => 'nilai', 'label' => 'Nilai', 'icon' => 'ki-chart-line'],
        ['key' => 'banding', 'label' => 'Banding', 'icon' => 'ki-message-question'],
        ['key' => 'audit', 'label' => 'Audit', 'icon' => 'ki-time'],
    ];
@endphp

<div class="card card-flush bg-light-primary border border-primary border-dashed mb-8">
    <div class="card-body p-8">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-6 mb-8">
            <div>
                <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                    <span class="badge badge-light-{{ $statusColor }} fs-7">{{ $akreditasi->getStatusLabel() }}</span>
                    <span class="badge badge-light-secondary">Siklus {{ $akreditasi->correction_cycle ?? 0 }}</span>
                </div>
                <h2 class="fw-bold text-gray-900 mb-2">{{ $pesantren?->nama_pesantren ?? $akreditasi->user?->name ?? 'Pesantren' }}</h2>
                <div class="fs-7 text-muted">UUID: <span class="font-monospace">{{ $akreditasi->uuid }}</span></div>
                <div class="fs-7 text-muted">Email: {{ $akreditasi->user?->email ?? '—' }}</div>
            </div>
            <div class="text-end">
                <div class="fs-8 text-muted mb-1">Tanggal Pengajuan</div>
                <div class="fw-bold text-gray-900">{{ $akreditasi->created_at->format('d M Y, H:i') }}</div>
                <div class="fs-8 text-muted mt-3">Deadline Assessment</div>
                <div class="fw-bold {{ $akreditasi->assessment_deadline?->isPast() ? 'text-danger' : 'text-gray-900' }}">{{ $akreditasi->assessment_deadline?->format('d M Y') ?? '—' }}</div>
            </div>
        </div>

        <div class="row g-4">
            @foreach($workflowSteps as $index => $step)
                @php
                    $isComplete = $index < $activeStep || $akreditasi->status === Akreditasi::STATUS_COMPLETED;
                    $isActive = $index === $activeStep && $akreditasi->status !== Akreditasi::STATUS_COMPLETED;
                    $stepColor = $isComplete ? 'success' : ($isActive ? $statusColor : 'secondary');
                @endphp
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="rounded border border-{{ $isActive ? $stepColor : 'gray-200' }} bg-white p-4 h-100">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <span class="symbol symbol-30px">
                                <span class="symbol-label bg-light-{{ $stepColor }}"><i class="ki-outline {{ $step['icon'] }} fs-4 text-{{ $stepColor }}"></i></span>
                            </span>
                            <span class="badge badge-light-{{ $stepColor }}">{{ $isComplete ? 'Selesai' : ($isActive ? 'Aktif' : 'Menunggu') }}</span>
                        </div>
                        <div class="fw-bold text-gray-900 fs-8">{{ $step['label'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $akreditasi->na1 ?? '—' }}" label="NA1" icon="ki-star" color="danger" /></div>
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $akreditasi->na2 ?? '—' }}" label="NA2" icon="ki-star" color="danger" /></div>
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $akreditasi->nilai ?? '—' }}" label="Nilai Akhir" icon="ki-chart" color="success" /></div>
    <div class="col-xl-3 col-md-6"><x-metronic.stat-card value="{{ $akreditasi->peringkat ?? '—' }}" label="Peringkat" icon="ki-medal-star" color="primary" /></div>
</div>

<div class="row g-5 g-xl-8">
    <div class="col-xl-4">
        <x-metronic.card title="Action Center">
            <x-slot:header>
                @if(! empty($actions))
                    <x-superadmin.action-menu label="Buka aksi workflow akreditasi {{ $akreditasi->uuid }}">
                        @foreach($actions as $action)
                            <div class="menu-item px-3">
                                <a href="{{ $action['route'] }}"
                                   class="menu-link px-3 d-flex align-items-center gap-2 text-{{ $action['color'] }}"
                                   data-swal-confirm="true"
                                   data-swal-title="Buka aksi {{ $action['label'] }}?"
                                   data-swal-text="Anda akan masuk ke halaman {{ $action['label'] }} untuk pengajuan {{ $akreditasi->uuid }}."
                                   data-swal-icon="question"
                                   data-swal-confirm-button="Ya, buka">
                                    <i class="ki-outline ki-right-square fs-4"></i>
                                    <span>{{ $action['label'] }}</span>
                                </a>
                            </div>
                        @endforeach
                    </x-superadmin.action-menu>
                @endif
            </x-slot:header>

            @if(empty($actions))
                <x-metronic.alert type="info" message="Tidak ada aksi workflow aktif untuk status ini." />
            @else
                <div class="rounded bg-light-primary p-4 fs-7 text-gray-700">
                    Gunakan icon aksi di kanan atas kartu ini untuk membuka opsi workflow yang tersedia.
                </div>
            @endif
        </x-metronic.card>

        <x-metronic.card title="Kelengkapan Data" class="mt-6">
            <div class="d-grid gap-3">
                @foreach($dataItems as $label => $ok)
                    <div class="d-flex justify-content-between align-items-center rounded border border-gray-200 p-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="symbol symbol-30px"><span class="symbol-label bg-light-{{ $ok ? 'success' : 'warning' }}"><i class="ki-outline {{ $ok ? 'ki-check' : 'ki-information-4' }} fs-4 text-{{ $ok ? 'success' : 'warning' }}"></i></span></span>
                            <span class="fw-semibold text-gray-800">{{ $label }}</span>
                        </div>
                        <span class="badge badge-light-{{ $ok ? 'success' : 'warning' }}">{{ $ok ? 'Lengkap' : 'Belum' }}</span>
                    </div>
                @endforeach
            </div>
        </x-metronic.card>

        <x-metronic.card title="Asesor" class="mt-6">
            @forelse($akreditasi->assessments as $assessment)
                <div class="d-flex align-items-center justify-content-between border rounded p-3 mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <span class="symbol symbol-35px"><span class="symbol-label bg-light-info text-info fw-bold">{{ strtoupper(substr($assessment->asesor?->name ?? '?', 0, 2)) }}</span></span>
                        <div>
                            <div class="fw-bold text-gray-900">{{ $assessment->asesor?->name ?? '—' }}</div>
                            <div class="fs-8 text-muted">{{ $assessment->asesor?->email ?? '—' }}</div>
                        </div>
                    </div>
                    <span class="badge badge-light-info">{{ strtoupper($assessment->tipe) }}</span>
                </div>
            @empty
                <div class="text-center py-8 text-muted border rounded bg-light">Belum ada asesor ditugaskan.</div>
            @endforelse
        </x-metronic.card>
    </div>

    <div class="col-xl-8" x-data="{ activeTab: 'ringkasan' }">
        <nav class="d-flex flex-wrap gap-2 mb-6" aria-label="Navigasi detail akreditasi">
            @foreach($tabs as $tab)
                <button type="button"
                        @click="activeTab = '{{ $tab['key'] }}'"
                        class="btn btn-sm btn-light btn-color-gray-600 border-transparent fw-semibold">
                    <i class="ki-outline {{ $tab['icon'] }} fs-4"></i>{{ $tab['label'] }}
                </button>
            @endforeach
        </nav>

        <div x-show="activeTab === 'ringkasan'">
            <x-metronic.card title="Ringkasan Pengajuan">
                <div class="row g-5">
                    <div class="col-md-6">
                        <div class="rounded border border-gray-200 p-4 h-100">
                            <div class="fs-8 text-muted mb-1">Pesantren</div>
                            <div class="fw-bold text-gray-900">{{ $pesantren?->nama_pesantren ?? $akreditasi->user?->name ?? '—' }}</div>
                            <div class="fs-8 text-muted mt-2">{{ $akreditasi->user?->email ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="rounded border border-gray-200 p-4 h-100">
                            <div class="fs-8 text-muted mb-1">Status Workflow</div>
                            <span class="badge badge-light-{{ $statusColor }}">{{ $akreditasi->getStatusLabel() }}</span>
                            <div class="fs-8 text-muted mt-2">Siklus koreksi: {{ $akreditasi->correction_cycle ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="rounded border border-gray-200 p-4 text-center h-100">
                            <div class="fs-8 text-muted">Dokumen Profil</div>
                            <div class="fs-4 fw-bold text-gray-900">{{ $profileDocs->count() }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="rounded border border-gray-200 p-4 text-center h-100">
                            <div class="fs-8 text-muted">Dokumen Upload</div>
                            <div class="fs-4 fw-bold text-gray-900">{{ $documents->count() }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="rounded border border-gray-200 p-4 text-center h-100">
                            <div class="fs-8 text-muted">Audit Log</div>
                            <div class="fs-4 fw-bold text-gray-900">{{ $akreditasi->auditLogs->count() }}</div>
                        </div>
                    </div>
                </div>
            </x-metronic.card>
        </div>

        <div x-show="activeTab === 'pesantren'" hidden>
            <x-metronic.card title="Data Pesantren">
                <div class="row g-5">
                    @foreach([
                        'Nama Pesantren' => $pesantren?->nama_pesantren,
                        'NS Pesantren' => $pesantren?->ns_pesantren,
                        'Kontak' => $pesantren?->hp_wa ?? $pesantren?->telp_pesantren,
                        'Email' => $akreditasi->user?->email,
                    ] as $label => $value)
                        <div class="col-md-6">
                            <div class="fs-8 text-muted">{{ $label }}</div>
                            <div class="fw-bold text-gray-900">{{ $value ?? '—' }}</div>
                        </div>
                    @endforeach
                    <div class="col-md-12">
                        <div class="fs-8 text-muted">Alamat</div>
                        <div class="fw-semibold text-gray-800">{{ $pesantren?->alamat ?? '—' }}</div>
                    </div>
                    <div class="col-md-12">
                        <div class="fs-8 text-muted mb-2">Unit Pendidikan</div>
                        <div class="d-flex flex-wrap gap-2">
                            @forelse($pesantren?->units ?? [] as $unit)
                                <span class="badge badge-light-primary">{{ $unit->layanan_satuan_pendidikan }} · {{ $unit->jumlah_rombel }} rombel</span>
                            @empty
                                <span class="text-muted fs-8">Belum ada unit.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </x-metronic.card>

            <div class="row g-5 mt-1">
                @foreach($instrumentItems as $item)
                    <div class="col-md-4">
                        <x-metronic.card title="{{ $item['label'] }}">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <span class="badge badge-light-{{ $item['available'] ? $item['color'] : 'secondary' }}">{{ $item['available'] ? 'Tersedia' : 'Belum ada' }}</span>
                                <span class="fs-8 text-muted">{{ $countDataItems($item['data']) }} item</span>
                            </div>
                            <details>
                                <summary class="cursor-pointer fs-8 fw-semibold text-primary">Lihat data mentah</summary>
                                <pre class="bg-light rounded p-4 fs-8 text-gray-700 mt-3 mb-0">{{ json_encode($item['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </details>
                        </x-metronic.card>
                    </div>
                @endforeach
            </div>
        </div>

        <div x-show="activeTab === 'dokumen'" hidden>
            <x-metronic.card title="Dokumen">
                <div class="row g-4">
                    @forelse($profileDocs as $doc)
                        <div class="col-md-6">
                            <div class="border rounded p-4 h-100">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                    <div class="fw-semibold text-gray-900">{{ $doc['label'] }}</div>
                                    <span class="badge badge-light-primary">Profil</span>
                                </div>
                                <div class="fs-8 text-muted font-monospace">{{ basename($doc['path']) }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-8 text-muted border rounded bg-light">Belum ada dokumen profil pesantren.</div>
                    @endforelse

                    @foreach($documents as $document)
                        <div class="col-md-6">
                            <div class="border rounded p-4 h-100 bg-light">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                    <div class="fw-semibold text-gray-900">{{ $document->category?->name ?? $document->type ?? 'Dokumen' }}</div>
                                    <span class="badge badge-light-info">Upload</span>
                                </div>
                                <div class="fs-8 text-muted font-monospace">{{ basename($document->file_path ?? '') }}</div>
                                <div class="fs-8 text-muted mt-2">Uploader: {{ $document->uploader?->name ?? '—' }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-metronic.card>
        </div>

        <div x-show="activeTab === 'nilai'" hidden>
            <x-metronic.card title="Nilai & EDPM Scores">
                <div class="row g-4 mb-6">
                    @foreach(['na1' => 'NA1', 'na2' => 'NA2', 'nk' => 'NK', 'nv' => 'NV'] as $field => $label)
                        <div class="col-md-3">
                            <div class="border rounded p-4 text-center h-100">
                                <div class="fs-8 text-muted">{{ $label }}</div>
                                <div class="fs-4 fw-bold text-gray-900">{{ $akreditasi->{$field} ?? '—' }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @forelse($edpmScores as $type => $scores)
                        <span class="badge badge-light-info">{{ strtoupper($type ?: 'unknown') }}: {{ $scores->count() }} butir</span>
                    @empty
                        <span class="text-muted fs-7">Belum ada skor butir.</span>
                    @endforelse
                </div>
            </x-metronic.card>
        </div>

        <div x-show="activeTab === 'banding'" hidden>
            <x-metronic.card title="Banding">
                @forelse($akreditasi->bandings as $banding)
                    <div class="border rounded p-4 mb-3">
                        <div class="d-flex justify-content-between gap-4 mb-3">
                            <span class="badge badge-light-{{ $banding->status === 'pending' ? 'warning' : ($banding->status === 'accepted' ? 'success' : 'danger') }}">{{ $banding->status }}</span>
                            <span class="fs-8 text-muted">{{ $banding->created_at->format('d M Y, H:i') }}</span>
                        </div>
                        <div class="fw-semibold text-gray-900 mb-1">Alasan</div>
                        <div class="fs-7 text-muted mb-3">{{ $banding->reason ?? '—' }}</div>
                        <div class="fw-semibold text-gray-900 mb-1">Respon</div>
                        <div class="fs-7 text-muted">{{ $banding->admin_response ?? 'Belum diproses' }}</div>
                        @if($banding->processor)
                            <div class="fs-8 text-muted mt-3">Diproses oleh: {{ $banding->processor->name }}</div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-10 text-muted border rounded bg-light">Belum ada banding.</div>
                @endforelse
            </x-metronic.card>
        </div>

        <div x-show="activeTab === 'audit'" hidden>
            <x-metronic.card title="Audit Timeline">
                <div class="timeline-label">
                    @forelse($akreditasi->auditLogs->sortByDesc('created_at') as $log)
                        @php $actor = $log->user ?? $actorUsers->get($log->actor_user_id); @endphp
                        <div class="timeline-item mb-6">
                            <div class="timeline-label fw-bold text-gray-800 fs-8">{{ $log->created_at?->format('d M H:i') ?? '—' }}</div>
                            <div class="timeline-badge"><i class="fa fa-genderless text-primary fs-1"></i></div>
                            <div class="timeline-content fw-semibold text-gray-800 ps-3">
                                <div>{{ AkreditasiAuditLog::getActionTypeLabel($log->action_type ?? 'status_changed') }}</div>
                                @if($log->from_status || $log->to_status)
                                    <div class="fs-8 text-muted">{{ $log->from_status ?? '—' }} → {{ $log->to_status ?? '—' }}</div>
                                @endif
                                <div class="fs-8 text-muted">Aktor: {{ $actor?->name ?? '—' }}</div>
                                @if($log->reason)
                                    <div class="rounded bg-light p-3 fs-8 text-gray-700 mt-2">{{ $log->reason }}</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 text-muted border rounded bg-light">Belum ada audit log.</div>
                    @endforelse
                </div>
            </x-metronic.card>
        </div>
    </div>
</div>
@endsection
