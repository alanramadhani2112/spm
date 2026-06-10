@extends('layouts.metronic.app')

@section('title', 'Pengaturan Dokumen')
@section('pageTitle', 'Pengaturan Dokumen')

@section('content')
@include('superadmin.settings._nav')

<x-metronic.card title="Pengaturan Dokumen">
    <div class="d-grid gap-6">
        {{-- kartu_kendali_wajib_before --}}
        <div class="rounded border border-gray-200 p-4">
            <form method="POST" action="{{ route('superadmin.settings.update') }}" class="d-grid gap-3" data-swal-confirm="true" data-swal-title="Simpan perubahan setting?" data-swal-text="Setting Super Admin ini akan diperbarui dan tercatat di audit log." data-swal-icon="warning" data-swal-confirm-button="Ya, simpan" data-swal-confirm-class="btn btn-primary">
                @csrf
                <input type="hidden" name="key" value="kartu_kendali_wajib_before">
                <label class="fs-6 fw-bold text-gray-800">Kartu Kendali Wajib Diunggah Sebelum</label>
                <div class="d-grid gap-2">
                    @php
                        $kkValue = old('value', $settings['kartu_kendali_wajib_before']);
                        $kkOptions = [
                            'before_admin_validation' => 'Sebelum Validasi Admin',
                            'before_submit' => 'Sebelum Submit',
                            'before_visitasi' => 'Sebelum Visitasi',
                        ];
                    @endphp
                    @foreach($kkOptions as $val => $label)
                        <label class="d-flex align-items-center gap-2 cursor-pointer fs-6 text-gray-700">
                            <input type="radio" name="value" value="{{ $val }}"
                                   class="text-primary border-gray-300"
                                   {{ $kkValue === $val ? 'checked' : '' }} required>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                <x-metronic.form-input name="reason" placeholder="Alasan perubahan..." required="true" />
                <button type="submit" class="btn btn-primary px-4 py-2 fs-6 fw-bold">
                    Simpan
                </button>
            </form>
        </div>

        {{-- laporan_wajib_before --}}
        <div class="rounded border border-gray-200 p-4">
            <form method="POST" action="{{ route('superadmin.settings.update') }}" class="d-grid gap-3" data-swal-confirm="true" data-swal-title="Simpan perubahan setting?" data-swal-text="Setting Super Admin ini akan diperbarui dan tercatat di audit log." data-swal-icon="warning" data-swal-confirm-button="Ya, simpan" data-swal-confirm-class="btn btn-primary">
                @csrf
                <input type="hidden" name="key" value="laporan_wajib_before">
                <label class="fs-6 fw-bold text-gray-800">Laporan Wajib Diunggah Sebelum</label>
                <div class="d-grid gap-2">
                    @php
                        $laporanValue = old('value', $settings['laporan_wajib_before']);
                        $laporanOptions = [
                            'before_admin_validation' => 'Sebelum Validasi Admin',
                            'before_submit' => 'Sebelum Submit',
                            'before_visitasi' => 'Sebelum Visitasi',
                        ];
                    @endphp
                    @foreach($laporanOptions as $val => $label)
                        <label class="d-flex align-items-center gap-2 cursor-pointer fs-6 text-gray-700">
                            <input type="radio" name="value" value="{{ $val }}"
                                   class="text-primary border-gray-300"
                                   {{ $laporanValue === $val ? 'checked' : '' }} required>
                            {{ $label }}
                        </label>
                    @endforeach
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
