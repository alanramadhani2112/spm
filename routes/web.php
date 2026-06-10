<?php

use App\Http\Controllers\Pesantren\AkreditasiController;
use App\Http\Controllers\Pesantren\DataController as PesantrenDataController;
use Illuminate\Support\Facades\Route;

// Auth routes
Route::get('/login', function () {
    return view('auth.login');
})->middleware('guest')->name('login');

Route::post('/login', function () {
    $credentials = request()->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (auth()->attempt($credentials, request()->boolean('remember'))) {
        request()->session()->regenerate();

        $user = auth()->user();
        return match ($user->role?->parameter) {
            'super_admin', 'superadmin' => redirect()->route('superadmin.dashboard'),
            'admin' => redirect()->route('admin.akreditasi.index'),
            'asesor' => redirect()->intended(route('asesor.ketua.index')),
            'pesantren' => redirect()->route('pesantren.akreditasi.index'),
            default => redirect('/'),
        };
    }

    return back()
        ->withInput(request()->only('email'))
        ->withErrors(['email' => 'Email atau kata sandi tidak ditemukan.']);
})->middleware('guest');

Route::middleware(['auth', 'role:pesantren,super_admin'])->prefix('pesantren')->name('pesantren.')->group(function () {
    Route::get('/data', [PesantrenDataController::class, 'index'])->name('data.index');
    Route::post('/data/profil', [PesantrenDataController::class, 'updateProfile'])->name('data.profile');
    Route::post('/data/ipm', [PesantrenDataController::class, 'updateIpm'])->name('data.ipm');
    Route::post('/data/sdm', [PesantrenDataController::class, 'updateSdm'])->name('data.sdm');
    Route::post('/data/edpm', [PesantrenDataController::class, 'updateEdpm'])->name('data.edpm');

    Route::get('/akreditasi', [AkreditasiController::class, 'index'])->name('akreditasi.index');
    Route::get('/akreditasi/pengajuan', [AkreditasiController::class, 'pengajuanForm'])->name('akreditasi.pengajuan');
    Route::post('/akreditasi/pengajuan', [AkreditasiController::class, 'submitPengajuan'])->name('akreditasi.submit-pengajuan');
    Route::get('/akreditasi/{id}/assessment', [AkreditasiController::class, 'assessmentForm'])->name('akreditasi.assessment');
    Route::post('/akreditasi/{id}/assessment', [AkreditasiController::class, 'submitAssessment'])->name('akreditasi.submit-assessment');
    Route::get('/akreditasi/{id}/koreksi', [AkreditasiController::class, 'correctionForm'])->name('akreditasi.koreksi');
    Route::post('/akreditasi/{id}/koreksi', [AkreditasiController::class, 'submitCorrection'])->name('akreditasi.submit-koreksi');
    Route::post('/akreditasi/{id}/kartu-kendali', [AkreditasiController::class, 'uploadKartuKendali'])->name('akreditasi.upload-kk');
    Route::get('/akreditasi/{id}/hasil', [AkreditasiController::class, 'hasilAkhir'])->name('akreditasi.hasil');
    Route::post('/akreditasi/{id}/banding', [AkreditasiController::class, 'submitBanding'])->name('akreditasi.submit-banding');
});

use App\Http\Controllers\Admin\AkreditasiController as AdminAkreditasiController;

Route::middleware(['auth', 'role:admin,super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/akreditasi', [AdminAkreditasiController::class, 'index'])->name('akreditasi.index');
    Route::get('/akreditasi/export', [AdminAkreditasiController::class, 'export'])->name('akreditasi.export');
    Route::get('/akreditasi/{id}/review-awal', [AdminAkreditasiController::class, 'reviewAwal'])->name('akreditasi.review-awal');
    Route::post('/akreditasi/{id}/terima-pengajuan', [AdminAkreditasiController::class, 'terimaPengajuan'])->name('akreditasi.terima-pengajuan');
    Route::post('/akreditasi/{id}/tolak-pengajuan', [AdminAkreditasiController::class, 'tolakPengajuan'])->name('akreditasi.tolak-pengajuan');
    Route::match(['get', 'post'], '/akreditasi/{id}/buka-assessment', [AdminAkreditasiController::class, 'bukaAssessment'])->name('akreditasi.buka-assessment');
    Route::get('/akreditasi/{id}/review-tahap1', [AdminAkreditasiController::class, 'reviewTahap1'])->name('akreditasi.review-tahap1');
    Route::post('/akreditasi/{id}/minta-perbaikan-tahap1', [AdminAkreditasiController::class, 'mintaPerbaikanTahap1'])->name('akreditasi.minta-perbaikan-tahap1');
    Route::post('/akreditasi/{id}/approve-tahap1', [AdminAkreditasiController::class, 'approveTahap1'])->name('akreditasi.approve-tahap1');
    Route::match(['get', 'post'], '/akreditasi/{id}/assign-asesor', [AdminAkreditasiController::class, 'assignAsesor'])->name('akreditasi.assign-asesor');
    Route::match(['get', 'post'], '/akreditasi/{id}/reassign-asesor', [AdminAkreditasiController::class, 'reassignAsesor'])->name('akreditasi.reassign-asesor');
    Route::post('/akreditasi/{id}/handle-limit-review', [AdminAkreditasiController::class, 'handleLimitReview'])->name('akreditasi.handle-limit-review');
    Route::get('/akreditasi/{id}/validasi-akhir', [AdminAkreditasiController::class, 'validasiAkhir'])->name('akreditasi.validasi-akhir');
    Route::post('/akreditasi/{id}/approve-final', [AdminAkreditasiController::class, 'approveFinal'])->name('akreditasi.approve-final');
    Route::post('/akreditasi/{id}/tolak-final', [AdminAkreditasiController::class, 'tolakFinal'])->name('akreditasi.tolak-final');
    Route::post('/akreditasi/{id}/terbitkan-sk', [AdminAkreditasiController::class, 'terbitkanSK'])->name('akreditasi.terbitkan-sk');
    Route::get('/akreditasi/{id}/banding', [AdminAkreditasiController::class, 'banding'])->name('akreditasi.banding');
    Route::post('/banding/{id}/terima', [AdminAkreditasiController::class, 'terimaBanding'])->name('banding.terima');
    Route::post('/banding/{id}/tolak', [AdminAkreditasiController::class, 'tolakBanding'])->name('banding.tolak');
});

use App\Http\Controllers\Asesor\KetuaAsesorController;
use App\Http\Controllers\Asesor\AnggotaAsesorController;

Route::middleware(['auth', 'role:asesor,super_admin'])->prefix('asesor/ketua')->name('asesor.ketua.')->group(function () {
    Route::get('/akreditasi', [KetuaAsesorController::class, 'index'])->name('index');
    Route::get('/akreditasi/{id}/review-tahap2', [KetuaAsesorController::class, 'reviewTahap2'])->name('review-tahap2');
    Route::post('/akreditasi/{id}/layak-visitasi', [KetuaAsesorController::class, 'nyatakanLayakVisitasi'])->name('nyatakan-layak-visitasi');
    Route::post('/akreditasi/{id}/minta-perbaikan-tahap2', [KetuaAsesorController::class, 'mintaPerbaikanTahap2'])->name('minta-perbaikan-tahap2');
    Route::match(['get', 'post'], '/akreditasi/{id}/jadwalkan-visitasi', [KetuaAsesorController::class, 'jadwalkanVisitasi'])->name('jadwalkan-visitasi');
    Route::post('/akreditasi/{id}/tandai-visitasi-selesai', [KetuaAsesorController::class, 'tandaiVisitasiSelesai'])->name('tandai-visitasi-selesai');
    Route::match(['get', 'post'], '/akreditasi/{id}/input-na1', [KetuaAsesorController::class, 'inputNA1'])->name('input-na1');
    Route::match(['get', 'post'], '/akreditasi/{id}/input-nk', [KetuaAsesorController::class, 'inputNK'])->name('input-nk');
    Route::match(['get', 'post'], '/akreditasi/{id}/upload-laporan', [KetuaAsesorController::class, 'uploadLaporan'])->name('upload-laporan');
    Route::post('/akreditasi/{id}/submit-hasil-visitasi', [KetuaAsesorController::class, 'submitHasilVisitasi'])->name('submit-hasil-visitasi');
});

Route::middleware(['auth', 'role:asesor,super_admin'])->prefix('asesor/anggota')->name('asesor.anggota.')->group(function () {
    Route::get('/akreditasi', [AnggotaAsesorController::class, 'index'])->name('index');
    Route::match(['get', 'post'], '/akreditasi/{id}/input-na2', [AnggotaAsesorController::class, 'inputNA2'])->name('input-na2');
    Route::match(['get', 'post'], '/akreditasi/{id}/upload-laporan-individu', [AnggotaAsesorController::class, 'uploadLaporanIndividu'])->name('upload-laporan-individu');
});

use App\Http\Controllers\SuperAdmin\AuditController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\SettingsController;
use App\Http\Controllers\SuperAdmin\MasterDataController;
use App\Http\Controllers\SuperAdmin\AkreditasiController as SuperAdminAkreditasiController;

Route::middleware(['auth', 'role:super_admin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/export', [DashboardController::class, 'export'])->name('dashboard.export');

    // Akreditasi — superadmin dapat semua akses operasional
    Route::get('/akreditasi', [SuperAdminAkreditasiController::class, 'index'])->name('akreditasi.index');
    Route::get('/akreditasi/pengajuan', [SuperAdminAkreditasiController::class, 'pengajuanForm'])->name('akreditasi.pengajuan');
    Route::post('/akreditasi/pengajuan', [SuperAdminAkreditasiController::class, 'submitPengajuan'])->name('akreditasi.submit-pengajuan');
    Route::get('/akreditasi/{id}/review-awal', [SuperAdminAkreditasiController::class, 'reviewAwal'])->name('akreditasi.review-awal');
    Route::post('/akreditasi/{id}/terima-pengajuan', [SuperAdminAkreditasiController::class, 'terimaPengajuan'])->name('akreditasi.terima-pengajuan');
    Route::post('/akreditasi/{id}/tolak-pengajuan', [SuperAdminAkreditasiController::class, 'tolakPengajuan'])->name('akreditasi.tolak-pengajuan');
    Route::match(['get', 'post'], '/akreditasi/{id}/buka-assessment', [SuperAdminAkreditasiController::class, 'bukaAssessment'])->name('akreditasi.buka-assessment');
    Route::get('/akreditasi/{id}/review-tahap1', [SuperAdminAkreditasiController::class, 'reviewTahap1'])->name('akreditasi.review-tahap1');
    Route::post('/akreditasi/{id}/minta-perbaikan-tahap1', [SuperAdminAkreditasiController::class, 'mintaPerbaikanTahap1'])->name('akreditasi.minta-perbaikan-tahap1');
    Route::post('/akreditasi/{id}/approve-tahap1', [SuperAdminAkreditasiController::class, 'approveTahap1'])->name('akreditasi.approve-tahap1');
    Route::match(['get', 'post'], '/akreditasi/{id}/assign-asesor', [SuperAdminAkreditasiController::class, 'assignAsesor'])->name('akreditasi.assign-asesor');
    Route::match(['get', 'post'], '/akreditasi/{id}/reassign-asesor', [SuperAdminAkreditasiController::class, 'reassignAsesor'])->name('akreditasi.reassign-asesor');
    Route::post('/akreditasi/{id}/handle-limit-review', [SuperAdminAkreditasiController::class, 'handleLimitReview'])->name('akreditasi.handle-limit-review');
    Route::get('/akreditasi/{id}/review-tahap2', [SuperAdminAkreditasiController::class, 'reviewTahap2'])->name('akreditasi.review-tahap2');
    Route::post('/akreditasi/{id}/layak-visitasi', [SuperAdminAkreditasiController::class, 'nyatakanLayakVisitasi'])->name('akreditasi.layak-visitasi');
    Route::post('/akreditasi/{id}/minta-perbaikan-tahap2', [SuperAdminAkreditasiController::class, 'mintaPerbaikanTahap2'])->name('akreditasi.minta-perbaikan-tahap2');
    Route::match(['get', 'post'], '/akreditasi/{id}/jadwalkan-visitasi', [SuperAdminAkreditasiController::class, 'jadwalkanVisitasi'])->name('akreditasi.jadwalkan-visitasi');
    Route::post('/akreditasi/{id}/tandai-visitasi-selesai', [SuperAdminAkreditasiController::class, 'tandaiVisitasiSelesai'])->name('akreditasi.tandai-visitasi-selesai');
    Route::match(['get', 'post'], '/akreditasi/{id}/input-na1', [SuperAdminAkreditasiController::class, 'inputNA1'])->name('akreditasi.input-na1');
    Route::match(['get', 'post'], '/akreditasi/{id}/input-na2', [SuperAdminAkreditasiController::class, 'inputNA2'])->name('akreditasi.input-na2');
    Route::match(['get', 'post'], '/akreditasi/{id}/input-nk', [SuperAdminAkreditasiController::class, 'inputNK'])->name('akreditasi.input-nk');
    Route::match(['get', 'post'], '/akreditasi/{id}/upload-laporan', [SuperAdminAkreditasiController::class, 'uploadLaporan'])->name('akreditasi.upload-laporan');
    Route::post('/akreditasi/{id}/submit-hasil-visitasi', [SuperAdminAkreditasiController::class, 'submitHasilVisitasi'])->name('akreditasi.submit-hasil-visitasi');
    Route::post('/akreditasi/{id}/kartu-kendali', [SuperAdminAkreditasiController::class, 'uploadKartuKendali'])->name('akreditasi.upload-kk');
    Route::get('/akreditasi/{id}/validasi-akhir', [SuperAdminAkreditasiController::class, 'validasiAkhir'])->name('akreditasi.validasi-akhir');
    Route::post('/akreditasi/{id}/approve-final', [SuperAdminAkreditasiController::class, 'approveFinal'])->name('akreditasi.approve-final');
    Route::post('/akreditasi/{id}/tolak-final', [SuperAdminAkreditasiController::class, 'tolakFinal'])->name('akreditasi.tolak-final');
    Route::post('/akreditasi/{id}/terbitkan-sk', [SuperAdminAkreditasiController::class, 'terbitkanSK'])->name('akreditasi.terbitkan-sk');
    Route::post('/banding/{id}/terima', [SuperAdminAkreditasiController::class, 'terimaBanding'])->name('banding.terima');
    Route::post('/banding/{id}/tolak', [SuperAdminAkreditasiController::class, 'tolakBanding'])->name('banding.tolak');

    Route::prefix('master-data')->name('master-data.')->group(function () {
        Route::get('/', [MasterDataController::class, 'index'])->name('index');
        Route::get('/edpm', [MasterDataController::class, 'edpm'])->name('edpm.index');
        Route::post('/edpm/komponen', [MasterDataController::class, 'storeKomponen'])->name('edpm.komponen.store');
        Route::put('/edpm/komponen/{komponen}', [MasterDataController::class, 'updateKomponen'])->name('edpm.komponen.update');
        Route::delete('/edpm/komponen/{komponen}', [MasterDataController::class, 'destroyKomponen'])->name('edpm.komponen.destroy');
        Route::post('/edpm/butir', [MasterDataController::class, 'storeButir'])->name('edpm.butir.store');
        Route::put('/edpm/butir/{butir}', [MasterDataController::class, 'updateButir'])->name('edpm.butir.update');
        Route::delete('/edpm/butir/{butir}', [MasterDataController::class, 'destroyButir'])->name('edpm.butir.destroy');
        Route::get('/document-categories', [MasterDataController::class, 'documentCategories'])->name('document-categories.index');
        Route::post('/document-categories', [MasterDataController::class, 'storeDocumentCategory'])->name('document-categories.store');
        Route::put('/document-categories/{category}', [MasterDataController::class, 'updateDocumentCategory'])->name('document-categories.update');
        Route::patch('/document-categories/{category}/toggle', [MasterDataController::class, 'toggleDocumentCategory'])->name('document-categories.toggle');
        Route::delete('/document-categories/{category}', [MasterDataController::class, 'destroyDocumentCategory'])->name('document-categories.destroy');
        Route::get('/roles', [MasterDataController::class, 'roles'])->name('roles.index');
        Route::put('/roles/{role}/permissions', [MasterDataController::class, 'updateRolePermissions'])->name('roles.permissions.update');
        Route::get('/users', [MasterDataController::class, 'users'])->name('users.index');
        Route::put('/users/{user}', [MasterDataController::class, 'updateUser'])->name('users.update');
    });

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/settings/deadline', [SettingsController::class, 'deadline'])->name('settings.deadline');
    Route::get('/settings/correction', [SettingsController::class, 'correction'])->name('settings.correction');
    Route::get('/settings/dokumen', [SettingsController::class, 'dokumen'])->name('settings.dokumen');
    Route::get('/settings/nv', [SettingsController::class, 'nv'])->name('settings.nv');
    Route::get('/settings/notifikasi', [SettingsController::class, 'notifikasi'])->name('settings.notifikasi');
    Route::get('/settings/banding', [SettingsController::class, 'banding'])->name('settings.banding');

    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
    Route::get('/audit/{id}', [AuditController::class, 'show'])->name('audit.show');
});

Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        return match ($user->role?->parameter) {
            'super_admin', 'superadmin' => redirect()->route('superadmin.dashboard'),
            'admin' => redirect()->route('admin.akreditasi.index'),
            'asesor' => redirect()->intended(route('asesor.ketua.index')),
            'pesantren' => redirect()->route('pesantren.akreditasi.index'),
            default => redirect()->route('login'),
        };
    }
    return redirect()->route('login');
});

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->middleware('auth')->name('logout');
