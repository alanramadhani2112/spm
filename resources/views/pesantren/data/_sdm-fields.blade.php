@php $data = old('sdm', $sdm?->data ?? []); @endphp

<div class="row g-5">
    <div class="col-md-4">
        <label class="form-label required">Ustaz Tetap</label>
        <input type="number" min="0" name="sdm[ustaz_tetap]" class="form-control form-control-solid @error('sdm.ustaz_tetap') is-invalid @enderror" value="{{ $data['ustaz_tetap'] ?? '' }}" required>
        @error('sdm.ustaz_tetap')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Ustaz Tidak Tetap</label>
        <input type="number" min="0" name="sdm[ustaz_tidak_tetap]" class="form-control form-control-solid" value="{{ $data['ustaz_tidak_tetap'] ?? '' }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Tenaga Kependidikan</label>
        <input type="number" min="0" name="sdm[tenaga_kependidikan]" class="form-control form-control-solid" value="{{ $data['tenaga_kependidikan'] ?? '' }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Rasio Pengasuh : Santri</label>
        <input type="text" name="sdm[rasio_pengasuh_santri]" class="form-control form-control-solid" value="{{ $data['rasio_pengasuh_santri'] ?? '' }}" placeholder="Contoh: 1:20">
    </div>
    <div class="col-md-12">
        <label class="form-label">Catatan SDM</label>
        <textarea name="sdm[catatan_sdm]" rows="3" class="form-control form-control-solid" placeholder="Ringkasan kualifikasi dan pembinaan SDM">{{ $data['catatan_sdm'] ?? '' }}</textarea>
    </div>
</div>
