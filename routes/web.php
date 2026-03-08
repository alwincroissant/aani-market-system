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
use App\Http\Controllers\VendorProfileController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\VendorDashboardController;
use App\Http\Controllers\VendorReportController;
use App\Http\Controllers\VendorOrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\StockManagementController;
use App\Http\Controllers\VendorStallPaymentController;
use App\Http\Controllers\VendorWalkInSaleController;
use App\Http\Controllers\PickupManagerController;

// Public Routes
Route::get('/', [MarketMapController::class, 'index'])->name('home');
Route::get('/shops', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{vendor_id}', [ShopController::class, 'show'])->name('shop.show');
Route::get('/product/{product_id}', [ShopController::class, 'product'])->name('shop.product');
Route::get('/auth/login', [AuthController::class, 'showLogin'])->name('auth.login');
Route::get('/auth/register', [AuthController::class, 'showRegister'])->name('auth.register');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/vendor/register', [AuthController::class, 'showVendorRegister'])->name('vendor.register');

Route::post('/user/register', [AuthController::class, 'register'])->name('user.register');
Route::post('signin', [AuthController::class, 'postSignin'])->name('user.signin');
Route::post('/vendor/register', [AuthController::class, 'registerVendor'])->name('vendor.register.submit');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::get('logout', [AuthController::class, 'logout'])->name('logout');
    
    // Customer Order Routes
    Route::get('/customer/orders', [CustomerOrderController::class, 'index'])->name('customer.orders.index');
    Route::get('/customer/orders/{orderReference}', [CustomerOrderController::class, 'show'])->name('customer.orders.show');
    Route::post('/customer/orders/{orderReference}/cancel', [CustomerOrderController::class, 'cancel'])->name('customer.orders.cancel');
    Route::post('/customer/orders/{orderReference}/complete', [CustomerOrderController::class, 'markComplete'])->name('customer.orders.mark-complete');
    
    // Cart Routes (authenticated users only)
    Route::get('/cart', [CartController::class, 'getCart'])->name('getCart');
    Route::get('/cart/add/{id}', [CartController::class, 'addToCart'])->name('addToCart');
    Route::get('/cart/reduce/{id}', [CartController::class, 'getReduceByOne'])->name('reduceByOne');
    Route::get('/cart/remove/{id}', [CartController::class, 'getRemoveItem'])->name('removeItem');

    /*
    |--------------------------------------------------------------------------
    | Checkout Routes
    |--------------------------------------------------------------------------
    */
    Route::match(['get', 'post'], '/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/process', [CheckoutController::class, 'postCheckout'])->name('checkout.process');
    Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
    
    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */

    // Admin Routes Group
    Route::middleware(['role:administrator'])->prefix('admin')->name('admin.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard.index');
        
        // User Management
        Route::post('users/assign-stall', [AdminUserController::class, 'assignStallAndActivate'])->name('users.assign-stall');
        Route::resource('users', AdminUserController::class);
        Route::put('users/{id}/deactivate', [AdminUserController::class, 'deactivate'])->name('users.deactivate');
        Route::put('users/{id}/activate', [AdminUserController::class, 'activate'])->name('users.activate');
        
        // Map Management
        Route::get('/map', [AdminMapController::class, 'index'])->name('map.index');
        Route::post('/map/upload', [AdminMapController::class, 'uploadBackground'])->name('map.upload');
        Route::get('/map/stalls-data', [AdminMapController::class, 'getStallsData'])->name('map.stalls-data');
        Route::post('/map/stalls', [AdminMapController::class, 'storeStall'])->name('map.stalls.store');
        Route::put('/map/stalls/{id}', [AdminMapController::class, 'updateStall'])->name('map.stalls.update');
        Route::delete('/map/stalls/{id}', [AdminMapController::class, 'deleteStall'])->name('map.stalls.delete');
        
        // Reports
        Route::get('/reports/sales', [AdminReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/attendance', [AdminReportController::class, 'attendance'])->name('reports.attendance');
        
        // Report Exports
        Route::get('/reports/sales/export-pdf', [AdminReportController::class, 'exportSalesPdf'])->name('reports.sales.export-pdf');
        Route::get('/reports/sales/export-csv', [AdminReportController::class, 'exportSalesCsv'])->name('reports.sales.export-csv');
        Route::get('/reports/attendance/export-pdf', [AdminReportController::class, 'exportAttendancePdf'])->name('reports.attendance.export-pdf');
        Route::get('/reports/attendance/export-csv', [AdminReportController::class, 'exportAttendanceCsv'])->name('reports.attendance.export-csv');

        // Pickup Operations (admin-owned)
        Route::get('/pickups', [PickupManagerController::class, 'index'])->name('pickups.index');
        Route::post('/pickups/verify-code', [PickupManagerController::class, 'verifyPickupCode'])->name('pickups.verify-code');
        Route::post('/pickups/mark-used', [PickupManagerController::class, 'markPickupCodeUsed'])->name('pickups.mark-used');
        Route::get('/pickups/search-orders', [PickupManagerController::class, 'searchOrders'])->name('pickups.search-orders');
    });
    
    // Vendor Order Routes
    Route::get('/vendor/orders', [VendorOrderController::class, 'index'])->name('vendor.orders.index');
    Route::get('/vendor/orders/{id}', [VendorOrderController::class, 'show'])->name('vendor.orders.show');
    Route::put('/vendor/orders/{orderId}/batch-update-status', [VendorOrderController::class, 'batchUpdateStatus'])->name('vendor.orders.batch-update-status');
    Route::put('/vendor/orders/items/{itemId}/status', [VendorOrderController::class, 'updateItemStatus'])->name('vendor.orders.items.update-status');
    Route::put('/vendor/orders/items/{itemId}/notes', [VendorOrderController::class, 'updateVendorNotes'])->name('vendor.orders.items.update-notes');
    Route::post('/vendor/orders/{orderId}/pickup-code', [VendorOrderController::class, 'generatePickupCode'])->name('vendor.orders.pickup-code');

    // Vendor Product Routes (prefixed with /vendor)
    Route::prefix('vendor/products')->name('vendor.products.')->group(function () {
        Route::get('/', [VendorProductController::class, 'index'])->name('index');
        Route::get('/create', [VendorProductController::class, 'create'])->name('create');
        Route::post('/', [VendorProductController::class, 'store'])->name('store');
        Route::get('/{id}', [VendorProductController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [VendorProductController::class, 'edit'])->name('edit');
        Route::put('/{id}', [VendorProductController::class, 'update'])->name('update');
        Route::delete('/{id}', [VendorProductController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/restore', [VendorProductController::class, 'restore'])->name('restore');
    });
    
    // Keep the original products routes for backward compatibility
    Route::resource('products', VendorProductController::class);
    Route::post('/products/batch-update', [VendorProductController::class, 'batchUpdate'])->name('products.batch-update');
    Route::post('/products/batch-delete', [VendorProductController::class, 'batchDelete'])->name('products.batch-delete');
    Route::post('/products/batch-restore', [VendorProductController::class, 'batchRestore'])->name('products.batch-restore');
    Route::post('/products/batch-force-delete', [VendorProductController::class, 'batchForceDelete'])->name('products.batch-force-delete');
    Route::get('/products/{id}/restore', [VendorProductController::class, 'restore'])->name('products.restore');
    
    // Vendor Report Routes
    Route::get('/vendor/reports/sales', [VendorReportController::class, 'sales'])->name('vendor.reports.sales');
    Route::get('/vendor/reports/products', [VendorReportController::class, 'products'])->name('vendor.reports.products');
    Route::get('/vendor/reports/orders', [VendorReportController::class, 'orders'])->name('vendor.reports.orders');
    
    // Vendor Report Exports
    Route::get('/vendor/reports/sales/export-pdf', [VendorReportController::class, 'exportSalesPdf'])->name('vendor.reports.sales.export-pdf');
    Route::get('/vendor/reports/sales/export-csv', [VendorReportController::class, 'exportSalesCsv'])->name('vendor.reports.sales.export-csv');
    Route::get('/vendor/reports/products/export-pdf', [VendorReportController::class, 'exportProductsPdf'])->name('vendor.reports.products.export-pdf');
    Route::get('/vendor/reports/products/export-csv', [VendorReportController::class, 'exportProductsCsv'])->name('vendor.reports.products.export-csv');
    Route::get('/vendor/reports/orders/export-pdf', [VendorReportController::class, 'exportOrdersPdf'])->name('vendor.reports.orders.export-pdf');
    Route::get('/vendor/reports/orders/export-csv', [VendorReportController::class, 'exportOrdersCsv'])->name('vendor.reports.orders.export-csv');
    
    // Vendor Dashboard Routes (for specific vendor actions)
    Route::get('/vendor/dashboard', [VendorDashboardController::class, 'index'])->name('vendor.dashboard');
    Route::post('/vendor/update-live-status', [VendorDashboardController::class, 'updateLiveStatus'])->name('vendor.update-live-status');
    Route::post('/vendor/upload-banner', [VendorDashboardController::class, 'uploadBanner'])->name('vendor.upload-banner');
    Route::get('/vendor/settings', [VendorDashboardController::class, 'settings'])->name('vendor.settings');
    Route::post('/vendor/update-settings', [VendorDashboardController::class, 'updateSettings'])->name('vendor.update-settings');
    
    Route::post('/vendor/remove-banner', [VendorDashboardController::class, 'removeBanner'])->name('vendor.remove-banner');
    Route::post('/vendor/remove-logo',   [VendorDashboardController::class, 'removeLogo'])->name('vendor.remove-logo');

    // Vendor Walk-In / Physical Sales Routes
    Route::get('/vendor/walk-in-sales', [VendorWalkInSaleController::class, 'index'])->name('vendor.walk-in-sales.index');
    Route::get('/vendor/walk-in-sales/create', [VendorWalkInSaleController::class, 'create'])->name('vendor.walk-in-sales.create');
    Route::post('/vendor/walk-in-sales', [VendorWalkInSaleController::class, 'store'])->name('vendor.walk-in-sales.store');
    Route::get('/vendor/walk-in-sales/{id}/edit', [VendorWalkInSaleController::class, 'edit'])->name('vendor.walk-in-sales.edit');
    Route::put('/vendor/walk-in-sales/{id}', [VendorWalkInSaleController::class, 'update'])->name('vendor.walk-in-sales.update');
    Route::delete('/vendor/walk-in-sales/{id}/archive', [VendorWalkInSaleController::class, 'destroy'])->name('vendor.walk-in-sales.archive');
    Route::post('/vendor/walk-in-sales/{id}/restore', [VendorWalkInSaleController::class, 'restore'])->name('vendor.walk-in-sales.restore');
    Route::delete('/vendor/walk-in-sales/{id}/delete', [VendorWalkInSaleController::class, 'forceDestroy'])->name('vendor.walk-in-sales.force-destroy');
    Route::delete('/vendor/walk-in-sales/{id}', [VendorWalkInSaleController::class, 'destroy'])->name('vendor.walk-in-sales.destroy');
    Route::get('/vendor/walk-in-sales/product-info/{productId}', [VendorWalkInSaleController::class, 'productInfo'])->name('vendor.walk-in-sales.product-info');

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/change-password', [ProfileController::class, 'showChangePassword'])->name('profile.change-password.show');
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');
    Route::get('/profile/addresses', [ProfileController::class, 'addresses'])->name('profile.addresses');
    Route::post('/profile/addresses', [ProfileController::class, 'storeAddress'])->name('profile.addresses.store');
    Route::put('/profile/addresses/{id}', [ProfileController::class, 'updateAddress'])->name('profile.addresses.update');
    Route::delete('/profile/addresses/{id}', [ProfileController::class, 'deleteAddress'])->name('profile.addresses.delete');
    Route::get('/profile/orders', [ProfileController::class, 'orders'])->name('profile.orders');
    
    // Stock Management Routes
    Route::get('/stock', [StockManagementController::class, 'index'])->name('stock.index');
    Route::get('/stock/recent-changes', [StockManagementController::class, 'recentChanges'])->name('stock.recent-changes');
    Route::get('/stock/{product}/edit', [StockManagementController::class, 'edit'])->name('stock.edit');
    Route::put('/stock/{product}', [StockManagementController::class, 'update'])->name('stock.update');
    Route::post('/stock/bulk-update', [StockManagementController::class, 'bulkUpdate'])->name('stock.bulk-update');
});

// Vendor Stall Payments
Route::middleware(['auth', 'role:vendor'])->group(function () {
    Route::get('/vendor/stall-payments', [\App\Http\Controllers\VendorStallPaymentController::class, 'index'])->name('vendor.stall-payments');
    Route::post('/vendor/stall-payments/pay/{id}', [\App\Http\Controllers\VendorStallPaymentController::class, 'pay'])->name('vendor.stall-payments.pay');
});

// Admin Stall Payments
Route::middleware(['auth', 'role:administrator'])->group(function () {
    Route::get('/admin/stall-payments', [\App\Http\Controllers\AdminStallPaymentController::class, 'index'])->name('admin.stall-payments');
    Route::post('/admin/stall-payments', [\App\Http\Controllers\AdminStallPaymentController::class, 'store'])->name('admin.stall-payments.store');
    Route::post('/admin/stall-payments/mark-overdue', [\App\Http\Controllers\AdminStallPaymentController::class, 'markOverdue'])->name('admin.stall-payments.mark-overdue');
});