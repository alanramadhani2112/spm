@extends('layouts.metronic.app')

@section('title', 'Detail Audit Log')
@section('pageTitle', 'Detail Audit Log')

@section('content')
<div class="mb-8">
    <a href="{{ route('superadmin.audit.index') }}"
       class="d-inline-flex align-items-center gap-1 fs-6 fw-semibold text-muted hover:text-gray-700">
        <i class="ki-outline ki-left fs-5"></i>
        Kembali ke Audit Log
    </a>
</div>

<x-metronic.card>
    <div class="d-grid gap-6">
        <div>
            <h2 class="fs-5 fw-bold text-gray-900">Rincian Log Audit</h2>
            <p class="mt-1 fs-7 text-muted">ID: {{ $log->id }}</p>
        </div>

        <div class="row g-5">
            <div class="col-md-3 rounded border border-gray-200 p-4">
                <p class="fs-7 fw-semibold text-muted text-uppercase ls-1r">Waktu</p>
                <p class="mt-1 fs-6 fw-semibold text-gray-900">{{ $log->created_at->format('d M Y, H:i:s') }}</p>
            </div>

            <div class="col-md-3 rounded border border-gray-200 p-4">
                <p class="fs-7 fw-semibold text-muted text-uppercase ls-1r">Tindakan</p>
                <p class="mt-1">
                    <x-metronic.badge type="primary">
                        {{ \App\Models\AkreditasiAuditLog::getActionTypeLabel($log->action_type) }}
                    </x-metronic.badge>
                </p>
            </div>

            <div class="col-md-3 rounded border border-gray-200 p-4">
                <p class="fs-7 fw-semibold text-muted text-uppercase ls-1r">Aktor</p>
                <p class="mt-1 fs-6 fw-semibold text-gray-900">{{ $log->user?->name ?? '—' }}</p>
                <p class="fs-8 text-gray-500">ID: {{ $log->user_id }}</p>
            </div>

            <div class="col-md-3 rounded border border-gray-200 p-4">
                <p class="fs-7 fw-semibold text-muted text-uppercase ls-1r">Akreditasi</p>
                <p class="mt-1 fs-6 font-monospace fw-semibold text-gray-900">{{ $log->akreditasi?->uuid ?? '—' }}</p>
                <p class="fs-8 text-gray-500">ID: {{ $log->akreditasi_id }}</p>
            </div>
        </div>

        @if($log->metadata)
            <div class="rounded border border-gray-200 p-4">
                <p class="fs-7 fw-semibold text-muted text-uppercase ls-1r mb-3">Metadata</p>
                <pre class="overflow-auto rounded bg-light p-4 fs-7 text-gray-700 font-monospace whitespace-pre-wrap">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        @endif
    </div>
</x-metronic.card>
@endsection
