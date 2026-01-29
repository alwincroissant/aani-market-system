<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MarketMapController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminMapController;
use App\Http\Controllers\VendorProductController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PickupManagerController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VendorProfileController;

// Public Routes
Route::get('/', [MarketMapController::class, 'index'])->name('home')->middleware('redirect.admins');
Route::get('/shops', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{vendor_id}', [ShopController::class, 'show'])->name('shop.show');
Route::get('/product/{product_id}', [ShopController::class, 'product'])->name('shop.product');
Route::get('/register', [AuthController::class, 'showRegister'])->name('auth.register');
Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.login');
Route::get('/vendor/register', [AuthController::class, 'showVendorRegister'])->name('vendor.register');

Route::post('/user/register', [AuthController::class, 'register'])->name('user.register');
Route::post('signin', [AuthController::class, 'postSignin'])->name('user.signin');
Route::post('/vendor/register', [AuthController::class, 'registerVendor'])->name('vendor.register.submit');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::get('logout', [AuthController::class, 'logout'])->name('logout');

    // Cart Routes (authenticated users only)
    Route::get('/cart', [CartController::class, 'view'])->name('cart.view');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::put('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/destroy', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::delete('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
    Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');

    // Customer Order Routes
    Route::get('/my-orders', [CustomerOrderController::class, 'index'])->name('customer.orders.index');
    Route::get('/my-orders/{orderReference}', [CustomerOrderController::class, 'show'])->name('customer.orders.show');

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/addresses', [ProfileController::class, 'addresses'])->name('profile.addresses');
    Route::post('/profile/addresses', [ProfileController::class, 'storeAddress'])->name('profile.addresses.store');
    Route::put('/profile/addresses/{id}', [ProfileController::class, 'updateAddress'])->name('profile.addresses.update');
    Route::delete('/profile/addresses/{id}', [ProfileController::class, 'deleteAddress'])->name('profile.addresses.delete');
    Route::get('/profile/orders', [ProfileController::class, 'orders'])->name('profile.orders');
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');

    // Vendor Profile Routes
    Route::get('/vendor/profile', [VendorProfileController::class, 'index'])->name('vendor.profile.index');
    Route::put('/vendor/profile', [VendorProfileController::class, 'update'])->name('vendor.profile.update');

    // Checkout Routes (authenticated users only)
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'index'])->name('checkout.index.post');
    Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
    
    // Pickup Manager Routes
    Route::middleware(['role:pickup_manager'])->prefix('pickup-manager')->name('pickup-manager.')->group(function () {
        Route::get('/', [PickupManagerController::class, 'index'])->name('index');
        Route::post('/verify-pickup-code', [PickupManagerController::class, 'verifyPickupCode'])->name('verifyPickupCode');
        Route::post('/mark-pickup-used', [PickupManagerController::class, 'markPickupCodeUsed'])->name('markPickupUsed');
        Route::get('/search-orders', [PickupManagerController::class, 'searchOrders'])->name('searchOrders');
    });
    
    // Admin Routes Group
    Route::middleware(['role:administrator'])->prefix('admin')->name('admin.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard.index');
        
        // User Management
        Route::resource('users', AdminUserController::class);
        
        // Vendor Management
        Route::resource('vendors', AdminUserController::class);
        
        // Product Management
        Route::resource('products', VendorProductController::class);
        
        // Order Management
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::put('/orders/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');
        Route::post('/orders/verify-pickup-code', [AdminOrderController::class, 'verifyPickupCode'])->name('orders.verifyPickupCode');
        Route::post('/orders/mark-pickup-used', [AdminOrderController::class, 'markPickupCodeUsed'])->name('orders.markPickupUsed');
        
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
