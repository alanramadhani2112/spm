@extends('layouts.metronic.app')

@section('title', 'Pengaturan Notifikasi')
@section('pageTitle', 'Pengaturan Notifikasi')

@section('content')
@include('superadmin.settings._nav')

<x-metronic.card title="Pengaturan Notifikasi">
    <div class="d-grid gap-6">
        {{-- superadmin_receives_admin_notif --}}
        <div class="rounded border border-gray-200 p-4">
            <form method="POST" action="{{ route('superadmin.settings.update') }}" class="d-grid gap-3" data-swal-confirm="true" data-swal-title="Simpan perubahan setting?" data-swal-text="Setting Super Admin ini akan diperbarui dan tercatat di audit log." data-swal-icon="warning" data-swal-confirm-button="Ya, simpan" data-swal-confirm-class="btn btn-primary">
                @csrf
                <input type="hidden" name="key" value="superadmin_receives_admin_notif">
                <label class="fs-6 fw-bold text-gray-800">Super Admin Menerima Notifikasi Admin</label>
                <p class="fs-7 text-muted">Super Admin akan menerima salinan semua notifikasi yang dikirim ke admin.</p>
                <div class="d-flex align-items-center gap-4">
                    <label class="d-flex align-items-center gap-2 cursor-pointer fs-6 text-gray-700">
                        <input type="radio" name="value" value="1"
                               class="text-primary border-gray-300"
                               {{ old('value', $settings['superadmin_receives_admin_notif']) == true ? 'checked' : '' }} required>
                        Ya
                    </label>
                    <label class="d-flex align-items-center gap-2 cursor-pointer fs-6 text-gray-700">
                        <input type="radio" name="value" value="0"
                               class="text-primary border-gray-300"
                               {{ old('value', $settings['superadmin_receives_admin_notif']) == false ? 'checked' : '' }} required>
                        Tidak
                    </label>
                </div>
                <x-metronic.form-input name="reason" placeholder="Alasan perubahan..." required="true" />
                <button type="submit" class="btn btn-primary px-4 py-2 fs-6 fw-bold">
                    Simpan
                </button>
            </form>
        </div>

        {{-- reminder_days --}}
        <div class="rounded border border-gray-200 p-4">
            <form method="POST" action="{{ route('superadmin.settings.update') }}" class="d-grid gap-3" data-swal-confirm="true" data-swal-title="Simpan perubahan setting?" data-swal-text="Setting Super Admin ini akan diperbarui dan tercatat di audit log." data-swal-icon="warning" data-swal-confirm-button="Ya, simpan" data-swal-confirm-class="btn btn-primary">
                @csrf
                <input type="hidden" name="key" value="reminder_days">
                <label class="fs-6 fw-bold text-gray-800">Jumlah Hari Pengingat</label>
                <p class="fs-7 text-muted">Notifikasi pengingat dikirim H-berapa sebelum deadline tercapai.</p>
                <div class="d-flex align-items-center gap-3">
                    <x-metronic.form-input name="value" type="number" :value="old('value', $settings['reminder_days'])" required="true" />
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
