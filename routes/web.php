<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\RawgController;
use App\Http\Controllers\PdfController;


// =========================================
// HALAMAN UTAMA
// =========================================

Route::get('/', function () {
    return redirect('/login');
});


// =========================================
// GUEST: PENGGUNA BELUM LOGIN
// =========================================

Route::middleware('guest')->group(function () {

    // Login
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login']);

    // Register
    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register']);

});


// =========================================
// AUTHENTICATED: SEMUA PENGGUNA LOGIN
// =========================================

Route::middleware('auth')->group(function () {

    // Dashboard (User & Admin Analytics)
    Route::get('/dashboard', [GameController::class, 'dashboard'])
        ->name('dashboard');

    Route::get('/dashboard/activity-data', [GameController::class, 'adminActivityData'])
        ->middleware('admin')
        ->name('dashboard.activity-data');

    // Rekomendasi Game Personal (API Visual)
    Route::get('/recommendations', function () {
        return view('recommendations');
    })->name('recommendations');

    // Halaman My Account
    Route::get('/my-account', function () {
        return view('account');
    })->name('account');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');


    // =====================================
    // KATALOG GAME
    // =====================================

    // Menampilkan katalog game
    Route::get('/games', [GameController::class, 'index'])
        ->name('games.index');


    // =====================================
    // KELOLA KATALOG: ADMIN SAJA
    // =====================================

    Route::middleware('admin')->group(function () {

        // Export & Import katalog game (Admin Only)
        Route::get('/games/export', [GameController::class, 'exportExcel'])
            ->name('games.export');

        Route::post('/games/import', [GameController::class, 'importExcel'])
            ->name('games.import');

        // Menampilkan form tambah game
        Route::get('/games/create', [GameController::class, 'create'])
            ->name('games.create');

        // Menyimpan game baru
        Route::post('/games', [GameController::class, 'store'])
            ->name('games.store');

        // Menampilkan form edit game
        Route::get('/games/{id}/edit', [GameController::class, 'edit'])
            ->whereNumber('id')
            ->name('games.edit');

        // Memperbarui data game
        Route::put('/games/{id}', [GameController::class, 'update'])
            ->whereNumber('id')
            ->name('games.update');

        // Menghapus game
        Route::delete('/games/{id}', [GameController::class, 'destroy'])
            ->whereNumber('id')
            ->name('games.destroy');

        // Export Game Catalog ke PDF (Admin Only)
        Route::get('/games/export/pdf', [PdfController::class, 'exportCatalog'])
      ->name('games.export.pdf');

    });


    // =====================================
    // DETAIL GAME: USER DAN ADMIN
    // =====================================

    // Letakkan setelah route /games/create
    Route::get('/games/{id}', [GameController::class, 'show'])
        ->whereNumber('id')
        ->name('games.show');


    // =====================================
    // PERSONAL LIBRARY: USER DAN ADMIN
    // =====================================

    // Menambahkan game ke Personal Library
    Route::post('/games/{id}/library', [GameController::class, 'addToLibrary'])
        ->whereNumber('id')
        ->name('games.library.add');

    // Menampilkan Personal Library
    Route::get('/my-library', [GameController::class, 'myLibrary'])
        ->name('games.library');

    // Menampilkan form edit Personal Library
    Route::get('/my-library/{id}/edit', [GameController::class, 'editLibrary'])
        ->whereNumber('id')
        ->name('games.library.edit');

    // Memperbarui Personal Library
    Route::put('/my-library/{id}', [GameController::class, 'updateLibrary'])
        ->whereNumber('id')
        ->name('games.library.update');

    // Menghapus game dari Personal Library
    Route::delete('/my-library/{id}', [GameController::class, 'removeFromLibrary'])
        ->whereNumber('id')
        ->name('games.library.remove');

    // Export & Import Personal Library (User & Admin)
    Route::get('/my-library/export', [GameController::class, 'exportLibrary'])
        ->name('games.library.export');

    // Export Personal Library ke PDF (User & Admin)
    Route::get('/my-library/export/pdf', [PdfController::class, 'exportLibrary'])
    ->name('games.library.export.pdf');

    Route::post('/my-library/import', [GameController::class, 'importLibrary'])
        ->name('games.library.import');

    Route::post('/rawg-game/{id}/add',
        [RawgController::class, 'addToMyGames']
    )->name('rawg.add');

});

Route::get('/test-rawg', [RawgController::class, 'search']);

Route::get('/rawg-catalog', [RawgController::class, 'catalog']);

Route::get('/rawg-game/{id}', [RawgController::class, 'detail'])
    ->name('rawg.detail');

Route::post('/rawg-game/{id}/add', [
    RawgController::class,
    'addToMyGames'
])
->middleware('auth')
->name('rawg.add');

use Illuminate\Support\Facades\Storage;

Route::get('/storage/{path}', function ($path) {
    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }
    return Storage::disk('public')->response($path);
})->where('path', '.*');
