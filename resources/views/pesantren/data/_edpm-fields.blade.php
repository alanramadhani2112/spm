@php $data = old('edpm', $edpm?->data ?? []); @endphp

<div class="row g-5">
    <div class="col-md-12">
        <label class="form-label required">Ringkasan Evaluasi Diri</label>
        <textarea name="edpm[self_assessment]" rows="5" class="form-control form-control-solid @error('edpm.self_assessment') is-invalid @enderror" required placeholder="Jelaskan hasil evaluasi diri pesantren dan kesiapan mengikuti akreditasi">{{ $data['self_assessment'] ?? '' }}</textarea>
        @error('edpm.self_assessment')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Kesiapan Dokumen</label>
        <select name="edpm[kesiapan_dokumen]" class="form-select form-select-solid">
            @php $kesiapan = $data['kesiapan_dokumen'] ?? ''; @endphp
            <option value="">Pilih kesiapan</option>
            <option value="lengkap" @selected($kesiapan === 'lengkap')>Lengkap</option>
            <option value="sebagian" @selected($kesiapan === 'sebagian')>Sebagian</option>
            <option value="perlu_perbaikan" @selected($kesiapan === 'perlu_perbaikan')>Perlu Perbaikan</option>
        </select>
    </div>
    <div class="col-md-12">
        <label class="form-label">Catatan IPR</label>
        <textarea name="edpm[catatan_ipr]" rows="3" class="form-control form-control-solid" placeholder="Catatan tambahan untuk evaluasi diri/IPR">{{ $data['catatan_ipr'] ?? '' }}</textarea>
    </div>
</div>
