<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MarketMapController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminMapController;
use App\Http\Controllers\VendorProductController;

// Public Routes
Route::get('/', [MarketMapController::class, 'index'])->name('home');
Route::view('/register', 'auth.register')->name('auth.register');
Route::view('/user/login', 'auth.login')->name('auth.login');

Route::post('/user/register', [AuthController::class, 'register'])->name('user.register');
Route::post('signin', [AuthController::class, 'postSignin'])->name('user.signin');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::get('logout', [AuthController::class, 'logout'])->name('logout');
    
    // Admin Routes
    Route::get('/admin/map', [AdminMapController::class, 'index'])->name('admin.map.index');
    Route::post('/admin/map/upload', [AdminMapController::class, 'uploadBackground'])->name('admin.map.upload');
    Route::post('/admin/map/stalls', [AdminMapController::class, 'storeStall'])->name('admin.map.stalls.store');
    Route::put('/admin/map/stalls/{id}', [AdminMapController::class, 'updateStall'])->name('admin.map.stalls.update');
    Route::delete('/admin/map/stalls/{id}', [AdminMapController::class, 'deleteStall'])->name('admin.map.stalls.delete');
    
    // Vendor Product Routes
    Route::resource('products', VendorProductController::class);
    Route::get('/products/{id}/restore', [VendorProductController::class, 'restore'])->name('products.restore');
});
