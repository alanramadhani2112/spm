@extends('layouts.metronic.app')

@section('title', 'Superadmin — Pengajuan Baru')
@section('pageTitle', 'Pengajuan Akreditasi Baru')

@section('content')
<x-metronic.card title="Pilih Pesantren untuk Pengajuan">
    <form method="POST"
          action="{{ route('superadmin.akreditasi.submit-pengajuan') }}"
          data-swal-confirm="true"
          data-swal-title="Ajukan akreditasi baru?"
          data-swal-text="Pengajuan akreditasi akan dibuat untuk pesantren yang dipilih."
          data-swal-icon="question"
          data-swal-confirm-button="Ya, ajukan"
          data-swal-confirm-class="btn btn-primary">
        @csrf

        <div class="mb-6">
            <x-metronic.form-input
                name="pesantren_id"
                label="Pesantren"
                type="select"
                :options="$pesantren->pluck('name', 'id')->toArray()"
                required="true"
                help="Pilih pesantren yang akan diajukan akreditasi."
                :error="$errors->first('pesantren_id')"
            />
        </div>

        <div class="d-flex gap-3">
            <button type="submit" class="btn btn-primary">
                <i class="ki-outline ki-verify fs-2 me-1"></i>Ajukan Akreditasi
            </button>
            <a href="{{ route('superadmin.akreditasi.index') }}" class="btn btn-light">Batal</a>
        </div>
    </form>
</x-metronic.card>
@endsection
