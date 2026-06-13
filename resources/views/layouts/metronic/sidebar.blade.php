@php
    $user = auth()->user();
    $roleParam = $user?->role?->parameter;

    $isActive = fn(string $pattern): bool => request()->routeIs($pattern);
    $linkClass = fn(string $pattern): string => 'menu-link' . ($isActive($pattern) ? ' active' : '');
    $accordionOpen = fn(array $patterns): string => collect($patterns)->some(fn($p) => $isActive($p)) ? ' hover show' : '';
@endphp

<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="225px" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
        <a href="{{ url('/') }}" class="d-flex align-items-center text-decoration-none">
            <span class="symbol symbol-35px me-3"><span class="symbol-label bg-primary text-white fw-bold">PM</span></span>
            <span class="text-white fw-bold fs-4">PesantrenMu</span>
        </a>
    </div>

    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper hover-scroll-overlay-y my-5" data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer" data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px">
            <div class="menu menu-column menu-rounded menu-sub-indention px-3" id="kt_app_sidebar_menu" data-kt-menu="true" data-kt-menu-expand="false">

                {{-- ===== PESANTREN ===== --}}
                @if ($roleParam === 'pesantren')
                    <div class="menu-item pt-5"><div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">Akreditasi</span></div></div>

                    <div class="menu-item">
                        <a href="{{ route('pesantren.akreditasi.index') }}" class="{{ $linkClass('pesantren.akreditasi.index') }}">
                            <span class="menu-icon"><i class="ki-outline ki-category fs-2"></i></span>
                            <span class="menu-title">Daftar Akreditasi</span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a href="{{ route('pesantren.data.index') }}" class="{{ $linkClass('pesantren.data.*') }}">
                            <span class="menu-icon"><i class="ki-outline ki-notepad-edit fs-2"></i></span>
                            <span class="menu-title">Kelengkapan Data</span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a href="{{ route('pesantren.akreditasi.pengajuan') }}" class="{{ $linkClass('pesantren.akreditasi.pengajuan') }}">
                            <span class="menu-icon"><i class="ki-outline ki-add-files fs-2"></i></span>
                            <span class="menu-title">Ajukan Baru</span>
                        </a>
                    </div>
                @endif

                {{-- ===== ADMIN ===== --}}
                @if ($roleParam === 'admin')
                    <div class="menu-item pt-5"><div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">Akreditasi</span></div></div>

                    <div class="menu-item">
                        <a href="{{ route('admin.akreditasi.index') }}" class="{{ $linkClass('admin.akreditasi.index') }}">
                            <span class="menu-icon"><i class="ki-outline ki-category fs-2"></i></span>
                            <span class="menu-title">Dashboard</span>
                        </a>
                    </div>
                @endif

                {{-- ===== ASESOR ===== --}}
                @if ($roleParam === 'asesor')
                    <div class="menu-item pt-5"><div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">Penugasan</span></div></div>

                    <div class="menu-item">
                        <a href="{{ route('asesor.ketua.index') }}" class="{{ $linkClass('asesor.ketua.index') }}">
                            <span class="menu-icon"><i class="ki-outline ki-briefcase fs-2"></i></span>
                            <span class="menu-title">Penugasan Ketua</span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a href="{{ route('asesor.anggota.index') }}" class="{{ $linkClass('asesor.anggota.index') }}">
                            <span class="menu-icon"><i class="ki-outline ki-user-tick fs-2"></i></span>
                            <span class="menu-title">Penugasan Anggota</span>
                        </a>
                    </div>
                @endif

                {{-- ===== SUPERADMIN ===== --}}
                @if (in_array($roleParam, ['super_admin', 'superadmin'], true))
                    <div class="menu-item pt-5"><div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">Monitoring</span></div></div>

                    <div class="menu-item">
                        <a href="{{ route('superadmin.dashboard') }}" class="{{ $linkClass('superadmin.dashboard') }}">
                            <span class="menu-icon"><i class="ki-outline ki-category fs-2"></i></span>
                            <span class="menu-title">Dashboard</span>
                        </a>
                    </div>
                    @if(auth()->user()?->hasPermission('superadmin.notifications'))
                        <div class="menu-item">
                            <a href="{{ route('superadmin.notifications.index') }}" class="{{ $linkClass('superadmin.notifications.*') }}">
                                <span class="menu-icon"><i class="ki-outline ki-notification-on fs-2"></i></span>
                                <span class="menu-title">Notification Center</span>
                            </a>
                        </div>
                    @endif

                    <div class="menu-item pt-5"><div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">Akreditasi</span></div></div>

                    <div class="menu-item">
                        <a href="{{ route('superadmin.akreditasi.index') }}" class="{{ $linkClass('superadmin.akreditasi.index') }}">
                            <span class="menu-icon"><i class="ki-outline ki-verify fs-2"></i></span>
                            <span class="menu-title">Semua Akreditasi</span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a href="{{ route('superadmin.akreditasi.pengajuan') }}" class="{{ $linkClass('superadmin.akreditasi.pengajuan') }}">
                            <span class="menu-icon"><i class="ki-outline ki-add-files fs-2"></i></span>
                            <span class="menu-title">Pengajuan Baru</span>
                        </a>
                    </div>

                    <div class="menu-item pt-5"><div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">Master Data</span></div></div>

                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion{{ $accordionOpen(['superadmin.master-data.*']) }}">
                        <span class="menu-link">
                            <span class="menu-icon"><i class="ki-outline ki-data fs-2"></i></span>
                            <span class="menu-title">Referensi Sistem</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            <div class="menu-item">
                                <a href="{{ route('superadmin.master-data.index') }}" class="{{ $linkClass('superadmin.master-data.index') }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Ringkasan</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a href="{{ route('superadmin.master-data.edpm.index') }}" class="{{ $linkClass('superadmin.master-data.edpm.*') }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Master EDPM</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a href="{{ route('superadmin.master-data.document-categories.index') }}" class="{{ $linkClass('superadmin.master-data.document-categories.*') }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Kategori Dokumen</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a href="{{ route('superadmin.master-data.roles.index') }}" class="{{ $linkClass('superadmin.master-data.roles.*') }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Role & Permission</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a href="{{ route('superadmin.master-data.users.index') }}" class="{{ $linkClass('superadmin.master-data.users.*') }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Akun Pengguna</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="menu-item pt-5"><div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">Pengaturan</span></div></div>

                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion{{ $accordionOpen(['superadmin.settings.*']) }}">
                        <span class="menu-link">
                            <span class="menu-icon"><i class="ki-outline ki-setting-2 fs-2"></i></span>
                            <span class="menu-title">Akreditasi</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            <div class="menu-item">
                                <a href="{{ route('superadmin.settings.index') }}" class="{{ $linkClass('superadmin.settings.index') }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Umum</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a href="{{ route('superadmin.settings.deadline') }}" class="{{ $linkClass('superadmin.settings.deadline') }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Deadline</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a href="{{ route('superadmin.settings.correction') }}" class="{{ $linkClass('superadmin.settings.correction') }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Batas Koreksi</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a href="{{ route('superadmin.settings.nv') }}" class="{{ $linkClass('superadmin.settings.nv') }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Nilai Visitasi</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a href="{{ route('superadmin.settings.dokumen') }}" class="{{ $linkClass('superadmin.settings.dokumen') }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Dokumen</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a href="{{ route('superadmin.settings.banding') }}" class="{{ $linkClass('superadmin.settings.banding') }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Penanganan Banding</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a href="{{ route('superadmin.settings.notifikasi') }}" class="{{ $linkClass('superadmin.settings.notifikasi') }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Notifikasi</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="menu-item pt-5"><div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">Audit</span></div></div>

                    <div class="menu-item">
                        <a href="{{ route('superadmin.audit.index') }}" class="{{ $linkClass('superadmin.audit.*') }}">
                            <span class="menu-icon"><i class="ki-outline ki-document fs-2"></i></span>
                            <span class="menu-title">Audit Log</span>
                        </a>
                    </div>
                @endif

            </div>
        </div>
    </div>

    <div class="app-sidebar-footer flex-column-auto px-6 pb-6" id="kt_app_sidebar_footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-flex flex-center btn-custom btn-primary overflow-hidden text-nowrap px-0 h-40px w-100">
                <i class="ki-outline ki-exit-right fs-2 me-2"></i>Logout
            </button>
        </form>
    </div>
</div>
