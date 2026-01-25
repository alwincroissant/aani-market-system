<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MarketMapController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminMapController;
use App\Http\Controllers\VendorProductController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminReportController;

// Public Routes
Route::get('/', [MarketMapController::class, 'index'])->name('home')->middleware('redirect.admins');
Route::get('/shops', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{vendor_id}', [ShopController::class, 'show'])->name('shop.show');
Route::get('/product/{product_id}', [ShopController::class, 'product'])->name('shop.product');
Route::get('/register', [AuthController::class, 'showRegister'])->name('auth.register');
Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.login');

Route::post('/user/register', [AuthController::class, 'register'])->name('user.register');
Route::post('signin', [AuthController::class, 'postSignin'])->name('user.signin');

// Cart Routes (Public - can be used by guests)
Route::get('/cart', [CartController::class, 'view'])->name('cart.view');
Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');
Route::put('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

// Checkout Routes
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');
Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::get('logout', [AuthController::class, 'logout'])->name('logout');
    
    // Admin Routes Group
    Route::middleware(['role:administrator'])->prefix('admin')->name('admin.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard.index');
        
        // User Management
        Route::resource('users', AdminUserController::class);
        
        // Map Management
        Route::get('/map', [AdminMapController::class, 'index'])->name('map.index');
        Route::post('/map/upload', [AdminMapController::class, 'uploadBackground'])->name('map.upload');
        Route::post('/map/stalls', [AdminMapController::class, 'storeStall'])->name('map.stalls.store');
        Route::put('/map/stalls/{id}', [AdminMapController::class, 'updateStall'])->name('map.stalls.update');
        Route::delete('/map/stalls/{id}', [AdminMapController::class, 'deleteStall'])->name('map.stalls.delete');
        
        // Reports
        Route::get('/reports/sales', [AdminReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/attendance', [AdminReportController::class, 'attendance'])->name('reports.attendance');
    });
    
    // Vendor Product Routes
    Route::resource('products', VendorProductController::class);
    Route::get('/products/{id}/restore', [VendorProductController::class, 'restore'])->name('products.restore');
});
