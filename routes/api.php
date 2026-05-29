<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\TargetSalesController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\IndentController;
use App\Http\Controllers\Api\TargetProspekController;
use App\Http\Controllers\Api\ProspekController;
use App\Http\Controllers\Api\ActualSpkController;
use App\Http\Controllers\Api\PerformanceController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ActualSalesController;
use App\Http\Controllers\Api\MasterController;

RateLimiter::for('auth', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip());
});

RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

RateLimiter::for('write', function (Request $request) {
    return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
});

Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:auth');
Route::post('/auth/biometric/login', [AuthController::class, 'biometricLogin'])->middleware('throttle:auth');

Route::middleware(['auth:api', 'throttle:api'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::get('/auth/devices', [AuthController::class, 'devices']);
    Route::post('/auth/biometric/register', [AuthController::class, 'biometricRegister']);
    Route::post('/auth/biometric/revoke', [AuthController::class, 'biometricRevoke']);
    
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/target-sales', [TargetSalesController::class, 'index']);
    Route::get('/stock', [StockController::class, 'index']);
    Route::get('/indent', [IndentController::class, 'index']);
    Route::get('/target-prospek', [TargetProspekController::class, 'index']);
    Route::get('/prospek', [ProspekController::class, 'index']);
    Route::get('/prospek/detail', [ProspekController::class, 'show']);
    Route::get('/prospek/cek-leads', [ProspekController::class, 'cekLeads']);
    Route::get('/actual-spk', [ActualSpkController::class, 'index']);
    Route::get('/actual-spk/detail', [ActualSpkController::class, 'show']);
    Route::get('/actual-sales', [ActualSalesController::class, 'index']);
    Route::get('/actual-sales/detail', [ActualSalesController::class, 'show']);
    Route::get('/performance', [PerformanceController::class, 'index']);
    Route::get('/master/sumber-data', [MasterController::class, 'sumberData']);
    Route::get('/master/tipe-konsumen', [MasterController::class, 'tipeKonsumen']);
    Route::get('/master/rencana-pembayaran', [MasterController::class, 'rencanaPembayaran']);
    Route::get('/master/tipe-kendaraan', [MasterController::class, 'tipeKendaraan']);
    Route::get('/master/warna-kendaraan', [MasterController::class, 'warnaKendaraan']);
    Route::get('/master/janji-temu', [MasterController::class, 'janjiTemu']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update'])->middleware('throttle:write');
});

Route::middleware(['auth:api', 'throttle:write'])->group(function () {
    Route::post('/prospek', [ProspekController::class, 'store']);
    Route::post('/prospek/generate-leads', [ProspekController::class, 'generateLeads']);
    Route::put('/prospek/{id}', [ProspekController::class, 'update'])->where('id', '.*');
    Route::delete('/prospek/{id}', [ProspekController::class, 'destroy'])->where('id', '.*');
    Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto']);
});

Route::get('/health', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API is running',
        'timestamp' => now()->toDateTimeString()
    ]);
});
