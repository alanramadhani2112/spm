@extends('layouts.metronic.app')

@section('title', 'Review Banding')
@section('pageTitle', 'Review Banding')

@section('content')
@php
    use App\Models\Akreditasi;
    $bandings = $akreditasi->bandings()->where('status', 'pending')->orderBy('created_at', 'desc')->get();
@endphp

    <div class="mb-6 flex flex-wrap align-items-center justify-content-between gap-4">
        <div>
            <h2 class="fs-5 fw-semibold text-gray-900">Review Permohonan Banding</h2>
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
            <p class="fs-8 fw-medium text-uppercase ls-1r text-gray-500">NA1</p>
            <p class="mt-1 fs-4 fw-semibold text-gray-900">{{ number_format($akreditasi->na1 ?? 0, 2) }}</p>
        </div>
        <div class="rounded-3 bg-white shadow-sm p-5">
            <p class="fs-8 fw-medium text-uppercase ls-1r text-gray-500">NA2</p>
            <p class="mt-1 fs-4 fw-semibold text-gray-900">{{ number_format($akreditasi->na2 ?? 0, 2) }}</p>
        </div>
        <div class="rounded-3 bg-white shadow-sm p-5">
            <p class="fs-8 fw-medium text-uppercase ls-1r text-gray-500">NK</p>
            <p class="mt-1 fs-4 fw-semibold text-primary">{{ number_format($akreditasi->nk ?? 0, 2) }}</p>
        </div>
        <div class="rounded-3 bg-white shadow-sm p-5">
            <p class="fs-8 fw-medium text-uppercase ls-1r text-gray-500">NV (Final)</p>
            <p class="mt-1 fs-4 fw-semibold text-success">{{ number_format($akreditasi->nv ?? 0, 2) }}</p>
        </div>
    </div>

    {{-- Banding Entries --}}
    @if($bandings->isEmpty())
        <div class="rounded-3 border border-dashed border-gray-300 bg-white p-12 text-center">
            <svg class="mx-auto w-45px h-45px text-gray-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
            </svg>
            <h3 class="mt-4 fs-7 fw-semibold text-gray-900">Tidak ada banding pending</h3>
            <p class="mt-1 fs-7 text-muted">Semua permohonan banding telah diproses.</p>
        </div>
    @else
        @foreach($bandings as $banding)
            <div class="rounded-3 bg-white shadow-sm mb-6">
                <div class="px-6 py-5">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="fs-6 fw-semibold text-gray-900">Permohonan Banding</h2>
                            <p class="mt-1 fs-8 text-gray-500">
                                Diajukan: {{ $banding->created_at->format('d M Y, H:i') }}
                            </p>
                        </div>
                        <span class="d-inline-flex align-items-center rounded-pill bg-light-warning px-2 py-1 fs-8 fw-medium text-warning">
                            Pending
                        </span>
                    </div>
                </div>

                <div class="px-6 py-5">
                    <div class="mb-6 rounded border border-gray-100 bg-light p-4">
                        <p class="fs-8 fw-medium text-uppercase ls-1r text-gray-500 mb-1">Alasan Banding</p>
                        <p class="fs-7 text-gray-900 whitespace-pre-wrap">{{ $banding->alasan ?? $banding->reason ?? '—' }}</p>
                    </div>

                    <div class="row gap-6">
                        {{-- Terima Banding --}}
                        <div class="rounded border border-green-100 bg-light-success p-4">
                            <h3 class="fs-7 fw-semibold text-success mb-3">Terima Banding</h3>
                            <form method="POST" action="{{ route('admin.banding.terima', $banding->id) }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="response_terima_{{ $banding->id }}" class="block fs-8 fw-medium text-success">
                                        Respon (opsional)
                                    </label>
                                    <textarea id="response_terima_{{ $banding->id }}" name="response" rows="2"
                                              class="mt-1 block w-100 rounded border border-green-200 bg-white px-3 py-2 fs-7 text-gray-900 placeholder:text-gray-500 focus:border-green-500 focus:"
                                              placeholder="Catatan persetujuan banding...">{{ old('response') }}</textarea>
                                </div>
                                <button type="submit"
                                        class="d-inline-flex w-100 align-items-center justify-content-center gap-2 rounded btn btn-success px-4 py-2 fs-7 fw-semibold text-white shadow-sm">
                                    <svg class="w-15px h-15px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                    Terima Banding
                                </button>
                            </form>
                        </div>

                        {{-- Tolak Banding --}}
                        <div class="rounded border border-red-100 bg-light-danger p-4">
                            <h3 class="fs-7 fw-semibold text-danger mb-3">Tolak Banding</h3>
                            <form method="POST" action="{{ route('admin.banding.tolak', $banding->id) }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="response_tolak_{{ $banding->id }}" class="block fs-8 fw-medium text-danger">
                                        Alasan Penolakan <span class="text-danger">*</span>
                                    </label>
                                    <textarea id="response_tolak_{{ $banding->id }}" name="response" rows="2" required
                                              class="mt-1 block w-100 rounded border border-red-200 bg-white px-3 py-2 fs-7 text-gray-900 placeholder:text-gray-500 focus:border-red-500 focus:"
                                              placeholder="Jelaskan alasan penolakan banding...">{{ old('response') }}</textarea>
                                    <p class="mt-1 fs-8 text-red-400">Minimal 5 karakter.</p>
                                </div>
                                <button type="submit"
                                        class="d-inline-flex w-100 align-items-center justify-content-center gap-2 rounded btn btn-danger px-4 py-2 fs-7 fw-semibold text-white shadow-sm">
                                    <svg class="w-15px h-15px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                    Tolak Banding
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
    </div>

@endsection
