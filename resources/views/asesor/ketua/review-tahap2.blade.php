@extends('layouts.metronic.app')

@section('title', 'Review Tahap 2 — Ketua Asesor')
@section('pageTitle', 'Review Dokumen Tahap 2')

@section('content')
@php
    use App\Models\Akreditasi;
    $approveRouteName = $approveRouteName ?? 'asesor.ketua.nyatakan-layak-visitasi';
    $correctionRouteName = $correctionRouteName ?? 'asesor.ketua.minta-perbaikan-tahap2';
    $backRouteName = $backRouteName ?? 'asesor.ketua.index';
@endphp

<div class="d-grid gap-6">

    @if(session('success'))
        <x-metronic.alert type="success" :message="session('success')" />
    @endif

    @if(session('error'))
        <x-metronic.alert type="danger" :message="session('error')" />
    @endif

    <x-metronic.card title="Detail Akreditasi">
        <dl class="row gap-4">
            <div>
                <dt class="fs-8 fw-medium text-muted">UUID</dt>
                <dd class="mt-1 fs-7 text-gray-900">{{ $akreditasi->uuid }}</dd>
            </div>
            <div>
                <dt class="fs-8 fw-medium text-muted">Status</dt>
                <dd class="mt-1">
                    <span class="badge badge-light-warning">{{ $akreditasi->getStatusLabel() }}</span>
                </dd>
            </div>
            <div>
                <dt class="fs-8 fw-medium text-muted">Nomor SK</dt>
                <dd class="mt-1 fs-7 text-gray-900">{{ $akreditasi->nomor_sk ?? '—' }}</dd>
            </div>
            <div>
                <dt class="fs-8 fw-medium text-muted">Tanggal Pengajuan</dt>
                <dd class="mt-1 fs-7 text-gray-900">{{ $akreditasi->created_at->format('d M Y, H:i') }}</dd>
            </div>
            @if($akreditasi->catatan)
            <div>
                <dt class="fs-8 fw-medium text-muted">Catatan</dt>
                <dd class="mt-1 fs-7 text-gray-900">{{ $akreditasi->catatan }}</dd>
            </div>
            @endif
        </dl>
    </x-metronic.card>

    <x-metronic.card title="Tindakan Review">
        <p class="text-muted fs-7">Anda dapat menyetujui kelayakan visitasi atau meminta perbaikan dokumen tertentu.</p>

        <div class="d-grid gap-8 mt-6">
            <div class="rounded border border-success bg-light-success p-5">
                <h3 class="fs-6 fw-bold text-gray-900">Nyatakan Layak Visitasi</h3>
                <p class="mt-1 fs-7 text-gray-700">Akreditasi akan dilanjutkan ke tahap penjadwalan visitasi.</p>
                <form method="POST" action="{{ route($approveRouteName, $akreditasi->id) }}" class="mt-4">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Apakah Anda yakin ingin menyatakan akreditasi ini layak visitasi?')"
                            class="btn btn-success fw-bold">
                        <i class="ki-outline ki-check-squared fs-5 me-2"></i>
                        Setujui & Lanjutkan
                    </button>
                </form>
            </div>

            <div class="rounded border border-warning bg-light-warning p-5">
                <h3 class="fs-6 fw-bold text-gray-900">Minta Perbaikan Tahap 2</h3>
                <p class="mt-1 fs-7 text-gray-700">Minta pesantren memperbaiki dokumen yang belum lengkap atau tidak sesuai.</p>
                <form method="POST" action="{{ route($correctionRouteName, $akreditasi->id) }}" class="mt-4 d-grid gap-4">
                    @csrf

                    <fieldset>
                        <legend class="fs-7 fw-semibold text-gray-900">Bagian yang perlu diperbaiki <span class="text-danger">*</span></legend>
                        <div class="mt-3 d-grid gap-2">
                            <label class="d-flex align-items-center gap-3">
                                <input type="checkbox" name="sections[]" value="ipm"
                                       class="form-check-input"
                                       @checked(is_array(old('sections')) && in_array('ipm', old('sections')))>
                                <span class="fs-7 text-gray-700">IPM (Instrumen Penilaian Mutu)</span>
                            </label>
                            <label class="d-flex align-items-center gap-3">
                                <input type="checkbox" name="sections[]" value="sdm"
                                       class="form-check-input"
                                       @checked(is_array(old('sections')) && in_array('sdm', old('sections')))>
                                <span class="fs-7 text-gray-700">SDM (Sumber Daya Manusia)</span>
                            </label>
                            <label class="d-flex align-items-center gap-3">
                                <input type="checkbox" name="sections[]" value="edpm"
                                       class="form-check-input"
                                       @checked(is_array(old('sections')) && in_array('edpm', old('sections')))>
                                <span class="fs-7 text-gray-700">EDPM/IPR (Evaluasi Diri Pesantren)</span>
                            </label>
                        </div>
                        @error('sections')
                            <p class="mt-2 fs-7 text-danger">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    <div>
                        <label for="reason" class="form-label">Alasan / Catatan Perbaikan</label>
                        <textarea id="reason" name="reason" rows="3"
                                  class="form-control form-control-solid">{{ old('reason') }}</textarea>
                        @error('reason')
                            <p class="mt-2 fs-7 text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                            class="btn btn-warning fw-bold">
                        <i class="ki-outline ki-notepad-edit fs-5 me-2"></i>
                        Kirim Permintaan Perbaikan
                    </button>
                </form>
            </div>
        </div>
    </x-metronic.card>

    <a href="{{ route($backRouteName) }}"
       class="btn btn-light">
        <i class="ki-outline ki-arrow-left fs-5 me-2"></i>
        Kembali ke Dashboard
    </a>
</div>
@endsection
