<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PopularPartController;
use App\Http\Controllers\Admin\SalesSpvController;
use App\Http\Controllers\Admin\PartCategoryImageWebController;
use App\Http\Controllers\Admin\ShopWebController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\NotificationManagementController;
use App\Http\Controllers\Admin\KatalogController as AdminKatalogController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\Auth\LoginController;

Route::get('/', function () {
    if (Auth::check() && Auth::user()->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('login');
});

Route::get('kebijakan-privasi', [LoginController::class, 'showKebijakanPrivasi'])->name('kebijakan_privasi');

// Auth Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/popular-parts', [PopularPartController::class, 'index'])->name('popular-parts.index');
    Route::post('/popular-parts/generate', [PopularPartController::class, 'generate'])->name('popular-parts.generate'); 
    
    // Category Images CRUDs
    Route::resource('category-images', PartCategoryImageWebController::class);
    Route::get('/category-images-api/categories', [PartCategoryImageWebController::class, 'getCategories'])->name('category-images.api.categories');

   Route::get('/shops', [ShopWebController::class, 'index'])->name('shops.index');
    Route::get('/shops/export', [ShopWebController::class, 'export'])->name('shops.export');
    Route::post('/shops/import', [ShopWebController::class, 'import'])->name('shops.import');
    Route::get('/shops/create', [ShopWebController::class, 'create'])->name('shops.create');
    Route::post('/shops', [ShopWebController::class, 'store'])->name('shops.store');
    Route::get('/shops/{kd_toko}', [ShopWebController::class, 'show'])->name('shops.show');
    Route::get('/shops/{kd_toko}/edit', [ShopWebController::class, 'edit'])->name('shops.edit');
    Route::put('/shops/{kd_toko}', [ShopWebController::class, 'update'])->name('shops.update');
    Route::delete('/shops/{kd_toko}', [ShopWebController::class, 'destroy'])->name('shops.destroy');
    Route::post('/shops/{kd_toko}/reset-pin', [ShopWebController::class, 'resetCollectionPin'])->name('shops.reset-pin');
    Route::post('/collection/refresh-cache', [ShopWebController::class, 'refreshCollectionCache'])->name('collection.refresh-cache');

    Route::get('/sales-spv', [SalesSpvController::class, 'index'])->name('sales-spv.index');
Route::get('/sales-spv/export', [SalesSpvController::class, 'export'])->name('sales-spv.export');
Route::post('/sales-spv/import', [SalesSpvController::class, 'import'])->name('sales-spv.import');
Route::get('/sales-spv/{id}', [SalesSpvController::class, 'show'])->name('sales-spv.show');
Route::delete('/sales-spv/{id}', [SalesSpvController::class, 'destroy'])->name('sales-spv.destroy');
Route::resource('campaigns', CampaignController::class);

    // Notification Management
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationManagementController::class, 'index'])->name('index');
        Route::post('/send-to-user', [NotificationManagementController::class, 'sendToUser'])->name('send-to-user');
        Route::post('/send-to-shop', [NotificationManagementController::class, 'sendToShop'])->name('send-to-shop');
        Route::post('/send-announcement', [NotificationManagementController::class, 'sendAnnouncement'])->name('send-announcement');
        Route::post('/send-campaign', [NotificationManagementController::class, 'sendCampaignNotification'])->name('send-campaign');
        Route::post('/send-stock', [NotificationManagementController::class, 'sendStockNotification'])->name('send-stock');
        Route::get('/users-with-token', [NotificationManagementController::class, 'getUsersWithToken'])->name('users-with-token');
        Route::get('/shops', [NotificationManagementController::class, 'getShops'])->name('shops');
    });

    Route::get('/katalog', [AdminKatalogController::class, 'index'])->name('katalog.index');
    Route::get('/katalog/create', [AdminKatalogController::class, 'create'])->name('katalog.create');
    Route::post('/katalog', [AdminKatalogController::class, 'store'])->name('katalog.store');
    Route::get('/katalog/{id}/edit', [AdminKatalogController::class, 'edit'])->name('katalog.edit');
    Route::put('/katalog/{id}', [AdminKatalogController::class, 'update'])->name('katalog.update');
    Route::delete('/katalog/{id}', [AdminKatalogController::class, 'destroy'])->name('katalog.destroy');

});

Route::get('/katalog-motor', [KatalogController::class, 'index'])->name('katalog.index');
Route::get('/katalog-motor/{type}', [KatalogController::class, 'kategori'])->name('katalog.kategori');
Route::get('/katalog-motor/download/{id}', [KatalogController::class, 'download'])->name('katalog.download');
Route::get('/katalog-motor/view/{id}', [KatalogController::class, 'view'])->name('katalog.view');
