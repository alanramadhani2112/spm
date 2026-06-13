@extends('layouts.metronic.app')

@section('title', 'Notification Center')
@section('pageTitle', 'Notification Center')

@section('toolbar')
<div class="d-flex flex-wrap align-items-center gap-2">
    <a href="{{ route('superadmin.notifications.index', ['filter' => 'all']) }}" class="btn btn-sm {{ $filter === 'all' ? 'btn-primary' : 'btn-light' }}">Semua</a>
    <a href="{{ route('superadmin.notifications.index', ['filter' => 'unread']) }}" class="btn btn-sm {{ $filter === 'unread' ? 'btn-primary' : 'btn-light' }}">Belum Dibaca</a>
    <form method="POST" action="{{ route('superadmin.notifications.mark-all-read') }}">
        @csrf
        <button type="submit" class="btn btn-sm btn-light-success">Tandai Semua Dibaca</button>
    </form>
</div>
@endsection

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-4 mb-8">
    <div>
        <h2 class="fs-2 fw-bold text-gray-900 mb-2">Pusat Notifikasi Super Admin</h2>
        <p class="fs-7 text-muted mb-0">Pantau notifikasi sistem, SLA breach, banding, SK pending, dan kegagalan pengiriman.</p>
    </div>
    <a href="{{ route('superadmin.dashboard') }}" class="btn btn-sm btn-light">
        <i class="ki-outline ki-category fs-3"></i>Dashboard
    </a>
</div>

<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl-3 col-md-6">
        <x-metronic.stat-card value="{{ $unreadCount }}" label="Belum Dibaca" icon="ki-notification-on" color="warning" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-metronic.stat-card value="{{ $totalCount }}" label="Total Notifikasi" icon="ki-message-text-2" color="primary" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-metronic.stat-card value="{{ $pendingBandingCount }}" label="Banding Pending" icon="ki-message-question" color="danger" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-metronic.stat-card value="{{ $pendingSkCount }}" label="SK Pending" icon="ki-award" color="success" />
    </div>
</div>

<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl-8">
        <div class="card card-flush h-100">
            <div class="card-header py-5">
                <div class="card-title d-flex flex-column">
                    <h3 class="fw-bold text-gray-900 m-0">Inbox Super Admin</h3>
                    <span class="text-muted fs-7 mt-1">Notifikasi yang dikirim ke akun Super Admin aktif.</span>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="d-grid gap-4">
                    @forelse($notifications as $notification)
                        @php
                            $color = $notification->is_read ? 'secondary' : 'primary';
                            $pesantrenName = $notification->akreditasi?->user?->pesantren?->nama_pesantren ?? $notification->akreditasi?->user?->name;
                        @endphp
                        <div class="border border-gray-300 border-dashed rounded p-5">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-4">
                                <div class="d-flex align-items-start gap-4 min-w-0">
                                    <span class="symbol symbol-40px flex-shrink-0">
                                        <span class="symbol-label bg-light-{{ $color }}"><i class="ki-outline ki-notification fs-3 text-{{ $color }}"></i></span>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <span class="badge badge-light-{{ $color }}">{{ $notification->type }}</span>
                                            @if(! $notification->is_read)
                                                <span class="badge badge-light-warning">Baru</span>
                                            @endif
                                        </div>
                                        <div class="fw-semibold text-gray-900">{{ $notification->message }}</div>
                                        <div class="fs-8 text-muted mt-1">
                                            {{ $notification->created_at?->format('d M Y H:i') }}
                                            @if($pesantrenName)
                                                &middot; {{ $pesantrenName }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    @if($notification->akreditasi_id)
                                        <a href="{{ route('superadmin.akreditasi.show', $notification->akreditasi_id) }}" class="btn btn-sm btn-light-primary">Detail</a>
                                    @endif
                                    @if(! $notification->is_read)
                                        <form method="POST" action="{{ route('superadmin.notifications.mark-read', $notification) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light-success">Dibaca</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12 text-muted border rounded bg-light">Belum ada notifikasi untuk filter ini.</div>
                    @endforelse
                </div>

                <div class="mt-6">
                    {{ $notifications->links() }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card card-flush mb-8">
            <div class="card-header py-5">
                <div class="card-title d-flex flex-column">
                    <h3 class="fw-bold text-gray-900 m-0">SLA Watchlist</h3>
                    <span class="text-muted fs-7 mt-1">Antrian melewati deadline settings.</span>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="d-grid gap-3">
                    @foreach($overdueQueues as $queue)
                        <a href="{{ $queue['route'] }}" class="d-flex align-items-center justify-content-between py-2 text-decoration-none">
                            <div>
                                <div class="fw-semibold text-gray-900">{{ $queue['label'] }}</div>
                                <div class="fs-8 text-muted">{{ $queue['days'] ?? '-' }} hari SLA</div>
                            </div>
                            <span class="badge badge-light-{{ $queue['count'] > 0 ? 'danger' : 'success' }}">{{ $queue['count'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card card-flush">
            <div class="card-header py-5">
                <div class="card-title d-flex flex-column">
                    <h3 class="fw-bold text-gray-900 m-0">Delivery Health</h3>
                    <span class="text-muted fs-7 mt-1">Kegagalan notifikasi yang perlu ditinjau.</span>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="d-flex align-items-center justify-content-between border border-gray-300 border-dashed rounded p-5">
                    <div>
                        <div class="fw-bold text-gray-900">Failed Notifications</div>
                        <div class="fs-8 text-muted">
                            Terakhir: {{ $latestFailure?->created_at?->format('d M Y H:i') ?? 'Tidak ada' }}
                        </div>
                    </div>
                    <span class="badge badge-light-{{ $failedCount > 0 ? 'danger' : 'success' }}">{{ $failedCount }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
