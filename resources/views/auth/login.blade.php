@extends('layouts.metronic.auth')

@section('title', 'Masuk')

@section('content')
    <form class="form w-100" method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <div class="text-center mb-11">
            <h1 class="text-gray-900 fw-bolder mb-3">Selamat Datang</h1>
            <div class="text-gray-500 fw-semibold fs-6">Sistem Akreditasi PesantrenMu</div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger d-flex align-items-center p-5 mb-10">
                <i class="ki-outline ki-information-5 fs-2hx text-danger me-4"></i>
                <div class="d-flex flex-column">
                    @foreach ($errors->all() as $error)
                        <span>{{ $error }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        @if (session('status'))
            <div class="alert alert-success d-flex align-items-center p-5 mb-10">
                <i class="ki-outline ki-verify fs-2hx text-success me-4"></i>
                <div class="d-flex flex-column">
                    <span>{{ session('status') }}</span>
                </div>
            </div>
        @endif

        <div class="d-grid mb-8">
            <a href="{{ route('auth.muhammadiyah.redirect') }}" class="btn btn-light-primary border border-primary border-dashed">
                <i class="ki-outline ki-verify fs-2"></i>Masuk dengan Muhammadiyah ID
            </a>
            <div class="text-center text-muted fs-8 mt-3">Gunakan akun yang sudah diundang oleh Super Admin.</div>
        </div>

        <div class="separator separator-content my-8"><span class="w-125px text-gray-500 fw-semibold fs-7">atau login lokal</span></div>

        <div class="fv-row mb-8">
            <label class="form-label fs-6 fw-semibold text-gray-700">Email</label>
            <input
                type="email"
                name="email"
                class="form-control form-control-solid @error('email') is-invalid @enderror"
                value="{{ old('email') }}"
                placeholder="admin@pesantrenmu.id"
                autocomplete="email"
                autofocus
            >
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="fv-row mb-3">
            <label class="form-label fs-6 fw-semibold text-gray-700">Kata Sandi</label>
            <div class="position-relative">
                <input
                    type="password"
                    name="password"
                    class="form-control form-control-solid @error('password') is-invalid @enderror"
                    placeholder="Masukkan kata sandi"
                    autocomplete="current-password"
                >
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
            <div class="form-check form-check-solid">
                <input
                    class="form-check-input"
                    type="checkbox"
                    name="remember"
                    id="remember"
                    {{ old('remember') ? 'checked' : '' }}
                >
                <label class="form-check-label text-gray-600" for="remember">Ingat saya</label>
            </div>

            <a href="mailto:admin@pesantrenmu.id?subject=Lupa%20Kata%20Sandi%20%2D%20PesantrenMu" class="link-primary fs-6 fw-semibold">Lupa Kata Sandi?</a>
        </div>

        <div class="d-grid mb-10">
            <button type="submit" class="btn btn-primary">
                <span class="indicator-label">Masuk</span>
                <span class="indicator-progress">
                    Memproses... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                </span>
            </button>
        </div>
    </form>
@endsection
