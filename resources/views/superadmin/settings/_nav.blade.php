@php
    $settingTabs = [
        ['route' => 'superadmin.settings.index', 'label' => 'Ringkasan', 'icon' => 'ki-category'],
        ['route' => 'superadmin.settings.deadline', 'label' => 'Deadline', 'icon' => 'ki-calendar-tick'],
        ['route' => 'superadmin.settings.correction', 'label' => 'Koreksi', 'icon' => 'ki-arrows-circle'],
        ['route' => 'superadmin.settings.dokumen', 'label' => 'Dokumen', 'icon' => 'ki-document'],
        ['route' => 'superadmin.settings.nv', 'label' => 'Nilai Visitasi', 'icon' => 'ki-chart-line'],
        ['route' => 'superadmin.settings.notifikasi', 'label' => 'Notifikasi', 'icon' => 'ki-notification'],
        ['route' => 'superadmin.settings.banding', 'label' => 'Banding', 'icon' => 'ki-message-question'],
    ];
    $currentRoute = request()->route() ? request()->route()->getName() : '';
@endphp

<nav class="d-flex flex-wrap gap-2 mb-8" aria-label="Navigasi pengaturan Super Admin">
    @foreach($settingTabs as $tab)
        @php
            $active = $tab['route'] === 'superadmin.settings.index'
                ? $currentRoute === 'superadmin.settings.index'
                : \Illuminate\Support\Str::startsWith($currentRoute, $tab['route']);
        @endphp
        <a href="{{ route($tab['route']) }}"
           class="btn btn-sm {{ $active ? 'btn-primary' : 'btn-light btn-color-gray-600' }} fw-semibold"
           @if($active) aria-current="page" @endif>
            <i class="ki-outline {{ $tab['icon'] }} fs-4"></i>{{ $tab['label'] }}
        </a>
    @endforeach
</nav>
