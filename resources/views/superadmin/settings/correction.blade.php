@extends('layouts.metronic.app')

@section('title', 'Pengaturan Koreksi')
@section('pageTitle', 'Pengaturan Koreksi')

@section('content')
@include('superadmin.settings._nav')

<x-metronic.card title="Pengaturan Koreksi">
    <div class="d-grid gap-6">
        {{-- max_siklus_tahap1 --}}
        <div class="rounded border border-gray-200 p-4">
            <form method="POST" action="{{ route('superadmin.settings.update') }}" class="d-grid gap-3" data-swal-confirm="true" data-swal-title="Simpan perubahan setting?" data-swal-text="Setting Super Admin ini akan diperbarui dan tercatat di audit log." data-swal-icon="warning" data-swal-confirm-button="Ya, simpan" data-swal-confirm-class="btn btn-primary">
                @csrf
                <input type="hidden" name="key" value="max_siklus_tahap1">
                <label class="fs-6 fw-bold text-gray-800">Maksimal Siklus Koreksi Tahap 1</label>
                <div class="d-flex align-items-center gap-3">
                    <x-metronic.form-input name="value" type="number" :value="old('value', $settings['max_siklus_tahap1'])" required="true" />
                    <span class="fs-7 text-muted">siklus</span>
                </div>
                <x-metronic.form-input name="reason" placeholder="Alasan perubahan..." required="true" />
                <button type="submit" class="btn btn-primary px-4 py-2 fs-6 fw-bold">
                    Simpan
                </button>
            </form>
        </div>

        {{-- max_siklus_tahap2 --}}
        <div class="rounded border border-gray-200 p-4">
            <form method="POST" action="{{ route('superadmin.settings.update') }}" class="d-grid gap-3" data-swal-confirm="true" data-swal-title="Simpan perubahan setting?" data-swal-text="Setting Super Admin ini akan diperbarui dan tercatat di audit log." data-swal-icon="warning" data-swal-confirm-button="Ya, simpan" data-swal-confirm-class="btn btn-primary">
                @csrf
                <input type="hidden" name="key" value="max_siklus_tahap2">
                <label class="fs-6 fw-bold text-gray-800">Maksimal Siklus Koreksi Tahap 2</label>
                <div class="d-flex align-items-center gap-3">
                    <x-metronic.form-input name="value" type="number" :value="old('value', $settings['max_siklus_tahap2'])" required="true" />
                    <span class="fs-7 text-muted">siklus</span>
                </div>
                <x-metronic.form-input name="reason" placeholder="Alasan perubahan..." required="true" />
                <button type="submit" class="btn btn-primary px-4 py-2 fs-6 fw-bold">
                    Simpan
                </button>
            </form>
        </div>

        {{-- action_on_limit --}}
        <div class="rounded border border-gray-200 p-4">
            <form method="POST" action="{{ route('superadmin.settings.update') }}" class="d-grid gap-3" data-swal-confirm="true" data-swal-title="Simpan perubahan setting?" data-swal-text="Setting Super Admin ini akan diperbarui dan tercatat di audit log." data-swal-icon="warning" data-swal-confirm-button="Ya, simpan" data-swal-confirm-class="btn btn-primary">
                @csrf
                <input type="hidden" name="key" value="action_on_limit">
                @php
                    $actionOptions = [
                        'reject' => 'Tolak Pengajuan',
                        'auto_approve' => 'Setujui Otomatis',
                        'freeze' => 'Bekukan',
                    ];
                @endphp
                <x-metronic.form-input name="value" label="Tindakan Saat Batas Siklus Tercapai" type="select" :value="old('value', $settings['action_on_limit'])" :options="$actionOptions" required="true" />
                <x-metronic.form-input name="reason" placeholder="Alasan perubahan..." required="true" />
                <button type="submit" class="btn btn-primary px-4 py-2 fs-6 fw-bold">
                    Simpan
                </button>
            </form>
        </div>
    </div>
</x-metronic.card>
@endsection
