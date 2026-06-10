<nav class="d-flex flex-wrap gap-2 mb-6">
    <a href="{{ route('superadmin.dashboard') }}"
       class="rounded px-3 py-1.5 fs-8 fw-medium {{ request()->routeIs('superadmin.dashboard') ? 'bg-light-primary text-primary' : 'text-muted hover:text-gray-700' }}">
        Dashboard
    </a>
    <a href="{{ route('superadmin.settings.deadline') }}"
       class="rounded px-3 py-1.5 fs-8 fw-medium {{ request()->routeIs('superadmin.settings.deadline') ? 'bg-light-primary text-primary' : 'text-muted hover:text-gray-700' }}">
        Deadline
    </a>
    <a href="{{ route('superadmin.settings.correction') }}"
       class="rounded px-3 py-1.5 fs-8 fw-medium {{ request()->routeIs('superadmin.settings.correction') ? 'bg-light-primary text-primary' : 'text-muted hover:text-gray-700' }}">
        Koreksi
    </a>
    <a href="{{ route('superadmin.settings.dokumen') }}"
       class="rounded px-3 py-1.5 fs-8 fw-medium {{ request()->routeIs('superadmin.settings.dokumen') ? 'bg-light-primary text-primary' : 'text-muted hover:text-gray-700' }}">
        Dokumen
    </a>
    <a href="{{ route('superadmin.settings.nv') }}"
       class="rounded px-3 py-1.5 fs-8 fw-medium {{ request()->routeIs('superadmin.settings.nv') ? 'bg-light-primary text-primary' : 'text-muted hover:text-gray-700' }}">
        NV
    </a>
    <a href="{{ route('superadmin.settings.notifikasi') }}"
       class="rounded px-3 py-1.5 fs-8 fw-medium {{ request()->routeIs('superadmin.settings.notifikasi') ? 'bg-light-primary text-primary' : 'text-muted hover:text-gray-700' }}">
        Notifikasi
    </a>
    <a href="{{ route('superadmin.settings.banding') }}"
       class="rounded px-3 py-1.5 fs-8 fw-medium {{ request()->routeIs('superadmin.settings.banding') ? 'bg-light-primary text-primary' : 'text-muted hover:text-gray-700' }}">
        Banding
    </a>
</nav>
