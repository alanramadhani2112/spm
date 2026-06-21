@php
    use App\Services\PesantrenService;
    $data = old('ipm', $ipm?->data ?? []);
    $butirs = PesantrenService::IPM_BUTIRS;
@endphp

<div class="row g-5">
    <div class="col-12 mb-2">
        <h5 class="fw-bold text-gray-800">Instrumen Penilaian Mutlak (IPM)</h5>
        <div class="text-muted fs-7">
            Pesantren <strong>wajib</strong> memenuhi 4 butir pernyataan di bawah ini untuk dapat melanjutkan proses akreditasi.
        </div>
    </div>

    @foreach($butirs as $key => $label)
    <div class="col-md-6">
        <label class="form-label required">{{ $label }}</label>
        <div class="d-flex gap-4">
            <label class="form-check form-check-custom form-check-solid">
                <input class="form-check-input" type="radio" name="ipm[{{ $key }}]" value="sesuai" @checked(($data[$key] ?? '') === 'sesuai') required>
                <span class="form-check-label fw-semibold">Sesuai</span>
            </label>
            <label class="form-check form-check-custom form-check-solid">
                <input class="form-check-input" type="radio" name="ipm[{{ $key }}]" value="tidak_sesuai" @checked(($data[$key] ?? '') === 'tidak_sesuai') required>
                <span class="form-check-label fw-semibold text-danger">Tidak Sesuai</span>
            </label>
        </div>
    </div>
    @endforeach

    <div class="col-12 mt-8 mb-2">
        <h5 class="fw-bold text-gray-800">Data IPM Lainnya</h5>
    </div>

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
