@php
    use App\Models\MasterEdpmKomponen;
    use App\Models\MasterEdpmButir;
    $data = old('edpm', $edpm?->data ?? []);
    $butirData = $data['butirs'] ?? [];
    $komponens = MasterEdpmKomponen::with('butirs')->orderBy('id')->get();
@endphp

<div class="row g-5">
    {{-- Header info --}}
    <div class="col-12">
        <div class="mb-4 p-4 rounded bg-light-info">
            <div class="d-flex align-items-start gap-3">
                <i class="ki-outline ki-information-2 fs-4 text-info mt-1"></i>
                <div class="fs-7 text-gray-700">
                    <strong>Evaluasi Diri Per-Butir (EDPM)</strong> — Isi penilaian mandiri dan link bukti dokumen untuk setiap butir IAPM.
                    <br>Skala: <strong>Sesuai</strong> / <strong>Perlu Perbaikan</strong> / <strong>Belum</strong>.
                </div>
            </div>
        </div>
    </div>

    {{-- Ringkasan global --}}
    <div class="col-md-12">
        <label class="form-label">Ringkasan Evaluasi Diri Umum</label>
        <textarea name="edpm[self_assessment]" rows="3" class="form-control form-control-solid" placeholder="Ringkasan umum kesiapan pesantren">{{ $data['self_assessment'] ?? '' }}</textarea>
    </div>

    {{-- Per-komponen --}}
    @foreach($komponens as $k)
        <div class="col-12">
            <div class="card card-flush">
                <div class="card-header bg-light-primary">
                    <h3 class="card-title fw-bold text-primary fs-6">
                        <span class="badge badge-light-primary fs-8 me-2">{{ $k->id }}</span>
                        {{ $k->nama ?? 'Komponen ' . $k->id }}
                    </h3>
                    <div class="card-toolbar">
                        <span class="badge badge-light-secondary fs-8">{{ $k->butirs->count() }} butir</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @php
                        $grouped = $k->butirs->groupBy('sub_komponen');
                    @endphp

                    @foreach($grouped as $subKode => $butirs)
                        @php
                            $subLabel = $butirs->first()?->subKomponen?->nama ?? ($subKode ?: 'Tanpa Sub Komponen');
                        @endphp
                        <div class="px-6 py-3 bg-light border-bottom">
                            <span class="fs-8 fw-bold text-gray-600 text-uppercase">{{ $subLabel }}</span>
                        </div>

                        @foreach($butirs as $butir)
                            @php $existing = $butirData[$butir->id] ?? []; @endphp
                            <div class="px-6 py-4 border-bottom {{ $loop->last ? '' : '' }}">
                                <div class="d-flex align-items-start gap-3 mb-3">
                                    <span class="badge badge-light-dark fs-8 fw-bold flex-shrink-0">{{ $butir->kode }}</span>
                                    <div class="flex-grow-1">
                                        <p class="fs-7 fw-semibold text-gray-900 mb-1">{{ $butir->deskripsi }}</p>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-7">
                                        <label class="form-label fs-8 fw-medium">Penilaian Mandiri</label>
                                        <select name="edpm[butirs][{{ $butir->id }}][self_assessment]" class="form-select form-select-sm form-select-solid">
                                            <option value="">— Pilih —</option>
                                            <option value="sesuai" @selected(($existing['self_assessment'] ?? '') === 'sesuai')>Sesuai</option>
                                            <option value="perlu_perbaikan" @selected(($existing['self_assessment'] ?? '') === 'perlu_perbaikan')>Perlu Perbaikan</option>
                                            <option value="belum" @selected(($existing['self_assessment'] ?? '') === 'belum')>Belum</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label fs-8 fw-medium">Link Bukti Dokumen</label>
                                        <input type="url" name="edpm[butirs][{{ $butir->id }}][bukti_link]"
                                               class="form-control form-control-sm form-control-solid"
                                               placeholder="https://..."
                                               value="{{ $existing['bukti_link'] ?? '' }}">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>
