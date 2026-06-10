@extends('layouts.metronic.app')

@section('title', 'Penugasan Asesor')
@section('pageTitle', 'Penugasan Asesor')

@section('content')
@php
    use App\Models\Akreditasi;
    $akreditasiRoutePrefix = $akreditasiRoutePrefix ?? 'admin.akreditasi';
@endphp
    @includeWhen($isSuperAdminView ?? false, 'superadmin._mode-banner')

    <div class="mb-6 flex flex-wrap align-items-center justify-content-between gap-4">
        <div>
            <h2 class="fs-5 fw-semibold text-gray-900">Penugasan Asesor</h2>
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

    {{-- Existing Assignments --}}
    @if($existing->isNotEmpty())
        <div class="rounded-3 bg-white shadow-sm mb-6">
            <div class="px-6 py-5">
                <h2 class="fs-6 fw-semibold text-gray-900">Asesor Saat Ini</h2>
            </div>
            <div class="px-6 py-4">
                <ul class="divide-y divide-gray-100">
                    @foreach($existing as $assignment)
                        <li class="d-flex align-items-center gap-3 py-3">
                            <div class="d-flex size-9 align-items-center justify-content-center rounded-pill bg-purple-100 text-info fw-semibold fs-8">
                                {{ strtoupper(substr($assignment->asesor?->name ?? '?', 0, 2)) }}
                            </div>
                            <div>
                                <p class="fs-7 fw-medium text-gray-900">{{ $assignment->asesor?->name ?? 'Tidak diketahui' }}</p>
                                <p class="fs-8 text-muted">{{ $assignment->asesor?->email ?? '—' }}</p>
                            </div>
                            <span class="ml-auto d-inline-flex align-items-center rounded-pill bg-light-info px-2 py-1 fs-8 fw-medium text-info">
                                {{ $assignment->role ?? 'Anggota' }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Assignment Form --}}
    <div class="rounded-3 bg-white shadow-sm">
        <div class="px-6 py-5">
            <h2 class="fs-6 fw-semibold text-gray-900">{{ $existing->isNotEmpty() ? 'Perbarui' : 'Tentukan' }} Penugasan Asesor</h2>
            <p class="mt-1 fs-7 text-muted">
                Pilih Ketua dan Anggota Asesor yang akan bertugas. Ketua wajib ditentukan.
            </p>
        </div>

        <div class="px-6 py-6">
            <form method="POST" action="{{ route($akreditasiRoutePrefix.'.assign-asesor', $akreditasi->id) }}" data-swal-confirm="true" data-swal-title="Simpan penugasan asesor?" data-swal-text="Tim asesor untuk pengajuan {{ $akreditasi->uuid }} akan diperbarui." data-swal-icon="question" data-swal-confirm-button="Ya, simpan" data-swal-confirm-class="btn btn-primary">
                @csrf

                <div class="d-grid gap-6">
                    {{-- Ketua Asesor --}}
                    <div>
                        <label for="ketua_id" class="block fs-7 fw-medium text-gray-700">
                            Ketua Asesor <span class="text-danger">*</span>
                        </label>
                        <select id="ketua_id" name="ketua_id" required
                                class="mt-1 block w-100 rounded border border-gray-200 bg-light px-3 py-2 fs-7 text-gray-900 focus:border-purple-500 focus:">
                            <option value="">— Pilih Ketua Asesor —</option>
                            @foreach($asesors as $asesor)
                                <option value="{{ $asesor->id }}" {{ old('ketua_id') == $asesor->id ? 'selected' : '' }}>
                                    {{ $asesor->name }} <span class="text-gray-500">({{ $asesor->email }})</span>
                                </option>
                            @endforeach
                        </select>
                        @if($asesors->isEmpty())
                            <p class="mt-1 fs-8 text-warning">Tidak ada user dengan role asesor. Pastikan role telah ditentukan.</p>
                        @endif
                    </div>

                    {{-- Anggota Asesor --}}
                    <div>
                        <label class="block fs-7 fw-medium text-gray-700">
                            Anggota Asesor
                        </label>
                        <p class="mt-1 fs-8 text-gray-500">Pilih satu atau lebih anggota asesor (opsional).</p>
                        <div class="mt-3 max-h-60 overflow-y-auto rounded border border-gray-200 bg-light p-3 d-grid gap-2">
                            @foreach($asesors as $asesor)
                                <label class="d-flex align-items-center gap-3 rounded px-3 py-2 cursor-pointer">
                                    <input type="checkbox" name="anggota_ids[]" value="{{ $asesor->id }}"
                                           class="w-15px h-15px rounded border-gray-300 text-purple-600"
                                           {{ in_array($asesor->id, old('anggota_ids', [])) ? 'checked' : '' }}>
                                    <div>
                                        <p class="fs-7">{{ $asesor->name }}</p>
                                        <p class="fs-8 text-gray-500">{{ $asesor->email }}</p>
                                    </div>
                                </label>
                            @endforeach
                            @if($asesors->isEmpty())
                                <p class="fs-7 text-gray-500 text-center py-2">Tidak ada asesor tersedia.</p>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-3 pt-2">
                        <a href="{{ route($akreditasiRoutePrefix.'.index') }}"
                           class="rounded border border-gray-200 bg-white px-4 py-2 fs-7 fw-medium text-gray-600 shadow-sm">
                            Batal
                        </a>
                        <button type="submit"
                                class="d-inline-flex align-items-center gap-2 rounded bg-purple-600 px-4 py-2 fs-7 fw-semibold text-white shadow-sm -purple-600">
                            <svg class="w-15px h-15px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            {{ $existing->isNotEmpty() ? 'Perbarui Asesor' : 'Tugaskan Asesor' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    </div>

@endsection
