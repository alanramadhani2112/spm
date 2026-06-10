@extends('layouts.metronic.app')

@section('title', 'Buka Assessment')
@section('pageTitle', 'Buka Assessment')

@section('content')
@php
    use App\Models\Akreditasi;
@endphp

    <div class="mb-6 flex flex-wrap align-items-center justify-content-between gap-4">
        <div>
            <h2 class="fs-5 fw-semibold text-gray-900">Buka Assessment</h2>
            <p class="mt-1 fs-7 text-muted">
                UUID: <span class="font-monospace">{{ $akreditasi->uuid }}</span>
            </p>
        </div>
        <x-metronic.badge type="primary" :label="$akreditasi->getStatusLabel()" pill />
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

    {{-- Info --}}
    <div class="mb-6 rounded border border-blue-100 bg-light-primary p-4">
        <div class="d-flex align-items-start gap-3">
            <i class="ki-outline ki-information-4 fs-2 text-primary mt-1"></i>
            <div class="fs-7">
                <p class="fw-medium">Membuka Assessment:</p>
                <p class="mt-1 text-primary">
                    Dengan membuka assessment, pesantren akan dapat mengisi instrumen asesmen IPM, EDPM, dan SDM.
                    Anda dapat mengatur batas waktu (deadline) pengisian assessment (opsional).
                </p>
            </div>
        </div>
    </div>

    <div class="rounded-3 bg-white shadow-sm">
        <div class="px-6 py-5">
            <h2 class="fs-6 fw-semibold text-gray-900">Form Buka Assessment</h2>
        </div>

        <div class="px-6 py-6">
            <form method="POST" action="{{ route('admin.akreditasi.buka-assessment', $akreditasi->id) }}">
                @csrf

                <div class="space-y-5">
                    <div>
                        <label for="deadline" class="block fs-7 fw-medium text-gray-700">
                            Batas Waktu Pengisian
                        </label>
                        <p class="mt-1 fs-8 text-gray-500">Kosongkan jika tidak ada batas waktu khusus.</p>
                        <input type="date" id="deadline" name="deadline"
                               value="{{ old('deadline') }}"
                               min="{{ now()->addDay()->format('Y-m-d') }}"
                               class="mt-2 block w-100 rounded border border-gray-200 bg-light px-3 py-2 fs-7 text-gray-900 focus:border-indigo-500 focus:" />
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-3 pt-2">
                        <a href="{{ route('admin.akreditasi.index') }}"
                           class="rounded border border-gray-200 bg-white px-4 py-2 fs-7 fw-medium text-gray-600 shadow-sm">
                            Batal
                        </a>
                        <button type="submit"
                                class="d-inline-flex align-items-center gap-2 rounded btn btn-primary px-4 py-2 fs-7 fw-semibold text-white shadow-sm">
                            <svg class="w-15px h-15px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                            Buka Assessment
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    </div>

@endsection
