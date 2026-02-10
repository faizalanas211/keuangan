<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Controllers
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KehadiranController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\FingerprintController;
use App\Http\Controllers\PenilaianController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\PenghasilanController;
use App\Http\Controllers\PotonganController;
use App\Http\Controllers\SlipGajiController;
use App\Http\Controllers\ProfileController;


/*
|--------------------------------------------------------------------------
| Guest Routes (BELUM LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'loginAction'])->name('loginAction');

    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'registerAction'])->name('registerAction');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (SUDAH LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('dashboard')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD (ADMIN & PEGAWAI)
    |--------------------------------------------------------------------------
    */
    Route::get('/', [DashboardController::class, 'index'])
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | SLIP GAJI (ADMIN & PEGAWAI)
    | - Admin   : bisa pilih pegawai
    | - Pegawai : hanya lihat milik sendiri
    |--------------------------------------------------------------------------
    */
    Route::get('slip-gaji', [SlipGajiController::class, 'index'])
        ->name('slip-gaji.index');

    Route::get('slip-gaji/cetak/{pegawai}/{bulan}', [SlipGajiController::class, 'cetak'])
        ->name('slip-gaji.cetak');

    /*
    |--------------------------------------------------------------------------
    | ================= DATA MASTER (ADMIN)
    | NOTE: Validasi role dilakukan di CONTROLLER
    |--------------------------------------------------------------------------
    */

    // Pegawai
    Route::resource('pegawai', PegawaiController::class);
    Route::post('pegawai/import', [PegawaiController::class, 'import'])
        ->name('pegawai.import');
    Route::get('/ganti-password', [App\Http\Controllers\PasswordController::class, 'edit'])
    ->name('password.edit');

    Route::post('/ganti-password', [App\Http\Controllers\PasswordController::class, 'update'])
    ->name('password.update');
    Route::post('/profile/update-photo', [App\Http\Controllers\ProfileController::class, 'updatePhoto'])
    ->name('profile.photo.update');

    Route::post('/pegawai/generate-akun', [PegawaiController::class, 'generateAkun'])
    ->name('pegawai.generateAkun');
    Route::post('/pegawai/generate-akun-semua', [PegawaiController::class, 'generateAkunSemua'])
        ->name('pegawai.generateAkunSemua');


    // Kehadiran
    Route::resource('kehadiran', KehadiranController::class);
    Route::post('kehadiran/import', [KehadiranController::class, 'import'])
        ->name('kehadiran.import');

    // Penghasilan & Potongan
    Route::resource('penghasilan', PenghasilanController::class);
    Route::resource('potongan', PotonganController::class);
    Route::post('/dashboard/penghasilan/import',
    [App\Http\Controllers\PenghasilanController::class,'import']
)->name('penghasilan.import');


    // Pengajuan
    Route::resource('pengajuan', PengajuanController::class);

    // Barang & Peminjaman
    Route::resource('barang', BarangController::class);
    Route::post('barang/import', [BarangController::class, 'import'])
        ->name('barang.import');

    Route::resource('peminjaman', PeminjamanController::class);
    Route::put(
        'peminjaman/{peminjaman}/kembalikan',
        [PeminjamanController::class, 'kembalikan']
    )->name('peminjaman.kembalikan');

    /*
    |--------------------------------------------------------------------------
    | Penilaian
    |--------------------------------------------------------------------------
    */
    Route::get('penilaian', [PenilaianController::class, 'index'])
        ->name('penilaian.index');

    Route::get('penilaian/create', [PenilaianController::class, 'create'])
        ->name('penilaian.create');

    Route::get('penilaian/create/{id}', [PenilaianController::class, 'createByPegawai'])
        ->name('penilaian.createByPegawai');

    Route::post('penilaian', [PenilaianController::class, 'store'])
        ->name('penilaian.store');

    Route::post('penilaian/import', [PenilaianController::class, 'import'])
        ->name('penilaian.import');

    Route::get('penilaian/{id}/edit', [PenilaianController::class, 'edit'])
        ->name('penilaian.edit');

    Route::put('penilaian/{id}', [PenilaianController::class, 'update'])
        ->name('penilaian.update');

    Route::delete('penilaian/{id}', [PenilaianController::class, 'destroy'])
        ->name('penilaian.destroy');

    /*
    |--------------------------------------------------------------------------
    | Fingerprint (ADMIN)
    |--------------------------------------------------------------------------
    */
    Route::get('debug-fingerprint', [FingerprintController::class, 'debug'])
        ->name('debug-fingerprint');

    Route::get('test-fingerprint', [FingerprintController::class, 'testSimple'])
        ->name('test-fingerprint');

    Route::get('test-buffer', [FingerprintController::class, 'testBuffer'])
        ->name('test-buffer');

    Route::get('sync-kehadiran', [FingerprintController::class, 'sync'])
        ->name('sync-kehadiran');

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */
    Route::post('logout', [AuthController::class, 'logout'])
        ->name('logout');
});
