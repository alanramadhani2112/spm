@extends('layouts.metronic.app')

@section('title', 'Pengaturan Banding')
@section('pageTitle', 'Pengaturan Banding')

@section('content')
@include('superadmin.settings._nav')

<x-metronic.card title="Pengaturan Banding">
    <div class="d-grid gap-6">
        {{-- banding_eligibility --}}
        <div class="rounded border border-gray-200 p-4">
            <form method="POST" action="{{ route('superadmin.settings.update') }}" class="d-grid gap-3" data-swal-confirm="true" data-swal-title="Simpan perubahan setting?" data-swal-text="Setting Super Admin ini akan diperbarui dan tercatat di audit log." data-swal-icon="warning" data-swal-confirm-button="Ya, simpan" data-swal-confirm-class="btn btn-primary">
                @csrf
                <input type="hidden" name="key" value="banding_eligibility">
                <label class="fs-6 fw-bold text-gray-800">Kriteria Kelayakan Banding</label>
                <p class="fs-7 text-muted">Menentukan siapa yang dapat mengajukan banding.</p>
                <div class="d-grid gap-2">
                    @php $bandingValue = old('value', $settings['banding_eligibility']); @endphp
                    <label class="d-flex align-items-center gap-2 cursor-pointer fs-6 text-gray-700">
                        <input type="radio" name="value" value="all"
                               class="text-primary border-gray-300"
                               {{ $bandingValue === 'all' ? 'checked' : '' }} required>
                        Semua — Semua pesantren dapat mengajukan banding
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
