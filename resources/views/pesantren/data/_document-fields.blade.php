@php
    $documents = [
        'dok_profil' => 'Dokumen Profil Pesantren',
        'dok_nsp' => 'Sertifikat NSP',
        'dok_renstra' => 'Renstra',
        'dok_rk_anggaran' => 'RK Anggaran',
        'dok_kurikulum' => 'Kurikulum',
        'dok_silabus_rpp' => 'Silabus/RPP',
        'dok_kepengasuhan' => 'Kepengasuhan',
        'dok_peraturan_kepegawaian' => 'Peraturan Kepegawaian',
        'dok_sarpras' => 'Sarpras',
        'dok_laporan_tahunan' => 'Laporan Tahunan',
        'dok_sop' => 'SOP',
    ];
@endphp

<div class="row g-5">
    @foreach($documents as $field => $label)
        <div class="col-md-6">
            <x-metronic.form-input name="{{ $field }}" label="{{ $label }}" type="file" help="PDF maksimal 5MB" />
            @if($pesantren?->{$field})
                <div class="fs-8 text-muted mt-n5 mb-6">File tersimpan: {{ basename($pesantren->{$field}) }}</div>
            @endif
        </div>
    @endforeach
</div>
