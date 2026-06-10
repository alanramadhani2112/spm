<div class="card card-flush bg-light-warning border border-warning border-dashed mb-6">
    <div class="card-body p-5">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-4">
            <div class="d-flex align-items-center gap-3">
                <span class="symbol symbol-35px">
                    <span class="symbol-label bg-warning">
                        <i class="ki-outline ki-shield-tick fs-3 text-white"></i>
                    </span>
                </span>
                <div>
                    <span class="badge badge-light-warning fs-8 fw-bold text-uppercase">Super Admin Mode</span>
                    <span class="fs-8 text-muted ms-2">Anda bertindak sebagai Super Admin — semua aksi tercatat di audit log.</span>
                </div>
            </div>
            <a href="{{ $superadminBackRoute ?? route('superadmin.akreditasi.index') }}" class="btn btn-sm btn-light">
                <i class="ki-outline ki-left fs-4"></i>Kembali ke Console
            </a>
        </div>
    </div>
</div>
