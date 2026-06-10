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
@endphp

<div class="card card-flush bg-light-primary mb-8">
    <div class="card-body p-8">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-6">
            <div>
                <div class="d-flex align-items-center gap-3 mb-3">
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
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-semibold text-gray-800">{{ $label }}</span>
                        <span class="badge badge-light-{{ $ok ? 'success' : 'warning' }}">{{ $ok ? 'Lengkap' : 'Belum' }}</span>
                    </div>
                @endforeach
            </div>
        </x-metronic.card>

        <x-metronic.card title="Asesor" class="mt-6">
            @forelse($akreditasi->assessments as $assessment)
                <div class="d-flex align-items-center justify-content-between border-bottom py-3">
                    <div>
                        <div class="fw-bold text-gray-900">{{ $assessment->asesor?->name ?? '—' }}</div>
                        <div class="fs-8 text-muted">{{ $assessment->asesor?->email ?? '—' }}</div>
                    </div>
                    <span class="badge badge-light-info">{{ $assessment->tipe }}</span>
                </div>
            @empty
                <div class="text-muted fs-7">Belum ada asesor ditugaskan.</div>
            @endforelse
        </x-metronic.card>
    </div>

    <div class="col-xl-8">
        <x-metronic.card title="Data Pesantren">
            <div class="row g-5">
                <div class="col-md-6">
                    <div class="fs-8 text-muted">Nama Pesantren</div>
                    <div class="fw-bold text-gray-900">{{ $pesantren?->nama_pesantren ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="fs-8 text-muted">NS Pesantren</div>
                    <div class="fw-bold text-gray-900">{{ $pesantren?->ns_pesantren ?? '—' }}</div>
                </div>
                <div class="col-md-12">
                    <div class="fs-8 text-muted">Alamat</div>
                    <div class="fw-semibold text-gray-800">{{ $pesantren?->alamat ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="fs-8 text-muted">Unit Pendidikan</div>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        @forelse($pesantren?->units ?? [] as $unit)
                            <span class="badge badge-light-primary">{{ $unit->layanan_satuan_pendidikan }} · {{ $unit->jumlah_rombel }} rombel</span>
                        @empty
                            <span class="text-muted fs-8">Belum ada unit.</span>
                        @endforelse
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="fs-8 text-muted">Kontak</div>
                    <div class="fw-semibold text-gray-800">{{ $pesantren?->hp_wa ?? $pesantren?->telp_pesantren ?? '—' }}</div>
                </div>
            </div>
        </x-metronic.card>

        <div class="row g-5 mt-1">
            <div class="col-md-4">
                <x-metronic.card title="IPM">
                    <pre class="bg-light rounded p-4 fs-8 text-gray-700 mb-0">{{ json_encode($ipm?->data ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </x-metronic.card>
            </div>
            <div class="col-md-4">
                <x-metronic.card title="SDM">
                    <pre class="bg-light rounded p-4 fs-8 text-gray-700 mb-0">{{ json_encode($sdm?->data ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </x-metronic.card>
            </div>
            <div class="col-md-4">
                <x-metronic.card title="EDPM/IPR">
                    <pre class="bg-light rounded p-4 fs-8 text-gray-700 mb-0">{{ json_encode($edpm?->data ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </x-metronic.card>
            </div>
        </div>

        <x-metronic.card title="Dokumen" class="mt-6">
            <div class="row g-4">
                @forelse($profileDocs as $doc)
                    <div class="col-md-6">
                        <div class="border rounded p-4 h-100">
                            <div class="fw-semibold text-gray-900">{{ $doc['label'] }}</div>
                            <div class="fs-8 text-muted">{{ basename($doc['path']) }}</div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-muted fs-7">Belum ada dokumen profil pesantren.</div>
                @endforelse
                @foreach($documents as $document)
                    <div class="col-md-6">
                        <div class="border rounded p-4 h-100 bg-light">
                            <div class="fw-semibold text-gray-900">{{ $document->category?->name ?? $document->type ?? 'Dokumen' }}</div>
                            <div class="fs-8 text-muted">{{ basename($document->file_path ?? '') }}</div>
                            <div class="fs-8 text-muted">Uploader: {{ $document->uploader?->name ?? '—' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-metronic.card>

        <x-metronic.card title="Nilai & EDPM Scores" class="mt-6">
            <div class="row g-4 mb-6">
                @foreach(['na1' => 'NA1', 'na2' => 'NA2', 'nk' => 'NK', 'nv' => 'NV'] as $field => $label)
                    <div class="col-md-3">
                        <div class="border rounded p-4 text-center">
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

        <x-metronic.card title="Banding" class="mt-6">
            @forelse($akreditasi->bandings as $banding)
                <div class="border rounded p-4 mb-3">
                    <div class="d-flex justify-content-between gap-4 mb-2">
                        <span class="badge badge-light-{{ $banding->status === 'pending' ? 'warning' : ($banding->status === 'accepted' ? 'success' : 'danger') }}">{{ $banding->status }}</span>
                        <span class="fs-8 text-muted">{{ $banding->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="fw-semibold text-gray-900 mb-1">Alasan</div>
                    <div class="fs-7 text-muted mb-3">{{ $banding->reason ?? '—' }}</div>
                    <div class="fw-semibold text-gray-900 mb-1">Respon</div>
                    <div class="fs-7 text-muted">{{ $banding->admin_response ?? 'Belum diproses' }}</div>
                </div>
            @empty
                <div class="text-muted fs-7">Belum ada banding.</div>
            @endforelse
        </x-metronic.card>

        <x-metronic.card title="Audit Timeline" class="mt-6">
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
                                <div class="fs-8 text-muted">Alasan: {{ $log->reason }}</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-muted fs-7">Belum ada audit log.</div>
                @endforelse
            </div>
        </x-metronic.card>
    </div>
</div>
@endsection
