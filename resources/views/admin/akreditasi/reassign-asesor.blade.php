@extends('layouts.metronic.app')

@section('title', 'Reassign Asesor')
@section('pageTitle', 'Reassign Asesor')

@section('content')
@php
    use App\Models\Akreditasi;
@endphp

    <div class="mb-6 flex flex-wrap align-items-center justify-content-between gap-4">
        <div>
            <h2 class="fs-5 fw-semibold text-gray-900">Reassign Asesor</h2>
            <p class="mt-1 fs-7 text-muted">
                UUID: <span class="font-monospace">{{ $akreditasi->uuid }}</span>
                &middot; Pesantren: <span>{{ $akreditasi->user?->pesantren?->nama_pesantren ?? '—' }}</span>
            </p>
        </div>
        <x-metronic.badge type="info" :label="$akreditasi->getStatusLabel()" pill />
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

    {{-- Warning --}}
    <div class="mb-6 rounded border border-yellow-100 bg-light-warning p-4">
        <div class="d-flex align-items-start gap-3">
            <i class="ki-outline ki-information-3 fs-2 text-warning mt-1"></i>
            <div class="fs-7">
                <p class="fw-medium">Perhatian: Reassign akan mengganti seluruh tim asesor.</p>
                <p class="mt-1 text-warning">
                    Semua penugasan asesor sebelumnya akan dihapus dan diganti dengan penugasan baru.
                    Pastikan Anda mengisi alasan reassign.
                </p>
            </div>
        </div>
    </div>

    {{-- Current Assignments --}}
    @if($existing->isNotEmpty())
        <div class="rounded-3 bg-white shadow-sm mb-6">
            <div class="px-6 py-5">
                <h2 class="fs-6 fw-semibold text-gray-900">Asesor Saat Ini</h2>
                <p class="mt-1 fs-7 text-muted">Tim asesor yang akan digantikan.</p>
            </div>
            <div class="px-6 py-4">
                <ul class="divide-y divide-gray-100">
                    @foreach($existing as $assignment)
                        <li class="d-flex align-items-center gap-3 py-3">
                            <div class="d-flex size-9 align-items-center justify-content-center rounded-pill bg-red-100 text-danger fw-semibold fs-8">
                                {{ strtoupper(substr($assignment->asesor?->name ?? '?', 0, 2)) }}
                            </div>
                            <div>
                                <p class="fs-7 fw-medium text-gray-900">{{ $assignment->asesor?->name ?? 'Tidak diketahui' }}</p>
                                <p class="fs-8 text-muted">{{ $assignment->asesor?->email ?? '—' }}</p>
                            </div>
                            <span class="ml-auto d-inline-flex align-items-center rounded-pill bg-light px-2 py-1 fs-8 fw-medium text-gray-600">
                                {{ $assignment->role ?? 'Anggota' }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Reassign Form --}}
    <div class="rounded-3 bg-white shadow-sm">
        <div class="px-6 py-5">
            <h2 class="fs-6 fw-semibold text-gray-900">Form Reassign Asesor</h2>
        </div>

        <div class="px-6 py-6">
            <form method="POST" action="{{ route('admin.akreditasi.reassign-asesor', $akreditasi->id) }}">
                @csrf

                <div class="d-grid gap-6">
                    {{-- Ketua Asesor --}}
                    <div>
                        <label for="ketua_id" class="block fs-7 fw-medium text-gray-700">
                            Ketua Asesor Baru <span class="text-danger">*</span>
                        </label>
                        <select id="ketua_id" name="ketua_id" required
                                class="mt-1 block w-100 rounded border border-gray-200 bg-light px-3 py-2 fs-7 text-gray-900 focus:border-red-500 focus:">
                            <option value="">— Pilih Ketua Asesor —</option>
                            @foreach($asesors as $asesor)
                                <option value="{{ $asesor->id }}" {{ old('ketua_id') == $asesor->id ? 'selected' : '' }}>
                                    {{ $asesor->name }} ({{ $asesor->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Anggota Asesor --}}
                    <div>
                        <label class="block fs-7 fw-medium text-gray-700">
                            Anggota Asesor Baru
                        </label>
                        <p class="mt-1 fs-8 text-gray-500">Pilih satu atau lebih anggota asesor (opsional).</p>
                        <div class="mt-3 max-h-60 overflow-y-auto rounded border border-gray-200 bg-light p-3 d-grid gap-2">
                            @foreach($asesors as $asesor)
                                <label class="d-flex align-items-center gap-3 rounded px-3 py-2 cursor-pointer">
                                    <input type="checkbox" name="anggota_ids[]" value="{{ $asesor->id }}"
                                           class="w-15px h-15px rounded border-gray-300 text-danger"
                                           {{ in_array($asesor->id, old('anggota_ids', [])) ? 'checked' : '' }}>
                                    <div>
                                        <p class="fs-7">{{ $asesor->name }}</p>
                                        <p class="fs-8 text-gray-500">{{ $asesor->email }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Reason --}}
                    <div>
                        <label for="reason" class="block fs-7 fw-medium text-gray-700">
                            Alasan Reassign
                        </label>
                        <textarea id="reason" name="reason" rows="3"
                                  class="mt-1 block w-100 rounded border border-gray-200 bg-light px-3 py-2 fs-7 text-gray-900 placeholder:text-gray-500 focus:border-red-500 focus:"
                                  placeholder="Jelaskan alasan pergantian asesor...">{{ old('reason') }}</textarea>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-3 pt-2">
                        <a href="{{ route('admin.akreditasi.index') }}"
                           class="rounded border border-gray-200 bg-white px-4 py-2 fs-7 fw-medium text-gray-600 shadow-sm">
                            Batal
                        </a>
                        <button type="submit"
                                class="d-inline-flex align-items-center gap-2 rounded btn btn-danger px-4 py-2 fs-7 fw-semibold text-white shadow-sm">
                            <svg class="w-15px h-15px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />
                            </svg>
                            Reassign Asesor
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    </div>

@endsection
