<div class="card card-flush bg-light mb-6 border border-dashed" style="border-color: var(--bs-gray-300);">
    <div class="card-header border-0 px-6 pt-5 pb-0" id="glossaryHelpHeader">
        <div class="d-flex align-items-center gap-3">
            <span class="symbol symbol-35px">
                <span class="symbol-label bg-light-info">
                    <i class="ki-outline ki-message-question fs-3 text-info"></i>
                </span>
            </span>
            <div>
                <h3 class="card-title fs-5 fw-bold text-gray-900 m-0">Panduan Istilah</h3>
                <span class="fs-8 text-muted">Ringkasan akronim dan singkatan yang digunakan dalam sistem akreditasi.</span>
            </div>
            <div class="ms-auto">
                <button
                    class="btn btn-sm btn-icon btn-light"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#glossaryHelpBody"
                    aria-expanded="false"
                    aria-controls="glossaryHelpBody"
                >
                    <i class="ki-outline ki-down fs-5"></i>
                </button>
            </div>
        </div>
    </div>

    <div id="glossaryHelpBody" class="collapse" aria-labelledby="glossaryHelpHeader">
        <div class="card-body px-6 pt-4 pb-5">
            <div class="table-responsive">
                <table class="table table-borderless align-middle m-0 fs-7">
                    <thead class="border-bottom">
                        <tr class="text-uppercase fs-8 text-gray-500 fw-bold">
                            <th class="ps-0 w-125px">Akronim</th>
                            <th class="w-250px">Kepanjangan</th>
                            <th class="pe-0">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-0 fw-bold text-gray-800">EDPM</td>
                            <td class="text-gray-700">Evaluasi Diri Pondok Pesantren Muadalah</td>
                            <td class="text-muted pe-0">Instrumen penilaian mutu mandiri yang diisi oleh pesantren.</td>
                        </tr>
                        <tr>
                            <td class="ps-0 fw-bold text-gray-800">IPR</td>
                            <td class="text-gray-700">Instrumen Penilaian Rapor</td>
                            <td class="text-muted pe-0">Nama historis/alias untuk EDPM.</td>
                        </tr>
                        <tr>
                            <td class="ps-0 fw-bold text-gray-800">NV</td>
                            <td class="text-gray-700">Nilai Visitasi</td>
                            <td class="text-muted pe-0">Nilai akhir setelah visitasi, dihitung dari NA1, NA2, dan NK.</td>
                        </tr>
                        <tr>
                            <td class="ps-0 fw-bold text-gray-800">NA1</td>
                            <td class="text-gray-700">Nilai Akhir 1</td>
                            <td class="text-muted pe-0">Nilai yang diberikan oleh Ketua Asesor setelah visitasi.</td>
                        </tr>
                        <tr>
                            <td class="ps-0 fw-bold text-gray-800">NA2</td>
                            <td class="text-gray-700">Nilai Akhir 2</td>
                            <td class="text-muted pe-0">Nilai yang diberikan oleh Anggota Asesor setelah visitasi.</td>
                        </tr>
                        <tr>
                            <td class="ps-0 fw-bold text-gray-800">NK</td>
                            <td class="text-gray-700">Nilai Komprehensif</td>
                            <td class="text-muted pe-0">Nilai gabungan akhir dari seluruh komponen penilaian.</td>
                        </tr>
                        <tr>
                            <td class="ps-0 fw-bold text-gray-800">NBM</td>
                            <td class="text-gray-700">Nomor Baku Muhammadiyah</td>
                            <td class="text-muted pe-0">Nomor identitas resmi setiap pesantren dalam sistem Muhammadiyah.</td>
                        </tr>
                        <tr class="border-bottom-0">
                            <td class="ps-0 fw-bold text-gray-800">M-ID</td>
                            <td class="text-gray-700">Muhammadiyah ID</td>
                            <td class="text-muted pe-0">Identitas SSO (Single Sign-On) untuk akses ke ekosistem digital Muhammadiyah.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @pushOnce('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const collapseEl = document.getElementById('glossaryHelpBody');
            if (!collapseEl) return;

            collapseEl.addEventListener('show.bs.collapse', function () {
                const btn = document.querySelector('[data-bs-target="#glossaryHelpBody"]');
                if (btn) {
                    const icon = btn.querySelector('i');
                    if (icon) {
                        icon.classList.remove('ki-down');
                        icon.classList.add('ki-up');
                    }
                }
            });

            collapseEl.addEventListener('hide.bs.collapse', function () {
                const btn = document.querySelector('[data-bs-target="#glossaryHelpBody"]');
                if (btn) {
                    const icon = btn.querySelector('i');
                    if (icon) {
                        icon.classList.remove('ki-up');
                        icon.classList.add('ki-down');
                    }
                }
            });
        });
    </script>
    @endPushOnce
</div>
