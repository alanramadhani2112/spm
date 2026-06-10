@extends('layouts.metronic.app')

@section('title', 'Pengaturan Deadline')
@section('pageTitle', 'Pengaturan Deadline')

@section('content')
@include('superadmin.settings._nav')

<x-metronic.card title="Pengaturan Deadline">
    <div class="d-grid gap-6">
        {{-- review_awal_deadline --}}
        <div class="rounded border border-gray-200 p-4">
            <form method="POST" action="{{ route('superadmin.settings.update') }}" class="d-grid gap-3">
                @csrf
                <input type="hidden" name="key" value="review_awal_deadline">
                <label class="fs-6 fw-bold text-gray-800">Deadline Review Awal (hari)</label>
                <div class="d-flex align-items-center gap-3">
                    <x-metronic.form-input name="value" type="number" :value="old('value', $settings['review_awal_deadline'])" required="true" />
                    <span class="fs-7 text-muted">hari</span>
                </div>
                <x-metronic.form-input name="reason" placeholder="Alasan perubahan..." required="true" />
                <button type="submit" class="btn btn-primary px-4 py-2 fs-6 fw-bold">
                    Simpan
                </button>
            </form>
        </div>

        {{-- assessment_deadline --}}
        <div class="rounded border border-gray-200 p-4">
            <form method="POST" action="{{ route('superadmin.settings.update') }}" class="d-grid gap-3">
                @csrf
                <input type="hidden" name="key" value="assessment_deadline">
                <label class="fs-6 fw-bold text-gray-800">Deadline Asesmen (hari)</label>
                <div class="d-flex align-items-center gap-3">
                    <x-metronic.form-input name="value" type="number" :value="old('value', $settings['assessment_deadline'])" required="true" />
                    <span class="fs-7 text-muted">hari</span>
                </div>
                <x-metronic.form-input name="reason" placeholder="Alasan perubahan..." required="true" />
                <button type="submit" class="btn btn-primary px-4 py-2 fs-6 fw-bold">
                    Simpan
                </button>
            </form>
        </div>

        {{-- review_tahap1_deadline --}}
        <div class="rounded border border-gray-200 p-4">
            <form method="POST" action="{{ route('superadmin.settings.update') }}" class="d-grid gap-3">
                @csrf
                <input type="hidden" name="key" value="review_tahap1_deadline">
                <label class="fs-6 fw-bold text-gray-800">Deadline Review Tahap 1 (hari)</label>
                <div class="d-flex align-items-center gap-3">
                    <x-metronic.form-input name="value" type="number" :value="old('value', $settings['review_tahap1_deadline'])" required="true" />
                    <span class="fs-7 text-muted">hari</span>
                </div>
                <x-metronic.form-input name="reason" placeholder="Alasan perubahan..." required="true" />
                <button type="submit" class="btn btn-primary px-4 py-2 fs-6 fw-bold">
                    Simpan
                </button>
            </form>
        </div>

        {{-- correction_tahap1_deadline --}}
        <div class="rounded border border-gray-200 p-4">
            <form method="POST" action="{{ route('superadmin.settings.update') }}" class="d-grid gap-3">
                @csrf
                <input type="hidden" name="key" value="correction_tahap1_deadline">
                <label class="fs-6 fw-bold text-gray-800">Deadline Koreksi Tahap 1 (hari)</label>
                <div class="d-flex align-items-center gap-3">
                    <x-metronic.form-input name="value" type="number" :value="old('value', $settings['correction_tahap1_deadline'])" required="true" />
                    <span class="fs-7 text-muted">hari</span>
                </div>
                <x-metronic.form-input name="reason" placeholder="Alasan perubahan..." required="true" />
                <button type="submit" class="btn btn-primary px-4 py-2 fs-6 fw-bold">
                    Simpan
                </button>
            </form>
        </div>

        {{-- review_tahap2_deadline --}}
        <div class="rounded border border-gray-200 p-4">
            <form method="POST" action="{{ route('superadmin.settings.update') }}" class="d-grid gap-3">
                @csrf
                <input type="hidden" name="key" value="review_tahap2_deadline">
                <label class="fs-6 fw-bold text-gray-800">Deadline Review Tahap 2 (hari)</label>
                <div class="d-flex align-items-center gap-3">
                    <x-metronic.form-input name="value" type="number" :value="old('value', $settings['review_tahap2_deadline'])" required="true" />
                    <span class="fs-7 text-muted">hari</span>
                </div>
                <x-metronic.form-input name="reason" placeholder="Alasan perubahan..." required="true" />
                <button type="submit" class="btn btn-primary px-4 py-2 fs-6 fw-bold">
                    Simpan
                </button>
            </form>
        </div>

        {{-- correction_tahap2_deadline --}}
        <div class="rounded border border-gray-200 p-4">
            <form method="POST" action="{{ route('superadmin.settings.update') }}" class="d-grid gap-3">
                @csrf
                <input type="hidden" name="key" value="correction_tahap2_deadline">
                <label class="fs-6 fw-bold text-gray-800">Deadline Koreksi Tahap 2 (hari)</label>
                <div class="d-flex align-items-center gap-3">
                    <x-metronic.form-input name="value" type="number" :value="old('value', $settings['correction_tahap2_deadline'])" required="true" />
                    <span class="fs-7 text-muted">hari</span>
                </div>
                <x-metronic.form-input name="reason" placeholder="Alasan perubahan..." required="true" />
                <button type="submit" class="btn btn-primary px-4 py-2 fs-6 fw-bold">
                    Simpan
                </button>
            </form>
        </div>

        {{-- scoring_deadline --}}
        <div class="rounded border border-gray-200 p-4">
            <form method="POST" action="{{ route('superadmin.settings.update') }}" class="d-grid gap-3">
                @csrf
                <input type="hidden" name="key" value="scoring_deadline">
                <label class="fs-6 fw-bold text-gray-800">Deadline Penilaian (hari)</label>
                <div class="d-flex align-items-center gap-3">
                    <x-metronic.form-input name="value" type="number" :value="old('value', $settings['scoring_deadline'])" required="true" />
                    <span class="fs-7 text-muted">hari</span>
                </div>
                <x-metronic.form-input name="reason" placeholder="Alasan perubahan..." required="true" />
                <button type="submit" class="btn btn-primary px-4 py-2 fs-6 fw-bold">
                    Simpan
                </button>
            </form>
        </div>

        {{-- banding_deadline --}}
        <div class="rounded border border-gray-200 p-4">
            <form method="POST" action="{{ route('superadmin.settings.update') }}" class="d-grid gap-3">
                @csrf
                <input type="hidden" name="key" value="banding_deadline">
                <label class="fs-6 fw-bold text-gray-800">Deadline Banding (hari)</label>
                <div class="d-flex align-items-center gap-3">
                    <x-metronic.form-input name="value" type="number" :value="old('value', $settings['banding_deadline'])" required="true" />
                    <span class="fs-7 text-muted">hari</span>
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
