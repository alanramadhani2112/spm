@php
    use App\Models\MasterEdpmButir;
    use App\Services\ScoringService;
    use App\Models\MasterEdpmKomponen;

    $data = old('ipr', $ipr?->data ?? []);
    $butirData = $data['butirs'] ?? [];

    $iprKomponen = MasterEdpmKomponen::find(ScoringService::IPR_CONFIG['id']);
    $iprButirs = MasterEdpmButir::where('komponen_id', ScoringService::IPR_CONFIG['id'])->orderBy('id')->get();
@endphp

<div class="row g-5">
    <div class="col-12">
        <div class="mb-4 p-4 rounded bg-light-info">
            <div class="d-flex align-items-start gap-3">
                <i class="ki-outline ki-information-2 fs-4 text-info mt-1"></i>
                <div class="fs-7 text-gray-700">
                    <strong>Instrumen Pemenuhan Relatif (IPR) — 22 Butir</strong>
                    <br>Unggah dokumen pendukung untuk setiap butir IPR. Format: PDF, maksimal 5 MB per dokumen.
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card card-flush">
            <div class="card-header bg-light-success">
                <h3 class="card-title fw-bold text-success fs-6">
                    <span class="badge badge-light-success fs-8 me-2">{{ $iprKomponen?->id ?? 5 }}</span>
                    {{ $iprKomponen?->nama ?? 'IPR' }}
                </h3>
                <div class="card-toolbar">
                    <span class="badge badge-light-secondary fs-8">{{ $iprButirs->count() }} butir</span>
                </div>
            </div>
            <div class="card-body p-0">
                @foreach($iprButirs as $butir)
                    @php $existing = $butirData[$butir->id] ?? []; @endphp
                    <div class="px-6 py-4 border-bottom">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <span class="badge badge-light-dark fs-8 fw-bold flex-shrink-0">{{ $butir->kode }}</span>
                            <div class="flex-grow-1">
                                <p class="fs-7 fw-semibold text-gray-900 mb-1">{{ $butir->deskripsi }}</p>
                            </div>
                        </div>
                        <div class="row g-3 align-items-center">
                            <div class="col-md-8">
                                <input type="file" name="ipr[butirs][{{ $butir->id }}][file]"
                                       class="form-control form-control-solid"
                                       accept=".pdf">
                                @if(!empty($existing['file']))
                                    <div class="fs-8 text-success mt-2">
                                        <i class="ki-outline ki-check-squared fs-8 me-1"></i>
                                        File tersimpan: {{ basename($existing['file']) }}
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-4">
                                @if(!empty($existing['file']))
                                    <a href="{{ asset('storage/' . $existing['file']) }}" target="_blank" class="btn btn-sm btn-light">
                                        <i class="ki-outline ki-file-down fs-6 me-1"></i>Lihat
                                    </a>
                                @else
                                    <span class="fs-8 text-muted fst-italic">Belum ada dokumen</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
