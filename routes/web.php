<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StorageLocationController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ============================================================
// МАРШРУТЫ, ТРЕБУЮЩИЕ АВТОРИЗАЦИИ
// ============================================================
Route::middleware(['auth'])->group(function () {
    
    // --------------------------------------------------------
    // ДАШБОРД (главная после входа)
    // --------------------------------------------------------
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
    
    // --------------------------------------------------------
    // ПРОДУКТЫ (товары) - доступны менеджерам и админам
    // --------------------------------------------------------
    Route::middleware(['role:manager'])->prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{product}', [ProductController::class, 'show'])->name('show');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
    });
    
    // --------------------------------------------------------
    // МЕСТА ХРАНЕНИЯ - доступны менеджерам и админам
    // --------------------------------------------------------
    Route::middleware(['role:manager'])->prefix('locations')->name('locations.')->group(function () {
        Route::get('/', [StorageLocationController::class, 'index'])->name('index');
        Route::get('/create', [StorageLocationController::class, 'create'])->name('create');
        Route::post('/', [StorageLocationController::class, 'store'])->name('store');
        Route::get('/{location}', [StorageLocationController::class, 'show'])->name('show');
        Route::get('/{location}/edit', [StorageLocationController::class, 'edit'])->name('edit');
        Route::put('/{location}', [StorageLocationController::class, 'update'])->name('update');
        Route::delete('/{location}', [StorageLocationController::class, 'destroy'])->name('destroy');
    });
    
    // --------------------------------------------------------
    // ПАРТИИ - доступны менеджерам и админам
    // --------------------------------------------------------
    Route::middleware(['role:manager'])->prefix('batches')->name('batches.')->group(function () {
        Route::get('/', [BatchController::class, 'index'])->name('index');
        Route::get('/create', [BatchController::class, 'create'])->name('create');
        Route::post('/', [BatchController::class, 'store'])->name('store');
        Route::get('/{batch}', [BatchController::class, 'show'])->name('show');
        Route::get('/{batch}/edit', [BatchController::class, 'edit'])->name('edit');
        Route::put('/{batch}', [BatchController::class, 'update'])->name('update');
        Route::delete('/{batch}', [BatchController::class, 'destroy'])->name('destroy');
    });
    
    // --------------------------------------------------------
    // СКЛАДСКИЕ ОПЕРАЦИИ (доступны кладовщикам и выше)
    // --------------------------------------------------------
    Route::middleware(['role:worker'])->prefix('movements')->name('movements.')->group(function () {
        // Список операций
        Route::get('/', [MovementController::class, 'index'])->name('index');
        
        // ПРИЁМКА товаров
        Route::get('/receipt', [MovementController::class, 'createReceipt'])->name('receipt.create');
        Route::post('/receipt', [MovementController::class, 'storeReceipt'])->name('receipt.store');
        
        // ОТГРУЗКА товаров
        Route::get('/shipment', [MovementController::class, 'createShipment'])->name('shipment.create');
        Route::post('/shipment', [MovementController::class, 'storeShipment'])->name('shipment.store');
        
        // ПЕРЕМЕЩЕНИЕ между ячейками
        Route::get('/transfer', [MovementController::class, 'createTransfer'])->name('transfer.create');
        Route::post('/transfer', [MovementController::class, 'storeTransfer'])->name('transfer.store');
        
        // СПИСАНИЕ товаров
        Route::get('/write-off', [MovementController::class, 'createWriteOff'])->name('write-off.create');
        Route::post('/write-off', [MovementController::class, 'storeWriteOff'])->name('write-off.store');
        
        // Просмотр конкретного движения
        Route::get('/{movement}', [MovementController::class, 'show'])->name('show');
    });
    
    // --------------------------------------------------------
    // ОТЧЁТЫ (доступны менеджерам и админам)
    // --------------------------------------------------------
    Route::middleware(['role:manager'])->prefix('reports')->name('reports.')->group(function () {
        Route::get('/stock', [ReportController::class, 'stock'])->name('stock');
        Route::get('/movements', [ReportController::class, 'movements'])->name('movements');
        Route::get('/expiring', [ReportController::class, 'expiring'])->name('expiring');
        Route::get('/expired', [ReportController::class, 'expired'])->name('expired');
    });
    
    // --------------------------------------------------------
    // АДМИН-ПАНЕЛЬ (только для администраторов)
    // --------------------------------------------------------
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', App\Http\Controllers\Admin\UserController::class);
        Route::resource('roles', App\Http\Controllers\Admin\RoleController::class);
    });
});

require __DIR__.'/auth.php';
