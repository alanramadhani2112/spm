@extends('layouts.metronic.app')

@section('title', 'Validasi Akhir')
@section('pageTitle', 'Validasi Akhir')

@section('content')
@php
    use App\Models\Akreditasi;
    use App\Models\AkreditasiEdpm;
@endphp

    <div class="mb-6 flex flex-wrap align-items-center justify-content-between gap-4">
        <div>
            <h2 class="fs-5 fw-semibold text-gray-900">Validasi Akhir — Admin Final Validation</h2>
            <p class="mt-1 fs-7 text-muted">
                UUID: <span class="font-monospace">{{ $akreditasi->uuid }}</span>
                &middot; Pesantren: <span>{{ $akreditasi->user?->pesantren?->nama_pesantren ?? '—' }}</span>
            </p>
        </div>
        <x-metronic.badge type="warning" :label="$akreditasi->getStatusLabel()" pill />
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <x-metronic.alert type="success" :message="session('success')" />
    @endif

    @if(session('error'))
        <x-metronic.alert type="danger" :message="session('error')" />
    @endif

    @if($errors->any())
        <x-metronic.alert type="danger">
            <p class="fw-medium">Terjadi kesalahan validasi:</p>
            <ul class="mt-1 list-disc list-inside ms-4">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-metronic.alert>
    @endif

    {{-- Scoring Summary --}}
    <div class="row gap-4 mb-6">
        <div class="rounded-3 bg-white shadow-sm p-5">
            <p class="fs-8 fw-medium text-uppercase ls-1r text-gray-500">NA1 (Asesor 1)</p>
            <p class="mt-2 fs-2 fw-semibold text-gray-900">{{ number_format($akreditasi->na1 ?? 0, 2) }}</p>
        </div>
        <div class="rounded-3 bg-white shadow-sm p-5">
            <p class="fs-8 fw-medium text-uppercase ls-1r text-gray-500">NA2 (Asesor 2)</p>
            <p class="mt-2 fs-2 fw-semibold text-gray-900">{{ number_format($akreditasi->na2 ?? 0, 2) }}</p>
        </div>
        <div class="rounded-3 bg-white shadow-sm p-5">
            <p class="fs-8 fw-medium text-uppercase ls-1r text-gray-500">NK (Kelompok)</p>
            <p class="mt-2 fs-2 fw-semibold text-primary">{{ number_format($akreditasi->nk ?? 0, 2) }}</p>
        </div>
        <div class="rounded-3 bg-white shadow-sm p-5">
            <p class="fs-8 fw-medium text-uppercase ls-1r text-gray-500">NV (Final)</p>
            <p class="mt-2 fs-2 fw-semibold text-success">{{ number_format($akreditasi->nv ?? $akreditasi->nk ?? 0, 2) }}</p>
        </div>
    </div>

    {{-- NK Detail Table --}}
    <div class="rounded-3 bg-white shadow-sm mb-6">
        <div class="px-6 py-5">
            <h2 class="fs-6 fw-semibold text-gray-900">Rincian NK (Nilai Kelompok)</h2>
            <p class="mt-1 fs-7 text-muted">Nilai NK bersifat read-only. Kolom NV digunakan untuk melakukan override.</p>
        </div>

        <div class="px-6 py-4">
            @if($nkEntries->isEmpty())
                <div class="rounded border border-dashed border-gray-200 bg-light p-8 text-center">
                    <p class="fs-7 text-muted">Belum ada data NK yang tersedia.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="w-100 table-row-dashed">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 text-left fs-8 fw-semibold text-uppercase ls-1r text-muted">Butir ID</th>
                                <th class="px-4 py-3 text-right fs-8 fw-semibold text-uppercase ls-1r text-muted">NK</th>
                                <th class="px-4 py-3 text-right fs-8 fw-semibold text-uppercase ls-1r text-muted">NV Override</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($nkEntries as $butirId => $entry)
                                <tr class="hover:bg-light/50">
                                    <td class="text-nowrap px-4 py-3 fs-7 text-gray-900">{{ $butirId }}</td>
                                    <td class="text-nowrap px-4 py-3 fs-7 text-right text-gray-700">
                                        {{ number_format($entry->nilai ?? 0, 2) }}
                                    </td>
                                    <td class="text-nowrap px-4 py-3 fs-7 text-right">
                                        <span class="text-gray-500">—</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Override NV Form --}}
    <div class="rounded-3 bg-white shadow-sm mb-6" x-data="{ hasOverride: {{ old('nv_values') ? 'true' : 'false' }} }">
        <div class="px-6 py-5">
            <h2 class="fs-6 fw-semibold text-gray-900">Override NV</h2>
            <p class="mt-1 fs-7 text-muted">Gunakan fitur ini untuk meng-override nilai NV jika diperlukan. Isi alasan jika NV berbeda dari NK.</p>
        </div>

        <div class="px-6 py-6">
            <form method="POST" action="{{ route('admin.akreditasi.approve-final', $akreditasi->id) }}" id="approveFinalForm">
                @csrf

                <div class="space-y-5">
                    {{-- Override Toggle --}}
                    <label class="d-flex items-start gap-3 cursor-pointer">
                        <input type="checkbox"
                               class="mt-1 w-15px h-15px rounded border-gray-300 text-warning"
                               x-model="hasOverride"
                               x-on:change="if(!hasOverride) { document.getElementById('override_section').classList.add('hidden'); } else { document.getElementById('override_section').classList.remove('hidden'); }">
                        <div>
                            <p class="fs-7 fw-medium text-gray-700">Override Nilai NV</p>
                            <p class="fs-8 text-gray-500">Aktifkan jika Anda perlu mengubah nilai NV per butir.</p>
                        </div>
                    </label>

                    {{-- Override Inputs --}}
                    <div id="override_section" x-show="hasOverride" class="d-grid gap-3 rounded border border-yellow-200 bg-light-warning p-4">
                        <p class="fs-7 fw-medium text-yellow-800">Masukkan nilai NV override per butir (skala 0–4):</p>

                        @if($nkEntries->isNotEmpty())
                            <div class="row gap-3">
                                @foreach($nkEntries as $butirId => $entry)
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="fs-8 text-gray-600 w-16">{{ $butirId }}</span>
                                        <input type="number" name="nv_values[{{ $butirId }}]"
                                               min="0" max="4" step="0.01"
                                               value="{{ old("nv_values.{$butirId}", $entry->nilai ?? '') }}"
                                               class="block w-100 rounded border border-yellow-300 bg-white px-2 py-1.5 fs-7 text-gray-900 focus:border-yellow-500 focus:" />
                                        <span class="fs-8 text-gray-500">NK: {{ number_format($entry->nilai ?? 0, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Reason for override --}}
                    <div>
                        <label for="reason" class="block fs-7 fw-medium text-gray-700">
                            Alasan (wajib jika ada override)
                        </label>
                        <textarea id="reason" name="reason" rows="3"
                                  class="mt-1 block w-100 rounded border border-gray-200 bg-light px-3 py-2 fs-7 text-gray-900 placeholder:text-gray-500 focus:border-yellow-500 focus:"
                                  placeholder="Jelaskan alasan perubahan nilai...">{{ old('reason') }}</textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Actions --}}
    <div class="row gap-6">
        {{-- Approve --}}
        <div class="rounded-3 bg-white shadow-sm">
            <div class="px-6 py-5">
                <button type="submit" form="approveFinalForm"
                        class="d-inline-flex w-100 align-items-center justify-content-center gap-2 rounded btn btn-success px-4 py-2 fs-7 fw-semibold text-white shadow-sm">
                    <svg class="w-15px h-15px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    Setujui Validasi Akhir
                </button>
                <p class="mt-2 fs-8 text-gray-500 text-center">Akreditasi akan berstatus Final Approved.</p>
            </div>
        </div>

        {{-- Reject --}}
        <div class="rounded-3 bg-white shadow-sm">
            <div class="px-6 py-5">
                <h2 class="fs-6 fw-semibold text-danger">Tolak Validasi Akhir</h2>
                <p class="mt-1 fs-7 text-muted">Tolak hasil akreditasi karena tidak memenuhi standar.</p>
            </div>
            <div class="px-6 py-5">
                <form method="POST" action="{{ route('admin.akreditasi.tolak-final', $akreditasi->id) }}">
                    @csrf
                    <div class="mb-4">
                        <label for="reject_reason" class="block fs-7 fw-medium text-gray-700">
                            Alasan Penolakan <span class="text-danger">*</span>
                        </label>
                        <textarea id="reject_reason" name="reason" rows="3" required
                                  class="mt-1 block w-100 rounded border border-gray-200 bg-light px-3 py-2 fs-7 text-gray-900 placeholder:text-gray-500 focus:border-red-500 focus:"
                                  placeholder="Jelaskan alasan penolakan...">{{ old('reason') }}</textarea>
                        <p class="mt-1 fs-8 text-gray-500">Minimal 5 karakter.</p>
                    </div>
                    <button type="submit"
                            class="d-inline-flex w-100 align-items-center justify-content-center gap-2 rounded btn btn-danger px-4 py-2 fs-7 fw-semibold text-white shadow-sm">
                        <svg class="w-15px h-15px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                        Tolak Validasi Akhir
                    </button>
                </form>
            </div>
        </div>
    </div>
    </div>

@endsection
