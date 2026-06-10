@extends('layouts.metronic.app')

@section('title', 'Review Awal Pengajuan')
@section('pageTitle', 'Review Awal Pengajuan')

@section('content')
@php
    use App\Models\Akreditasi;
    $pesantren = $akreditasi->user?->pesantren;
@endphp

    <div class="mb-6 flex flex-wrap align-items-center justify-content-between gap-4">
        <div>
            <h2 class="fs-5 fw-semibold text-gray-900">Review Pengajuan Awal</h2>
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

    {{-- Profil Pesantren --}}
    <div class="rounded-3 bg-white shadow-sm mb-6">
        <div class="px-6 py-5">
            <h2 class="fs-6 fw-semibold text-gray-900">Profil Pesantren</h2>
            <p class="mt-1 fs-7 text-muted">Data berikut bersifat read-only untuk review.</p>
        </div>

        <div class="px-6 py-5">
            @if(!$pesantren)
                <div class="rounded border border-yellow-100 bg-light-warning p-4">
                    <p class="fs-7">Data pesantren belum tersedia.</p>
                </div>
            @else
                <dl class="row gap-x-6 gap-y-4">
                    <div>
                        <dt class="fs-8 fw-medium text-uppercase ls-1r text-gray-500">Nama Pesantren</dt>
                        <dd class="mt-1 fs-7 text-gray-900">{{ $pesantren->nama_pesantren ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="fs-8 fw-medium text-uppercase ls-1r text-gray-500">NSPP</dt>
                        <dd class="mt-1 fs-7 text-gray-900">{{ $pesantren->nspp ?? '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="fs-8 fw-medium text-uppercase ls-1r text-gray-500">Alamat</dt>
                        <dd class="mt-1 fs-7 text-gray-900">{{ $pesantren->alamat ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="fs-8 fw-medium text-uppercase ls-1r text-gray-500">Provinsi</dt>
                        <dd class="mt-1 fs-7 text-gray-900">{{ $pesantren->provinsi ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="fs-8 fw-medium text-uppercase ls-1r text-gray-500">Kota / Kabupaten</dt>
                        <dd class="mt-1 fs-7 text-gray-900">{{ $pesantren->kota_kabupaten ?? $pesantren->kabupaten ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="fs-8 fw-medium text-uppercase ls-1r text-gray-500">Kecamatan</dt>
                        <dd class="mt-1 fs-7 text-gray-900">{{ $pesantren->kecamatan ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="fs-8 fw-medium text-uppercase ls-1r text-gray-500">Kelurahan</dt>
                        <dd class="mt-1 fs-7 text-gray-900">{{ $pesantren->kelurahan ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="fs-8 fw-medium text-uppercase ls-1r text-gray-500">Telepon</dt>
                        <dd class="mt-1 fs-7 text-gray-900">{{ $pesantren->telp_pesantren ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="fs-8 fw-medium text-uppercase ls-1r text-gray-500">HP / WA</dt>
                        <dd class="mt-1 fs-7 text-gray-900">{{ $pesantren->hp_wa ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="fs-8 fw-medium text-uppercase ls-1r text-gray-500">Email</dt>
                        <dd class="mt-1 fs-7 text-gray-900">{{ $pesantren->email_pesantren ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="fs-8 fw-medium text-uppercase ls-1r text-gray-500">Tahun Pendirian</dt>
                        <dd class="mt-1 fs-7 text-gray-900">{{ $pesantren->tahun_pendirian ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="fs-8 fw-medium text-uppercase ls-1r text-gray-500">Nama Mudir</dt>
                        <dd class="mt-1 fs-7 text-gray-900">{{ $pesantren->nama_mudir ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="fs-8 fw-medium text-uppercase ls-1r text-gray-500">Persyarikatan</dt>
                        <dd class="mt-1 fs-7 text-gray-900">{{ $pesantren->persyarikatan ?? '—' }}</dd>
                    </div>
                    @if($pesantren->layanan_satuan_pendidikan)
                        <div class="sm:col-span-2">
                            <dt class="fs-8 fw-medium text-uppercase ls-1r text-gray-500">Layanan Satuan Pendidikan</dt>
                            <dd class="mt-1 fs-7 text-gray-900">
                                {{ is_array($pesantren->layanan_satuan_pendidikan) ? implode(', ', $pesantren->layanan_satuan_pendidikan) : $pesantren->layanan_satuan_pendidikan }}
                            </dd>
                        </div>
                    @endif
                    @if($pesantren->visi)
                        <div class="sm:col-span-2">
                            <dt class="fs-8 fw-medium text-uppercase ls-1r text-gray-500">Visi</dt>
                            <dd class="mt-1 fs-7 text-gray-900">{{ $pesantren->visi }}</dd>
                        </div>
                    @endif
                    @if($pesantren->misi)
                        <div class="sm:col-span-2">
                            <dt class="fs-8 fw-medium text-uppercase ls-1r text-gray-500">Misi</dt>
                            <dd class="mt-1 fs-7 text-gray-900">{{ $pesantren->misi }}</dd>
                        </div>
                    @endif
                </dl>
            @endif
        </div>
    </div>

    {{-- Aksi Review --}}
    <div class="row gap-6">
        {{-- Terima Pengajuan --}}
        <div class="rounded-3 bg-white shadow-sm">
            <div class="px-6 py-5">
                <h2 class="fs-6 fw-semibold text-success">Terima Pengajuan</h2>
                <p class="mt-1 fs-7 text-muted">Terima pengajuan dan lanjutkan ke tahap assessment.</p>
            </div>
            <div class="px-6 py-5">
                <form method="POST" action="{{ route('admin.akreditasi.terima-pengajuan', $akreditasi->id) }}">
                    @csrf
                    <div class="mb-4">
                        <label for="catatan_terima" class="block fs-7 fw-medium text-gray-700">
                            Catatan (opsional)
                        </label>
                        <textarea id="catatan_terima" name="catatan" rows="3"
                                  class="mt-1 block w-100 rounded border border-gray-200 bg-light px-3 py-2 fs-7 text-gray-900 placeholder:text-gray-500 focus:border-green-500 focus:"
                                  placeholder="Catatan tambahan untuk pesantren...">{{ old('catatan') }}</textarea>
                    </div>
                    <button type="submit"
                            class="d-inline-flex w-100 align-items-center justify-content-center gap-2 rounded btn btn-success px-4 py-2 fs-7 fw-semibold text-white shadow-sm">
                        <svg class="w-15px h-15px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        Terima Pengajuan
                    </button>
                </form>
            </div>
        </div>

        {{-- Tolak Pengajuan --}}
        <div class="rounded-3 bg-white shadow-sm">
            <div class="px-6 py-5">
                <h2 class="fs-6 fw-semibold text-danger">Tolak Pengajuan</h2>
                <p class="mt-1 fs-7 text-muted">Tolak pengajuan karena data tidak memenuhi syarat.</p>
            </div>
            <div class="px-6 py-5">
                <form method="POST" action="{{ route('admin.akreditasi.tolak-pengajuan', $akreditasi->id) }}">
                    @csrf
                    <div class="mb-4">
                        <label for="reason" class="block fs-7 fw-medium text-gray-700">
                            Alasan Penolakan <span class="text-danger">*</span>
                        </label>
                        <textarea id="reason" name="reason" rows="3" required
                                  class="mt-1 block w-100 rounded border border-gray-200 bg-light px-3 py-2 fs-7 text-gray-900 placeholder:text-gray-500 focus:border-red-500 focus:"
                                  placeholder="Jelaskan alasan penolakan...">{{ old('reason') }}</textarea>
                        <p class="mt-1 fs-8 text-gray-500">Minimal 5 karakter.</p>
                    </div>
                    <button type="submit"
                            class="d-inline-flex w-100 align-items-center justify-content-center gap-2 rounded btn btn-danger px-4 py-2 fs-7 fw-semibold text-white shadow-sm">
                        <svg class="w-15px h-15px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                        Tolak Pengajuan
                    </button>
                </form>
            </div>
        </div>
    </div>
    </div>

@endsection
