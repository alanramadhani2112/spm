@extends('layouts.metronic.app')

@section('title', 'Audit Log')
@section('pageTitle', 'Audit Log')

@section('content')
{{-- Filters --}}
<x-metronic.card title="Filter" class="mb-8">
    <form method="GET" action="{{ route('superadmin.audit.index') }}" class="row g-5 align-items-end">
        <div class="col-md-3">
            <x-metronic.form-input name="actor" label="Aktor" placeholder="Semua" :value="request('actor')" />
        </div>
        <div class="col-md-3">
            <x-metronic.form-input name="action" label="Tindakan" type="select" :value="request('action')" :options="collect(['' => 'Semua'])->merge(\App\Models\AkreditasiAuditLog::distinct('action_type')->pluck('action_type')->mapWithKeys(fn($a) => [$a => \App\Models\AkreditasiAuditLog::getActionTypeLabel($a)]))->toArray()" />
        </div>
        <div class="col-md-2">
            <x-metronic.form-input name="start_date" label="Dari Tanggal" type="date" :value="request('start_date')" />
        </div>
        <div class="col-md-2">
            <x-metronic.form-input name="end_date" label="Sampai Tanggal" type="date" :value="request('end_date')" />
        </div>
        <div class="col-md-2 d-flex align-items-end gap-3">
            <button type="submit" class="btn btn-primary px-4 py-2 fs-6 fw-bold">
                Filter
            </button>
            <a href="{{ route('superadmin.audit.index') }}" class="btn btn-light px-4 py-2 fs-6 fw-semibold text-gray-700">
                Reset
            </a>
        </div>
    </form>
</x-metronic.card>

{{-- Audit table --}}
<x-metronic.card>
    @if($logs->isEmpty())
        <div class="py-8 text-center">
            <p class="fs-6 text-muted">Tidak ada log audit.</p>
        </div>
    @else
        <table class="table align-middle table-row-dashed fs-6 gy-5 w-100">
            <thead class="bg-light">
                <tr>
                    <th class="px-8 py-4 text-start fs-7 fw-bold text-uppercase ls-1r text-muted">Waktu</th>
                    <th class="px-8 py-4 text-start fs-7 fw-bold text-uppercase ls-1r text-muted">Aktor</th>
                    <th class="px-8 py-4 text-start fs-7 fw-bold text-uppercase ls-1r text-muted">Tindakan</th>
                    <th class="px-8 py-4 text-start fs-7 fw-bold text-uppercase ls-1r text-muted">Akreditasi</th>
                    <th class="px-8 py-4 text-end fs-7 fw-bold text-uppercase ls-1r text-muted">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td class="text-nowrap px-8 py-4 fs-6 text-muted">
                            {{ $log->created_at->format('d M Y, H:i') }}
                        </td>
                        <td class="text-nowrap px-8 py-4 fs-6 text-gray-900">
                            {{ $log->user?->name ?? '—' }}
                        </td>
                        <td class="text-nowrap px-8 py-4">
                            <x-metronic.badge type="primary">
                                {{ \App\Models\AkreditasiAuditLog::getActionTypeLabel($log->action_type) }}
                            </x-metronic.badge>
                        </td>
                        <td class="text-nowrap px-8 py-4 fs-6 font-monospace text-muted">
                            {{ \Illuminate\Support\Str::limit($log->akreditasi?->uuid, 12, '...') }}
                        </td>
                        <td class="text-nowrap px-8 py-4 text-end fs-6">
                            <a href="{{ route('superadmin.audit.show', $log->id) }}"
                               class="btn btn-light px-3 py-2 fs-7 fw-semibold text-gray-700">
                                Detail
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($logs->isNotEmpty())
        <div class="border-top border-gray-200 px-8 py-6">
            {{ $logs->links() }}
        </div>
    @endif
</x-metronic.card>
@endsection
