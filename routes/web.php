<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PermintaanController;
use App\Http\Controllers\SuperadminController;
use App\Http\Controllers\KepalaGudangController;
use App\Http\Controllers\SparepartController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\KepalaROController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\PengirimanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ApprovalStatusController;


require __DIR__ . '/auth.php';

// =====================
// DEFAULT ROUTE
// =====================
Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('home')
        : redirect()->route('login');
});


// =====================
// AUTH
// =====================
Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'login')->name('login.post');
    Route::post('/logout', 'logout')->name('logout');
});

// =====================
// PROFILE (all roles)
// =====================
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', fn() => view('profile.index'))->name('profile.index');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/show', [ProfileController::class, 'show'])->name('profile.show');

});




// =====================
// SUPERADMIN (role:1)
Route::middleware(['auth', 'role:1'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->controller(SuperadminController::class)
    ->group(function () {
        Route::get('/dashboard', 'dashboard')->name('dashboard');
        Route::get('/request', 'requestIndex')->name('request.index');
        Route::post('/pengiriman', [PengirimanController::class, 'store'])->name('pengiriman.store');

        // ✅ Tambahkan ini!
        Route::post('/request/{tiket}/approve', 'approveRequest')->name('request.approve');
        Route::post('/request/{tiket}/reject', 'rejectRequest')->name('request.reject');

        Route::get('/sparepart', [SparepartController::class, 'indexAdmin'])->name('sparepart.index');
        Route::get('/sparepart/{tiket_sparepart}/detail', [SparepartController::class, 'showDetail'])->name('sparepart.detail');
        Route::get('/history', 'historyIndex')->name('history.index');
        Route::get('/history/{tiket}/api', 'historyDetailApi')->name('history.api');
    });


// =====================
// KEPALA RO (role:2)
// =====================
Route::middleware(['auth', 'role:2'])
    ->prefix('kepalaro')
    ->name('kepalaro.')
    ->controller(KepalaROController::class)
    ->group(function () {
        // Halaman Home (ini yang ditampilkan pertama kali setelah login Kepala RO)
        Route::get('/home', fn() => view('kepalaro.home'))->name('home');

        // Dashboard & fitur lainnya
        Route::get('/dashboard', 'index')->name('dashboard');
        Route::get('/history', 'history')->name('history');
        Route::post('/approve/{id}', 'approve')->name('approve');
        Route::post('/reject/{id}', 'reject')->name('reject');

        // API: cek jumlah pending (filter region kepala RO)
        Route::get('/api/pending-count', [KepalaROController::class, 'pendingCount'])
            ->name('api.pending.count');
    });

// =====================
// KEPALA GUDANG (role:3)
// =====================
Route::middleware(['auth', 'role:3'])
    ->prefix('kepalagudang')
    ->name('kepalagudang.')
    ->controller(KepalaGudangController::class)
    ->group(function () {
        Route::get('/dashboard', fn() => view('kepalagudang.dashboard'))->name('dashboard');
        Route::get('/dashboard', 'dashboard')->name('dashboard');

        Route::get('/request', 'requestIndex')->name('request.index');
        Route::post('/request/store', 'requestStore')->name('request.store');

        Route::post('/sparepart/store', [SparepartController::class, 'store'])->name('sparepart.store');
        Route::get('/sparepart', [SparepartController::class, 'index'])->name('sparepart.index');
        Route::get('/sparepart/{tiket_sparepart}/detail', [SparepartController::class, 'showDetail'])->name('sparepart.detail');

         Route::get('/datauser', [UserController::class, 'index'])->name('user.index');
        Route::post('/datauser/store', [UserController::class, 'store'])->name('user.store');
        Route::get('/datauser/{id}/edit', [UserController::class, 'edit'])->name('user.edit');
        Route::put('/datauser/{id}', [UserController::class, 'update'])->name('user.update');
        Route::delete('/datauser/{id}', [UserController::class, 'destroy'])->name('user.destroy');

        Route::get('/history', 'historyIndex')->name('history.index');
        Route::get('/history/{tiket}/detail', 'historyDetail')->name('history.detail');
        Route::get('/history/{tiket}/api', [KepalaGudangController::class, 'historyDetailApi'])
            ->name('kepalagudang.history.api');
        Route::post('/request/{tiket}/approve', [KepalaGudangController::class, 'approveGudang'])
            ->name('kepalagudang.request.approve');
        Route::post('/request/{tiket}/reject', [KepalaGudangController::class, 'rejectGudang'])
            ->name('kepalagudang.request.reject');

        Route::get('/profile', fn() => view('kepalagudang.profile'))->name('profile');

        Route::put('/sparepart/{id}', [SparepartController::class, 'update'])->name('sparepart.update');

        Route::delete('/sparepart/{id}', [SparepartController::class, 'destroy'])
            ->name('.sparepart.details.destroy')
            ->middleware('auth')
            ->where('serial', '.*');

        Route::get('/data', [DataController::class, 'index'])->name('data');

        // Jenis
        Route::post('/data/jenis/', [DataController::class, 'storeJenis'])->name('jenis.store');
        Route::put('/data/jenis/{id}', [DataController::class, 'updateJenis'])->name('jenis.update');
        Route::delete('/data/jenis/{id}', [DataController::class, 'destroyJenis'])->name('jenis.destroy');

        // Tipe
        Route::post('/data/tipe', [DataController::class, 'storeTipe'])->name('tipe.store');
        Route::put('/data/tipe/{id}', [DataController::class, 'updateTipe'])->name('tipe.update');
        Route::delete('/data/tipe/{id}', [DataController::class, 'destroyTipe'])->name('tipe.destroy');

        // Vendor
        Route::post('/data/vendor', [DataController::class, 'storeVendor'])->name('vendor.store');
        Route::put('/data/vendor/{id}', [DataController::class, 'updateVendor'])->name('vendor.update');
        Route::delete('/data/vendor/{id}', [DataController::class, 'destroyVendor'])->name('vendor.destroy');


        // Region
        Route::post('/data/region', [DataController::class, 'storeRegion'])->name('region.store');
        Route::put('/data/region/{id}', [DataController::class, 'updateRegion'])->name('region.update');
        Route::delete('/data/region/{id}', [DataController::class, 'destroyRegion'])->name('region.destroy');
        
        // 🔥 Baru: Simpan data pengiriman
        Route::post('/pengiriman', [PengirimanController::class, 'store'])->name('pengiriman.store');
    });



// =====================
// USER (role:4)
// =====================
Route::middleware(['auth', 'role:4'])
    ->prefix('user')
    ->group(function () {
        Route::get('/home', [HomeController::class, 'index'])->name('home');
        Route::get('/sparepart', [HomeController::class, 'jenisBarang'])->name('jenis.barang');
        Route::get('/jenis-barang', [PermintaanController::class, 'getJenis']);
        Route::get('/tipe-barang', [PermintaanController::class, 'getTipe']);
    });


// =====================
// REQUEST BARANG (all authenticated users)
// =====================
// Menu lain

// Request Barang
Route::prefix('requestbarang')
    ->name('request.barang.')
    ->controller(PermintaanController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{tiket}', 'getDetail')->name('detail');
        Route::post('/', 'store')->name('store');

        Route::get('/requestbarang/api/permintaan/{tiket}/status', [ApprovalStatusController::class, 'getStatus'])->name('api.permintaan.status');
// ✅ Cukup satu ini saja
Route::get('/api/permintaan/{tiket}/status', [ApprovalStatusController::class, 'getStatus'])->name('api.permintaan.status');
        // 🔥 API: Detail Status Approval Berjenjang
        Route::get('/api/permintaan/{tiket}/status', function ($tiket) {
            $permintaan = \App\Models\Permintaan::where('tiket', $tiket)->firstOrFail();

            return response()->json([
                'ro' => $permintaan->status_ro,
                'gudang' => $permintaan->status_gudang,
                'admin' => $permintaan->status_admin,
                'super_admin' => $permintaan->status_super_admin,
                'catatan' => collect([
                    $permintaan->catatan_ro,
                    $permintaan->catatan_gudang,
                    $permintaan->catatan_admin,
                    $permintaan->catatan_super_admin,
                ])->filter()->first(),
            ]);
        })->name('api.permintaan.status');

        // ✅ API: Ambil jenis barang berdasarkan kategori
        Route::get('/api/jenis-barang', function (\Illuminate\Http\Request $request) {
            $kategori = $request->query('kategori');
            $query = \App\Models\JenisBarang::query();

            if ($kategori) {
                $query->where('kategori', $kategori);
            }

            return response()->json(
                $query->orderBy('nama')->get(['id', 'nama']) // <-- di sini: as nama
            );
        })->name('api.jenis.barang');

        // API: Ambil tipe barang berdasarkan kategori
        Route::get('/api/tipe-barang', function (\Illuminate\Http\Request $request) {
            $kategori = $request->query('kategori');
            $jenisId = $request->query('jenis_id');

            $query = \App\Models\TipeBarang::query();

            // Filter berdasarkan kategori
            if ($kategori) {
                $query->where('kategori', $kategori);
            }

            // Filter berdasarkan relasi ke jenis_barang melalui detail_barang
            if ($jenisId) {
                $query->whereHas('listBarangs', function ($q) use ($jenisId) {
                    $q->where('jenis_id', $jenisId);
                });
            }

            return response()->json(
                $query->orderBy('nama')->get(['id', 'nama'])
            );
        })->name('api.tipe.barang');
    });