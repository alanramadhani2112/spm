@php $data = old('ipm', $ipm?->data ?? []); @endphp

<div class="row g-5">
    <div class="col-md-4">
        <label class="form-label required">Santri Mukim</label>
        <input type="number" min="0" name="ipm[santri_mukim]" class="form-control form-control-solid @error('ipm.santri_mukim') is-invalid @enderror" value="{{ $data['santri_mukim'] ?? '' }}" required>
        @error('ipm.santri_mukim')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Santri Non Mukim</label>
        <input type="number" min="0" name="ipm[santri_non_mukim]" class="form-control form-control-solid" value="{{ $data['santri_non_mukim'] ?? '' }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Jumlah Rombongan Belajar</label>
        <input type="number" min="0" name="ipm[jumlah_rombongan_belajar]" class="form-control form-control-solid" value="{{ $data['jumlah_rombongan_belajar'] ?? '' }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Kurikulum Utama</label>
        <input type="text" name="ipm[kurikulum_utama]" class="form-control form-control-solid" value="{{ $data['kurikulum_utama'] ?? '' }}" placeholder="Contoh: Kurikulum Pesantren + Kemenag">
    </div>
    <div class="col-md-12">
        <label class="form-label">Catatan Mutu</label>
        <textarea name="ipm[catatan_mutu]" rows="3" class="form-control form-control-solid" placeholder="Ringkasan capaian mutu pendidikan">{{ $data['catatan_mutu'] ?? '' }}</textarea>
    </div>
</div>
