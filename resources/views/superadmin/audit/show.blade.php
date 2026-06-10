@extends('layouts.metronic.app')

@section('title', 'Detail Audit Log')
@section('pageTitle', 'Detail Audit Log')

@section('toolbar')
<a href="{{ route('superadmin.audit.index') }}" class="btn btn-sm btn-light">
    <i class="ki-outline ki-left fs-4"></i>Kembali
</a>
@endsection

@section('content')
<div class="card card-flush bg-light-primary border border-primary border-dashed mb-8">
    <div class="card-body p-7">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-5">
            <div class="d-flex align-items-start gap-4">
                <div class="symbol symbol-45px">
                    <span class="symbol-label bg-primary"><i class="ki-outline ki-shield-tick fs-2 text-white"></i></span>
                </div>
                <div>
                    <h2 class="fw-bold text-gray-900 mb-1">Rincian Log Audit #{{ $log->id }}</h2>
                    <div class="fs-7 text-muted">Jejak aktivitas ini membantu melacak siapa melakukan apa, kapan, dan pada objek akreditasi mana.</div>
                </div>
            </div>
            @if($log->akreditasi_id)
                <a href="{{ route('superadmin.akreditasi.show', $log->akreditasi_id) }}" class="btn btn-sm btn-primary">
                    <i class="ki-outline ki-document fs-3"></i>Buka Akreditasi
                </a>
            @endif
        </div>
    </div>
</div>

<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl-3 col-md-6">
        <div class="card card-flush h-100">
            <div class="card-body p-6">
                <div class="fs-8 fw-bold text-muted text-uppercase mb-2">Waktu</div>
                <div class="fs-6 fw-bold text-gray-900">{{ $log->created_at?->format('d M Y') }}</div>
                <div class="fs-8 text-muted">{{ $log->created_at?->format('H:i:s') }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-flush h-100">
            <div class="card-body p-6">
                <div class="fs-8 fw-bold text-muted text-uppercase mb-2">Tindakan</div>
                <span class="badge badge-light-primary">{{ \App\Models\AkreditasiAuditLog::getActionTypeLabel($log->action_type) }}</span>
                <div class="fs-8 text-muted font-monospace mt-2">{{ $log->action_type }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-flush h-100">
            <div class="card-body p-6">
                <div class="fs-8 fw-bold text-muted text-uppercase mb-2">Aktor</div>
                <div class="fs-6 fw-bold text-gray-900">{{ $log->user?->name ?? 'Aktor tidak diketahui' }}</div>
                <div class="fs-8 text-muted">ID: {{ $log->user_id ?? $log->actor_user_id ?? '—' }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-flush h-100">
            <div class="card-body p-6">
                <div class="fs-8 fw-bold text-muted text-uppercase mb-2">Akreditasi</div>
                <div class="fs-7 fw-bold text-gray-900 font-monospace">{{ $log->akreditasi?->uuid ?? '—' }}</div>
                <div class="fs-8 text-muted">ID: {{ $log->akreditasi_id ?? '—' }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-5 g-xl-8">
    <div class="col-xl-5">
        <x-metronic.card title="Timeline Perubahan">
            <div class="timeline-label">
                <div class="timeline-item mb-6">
                    <div class="timeline-label fw-bold text-gray-800 fs-8">{{ $log->created_at?->format('H:i') }}</div>
                    <div class="timeline-badge"><i class="fa fa-genderless text-primary fs-1"></i></div>
                    <div class="timeline-content fw-semibold text-gray-800 ps-3">
                        <div>{{ \App\Models\AkreditasiAuditLog::getActionTypeLabel($log->action_type) }}</div>
                        @if($log->from_status || $log->to_status)
                            <div class="fs-8 text-muted mt-1">{{ $log->from_status ?? '—' }} → {{ $log->to_status ?? '—' }}</div>
                        @endif
                        @if($log->reason)
                            <div class="rounded bg-light p-3 fs-8 text-gray-700 mt-3">{{ $log->reason }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </x-metronic.card>
    </div>

    <div class="col-xl-7">
        <x-metronic.card title="Metadata Audit" flush>
            <x-slot:header>
                <span class="badge badge-light-{{ $log->metadata ? 'primary' : 'secondary' }}">{{ $log->metadata ? 'Tersedia' : 'Kosong' }}</span>
            </x-slot:header>

            @if($log->metadata)
                <pre class="overflow-auto rounded bg-light p-5 fs-7 text-gray-700 font-monospace whitespace-pre-wrap mb-0">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
            @else
                <div class="text-center py-10 text-muted border rounded bg-light">
                    Tidak ada metadata tambahan untuk log ini.
                </div>
            @endif
        </x-metronic.card>
    </div>
</div>
@endsection
