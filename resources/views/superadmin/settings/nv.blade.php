@extends('layouts.metronic.app')

@section('title', 'Pengaturan Nilai Visitasi (NV)')
@section('pageTitle', 'Pengaturan Nilai Visitasi (NV)')

@section('content')
@include('superadmin.settings._nav')

<x-metronic.card title="Pengaturan Nilai Visitasi (NV)">
    <div class="d-grid gap-6">
        {{-- nv_override_allowed --}}
        <div class="rounded border border-gray-200 p-4">
            <form method="POST" action="{{ route('superadmin.settings.update') }}" class="d-grid gap-3" data-swal-confirm="true" data-swal-title="Simpan perubahan setting?" data-swal-text="Setting Super Admin ini akan diperbarui dan tercatat di audit log." data-swal-icon="warning" data-swal-confirm-button="Ya, simpan" data-swal-confirm-class="btn btn-primary">
                @csrf
                <input type="hidden" name="key" value="nv_override_allowed">
                <label class="fs-6 fw-bold text-gray-800">Izinkan Override NV</label>
                <p class="fs-7 text-muted">Mengizinkan admin untuk menimpa nilai visitasi yang dihitung otomatis.</p>
                <div class="d-flex align-items-center gap-4">
                    <label class="d-flex align-items-center gap-2 cursor-pointer fs-6 text-gray-700">
                        <input type="radio" name="value" value="1"
                               class="text-primary border-gray-300"
                               {{ old('value', $settings['nv_override_allowed']) == true ? 'checked' : '' }} required>
                        Ya
                    </label>
                    <label class="d-flex align-items-center gap-2 cursor-pointer fs-6 text-gray-700">
                        <input type="radio" name="value" value="0"
                               class="text-primary border-gray-300"
                               {{ old('value', $settings['nv_override_allowed']) == false ? 'checked' : '' }} required>
                        Tidak
                    </label>
                </div>
                <x-metronic.form-input name="reason" placeholder="Alasan perubahan..." required="true" />
                <button type="submit" class="btn btn-primary px-4 py-2 fs-6 fw-bold">
                    Simpan
                </button>
            </form>
        </div>

        {{-- nv_reason_mode --}}
        <div class="rounded border border-gray-200 p-4">
            <form method="POST" action="{{ route('superadmin.settings.update') }}" class="d-grid gap-3" data-swal-confirm="true" data-swal-title="Simpan perubahan setting?" data-swal-text="Setting Super Admin ini akan diperbarui dan tercatat di audit log." data-swal-icon="warning" data-swal-confirm-button="Ya, simpan" data-swal-confirm-class="btn btn-primary">
                @csrf
                <input type="hidden" name="key" value="nv_reason_mode">
                <label class="fs-6 fw-bold text-gray-800">Mode Alasan Override NV</label>
                <div class="d-grid gap-2">
                    @php $reasonValue = old('value', $settings['nv_reason_mode']); @endphp
                    <label class="d-flex align-items-center gap-2 cursor-pointer fs-6 text-gray-700">
                        <input type="radio" name="value" value="collective"
                               class="text-primary border-gray-300"
                               {{ $reasonValue === 'collective' ? 'checked' : '' }} required>
                        Kolektif — Satu alasan untuk seluruh NV
                    </label>
                    <label class="d-flex align-items-center gap-2 cursor-pointer fs-6 text-gray-700">
                        <input type="radio" name="value" value="per_butir"
                               class="text-primary border-gray-300"
                               {{ $reasonValue === 'per_butir' ? 'checked' : '' }} required>
                        Per Butir — Alasan untuk setiap butir penilaian
                    </label>
                </div>
                <x-metronic.form-input name="reason" placeholder="Alasan perubahan..." required="true" />
                <button type="submit" class="btn btn-primary px-4 py-2 fs-6 fw-bold">
                    Simpan
                </button>
            </form>
        </div>
    </div>
</x-metronic.card>
@endsection
