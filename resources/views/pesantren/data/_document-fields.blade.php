@php
    $dokUtama = [
        'dok_profil' => 'Dokumen Profil Pesantren',
        'dok_nsp' => 'Sertifikat NSP',
        'dok_renstra' => 'Renstra',
        'dok_rk_anggaran' => 'RK Anggaran',
        'dok_kurikulum' => 'Kurikulum',
        'dok_silabus_rpp' => 'Silabus/RPP',
        'dok_kepengasuhan' => 'Kepengasuhan',
    ];
    $dokSekunder = [
        'dok_peraturan_kepegawaian' => 'Peraturan Kepegawaian',
        'dok_sarpras' => 'Sarpras',
        'dok_laporan_tahunan' => 'Laporan Tahunan',
        'dok_sop' => 'SOP',
    ];
@endphp

<h5 class="fw-bold text-gray-800 mb-4">Dokumen Utama (7 Dokumen Wajib)</h5>
<div class="row g-5">
    @foreach($dokUtama as $field => $label)
        <div class="col-md-6">
            <x-metronic.form-input name="{{ $field }}" label="{{ $label }}" type="file" help="PDF maksimal 5MB" />
            @if($pesantren?->{$field})
                <div class="fs-8 text-muted mt-n5 mb-6">File tersimpan: {{ basename($pesantren->{$field}) }}</div>
            @endif
        </div>
    @endforeach
    <div class="col-md-6">
        <x-metronic.form-input name="file_lk_iapm" label="File LK IAPM" type="file" help="PDF maksimal 5MB" />
        @if($pesantren?->file_lk_iapm)
            <div class="fs-8 text-muted mt-n5 mb-6">File tersimpan: {{ basename($pesantren->file_lk_iapm) }}</div>
        @endif
    </div>
</div>

<h5 class="fw-bold text-gray-800 mb-4 mt-6">Dokumen Sekunder (4 Dokumen Pendukung)</h5>
<div class="row g-5">
    @foreach($dokSekunder as $field => $label)
        <div class="col-md-6">
            <x-metronic.form-input name="{{ $field }}" label="{{ $label }}" type="file" help="PDF maksimal 5MB" />
            @if($pesantren?->{$field})
                <div class="fs-8 text-muted mt-n5 mb-6">File tersimpan: {{ basename($pesantren->{$field}) }}</div>
            @endif
        </div>
    @endforeach
</div>
