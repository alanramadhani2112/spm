@extends('layouts.metronic.app')

@section('title', 'Pengajuan Akreditasi Baru')
@section('pageTitle', 'Pengajuan Akreditasi Baru')

@section('content')
<div class="mx-auto">
    <x-metronic.card title="Konfirmasi Pengajuan">
        <p class="fs-7 text-muted mb-6">Pastikan data pesantren Anda telah lengkap sebelum mengajukan akreditasi.</p>

        <div class="rounded border border-primary bg-light-primary p-4 mb-6">
            <div class="d-flex align-items-start gap-3">
                <i class="ki-outline ki-information-5 fs-2 text-primary mt-1"></i>
                <div class="fs-7">
                    <p class="fw-medium text-primary">Sebelum mengajukan, pastikan:</p>
                    <ul class="mt-2 list-unstyled">
                        <li class="mb-1 text-primary">Profil pesantren telah diisi lengkap</li>
                        <li class="mb-1 text-primary">Unit satuan pendidikan telah ditambahkan</li>
                        <li class="mb-1 text-primary">Data IPM (Instrumen Penilaian Mutu) telah diisi</li>
                        <li class="mb-1 text-primary">Data SDM (Sumber Daya Manusia) telah diisi</li>
                        <li class="text-primary">Data EDPM/IPR (Evaluasi Diri) telah diisi</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="rounded border border-warning bg-light-warning p-4 mb-6">
            <div class="d-flex align-items-start gap-3">
                <i class="ki-outline ki-information-3 fs-2 text-warning mt-1"></i>
                <div class="fs-7">
                    <p class="fw-medium text-warning">Perhatian:</p>
                    <p class="mt-1 text-warning">Setelah pengajuan dikirim, data profil akan dikunci dan tidak dapat diubah selama proses akreditasi berlangsung.</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('pesantren.akreditasi.submit-pengajuan') }}">
            @csrf

            <div class="d-flex align-items-center gap-3">
                <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                    <i class="ki-outline ki-sms fs-5"></i>
                    Kirim Pengajuan
                </button>
                <a href="{{ route('pesantren.akreditasi.index') }}" class="btn btn-light btn-sm">
                    Batal
                </a>
            </div>
        </form>
    </x-metronic.card>
</div>
@endsection
