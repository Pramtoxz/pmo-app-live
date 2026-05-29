<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PartController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\FilterController;

// Internal cron endpoint — no auth, secret key only
Route::post('/internal/refresh-cache', function (\Illuminate\Http\Request $request) {
    $key = env('INTERNAL_CRON_KEY', '');
    if (empty($key) || $request->header('X-Internal-Key') !== $key) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    // Kirim response 200 dulu, job jalan setelah koneksi client ditutup
    response()->json(['message' => 'Cache refresh dimulai di background'])->send();

    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }

    set_time_limit(0);
    ignore_user_abort(true);
    (new \App\Jobs\RefreshCollectionCache)->handle();
    exit(0);
});

// Public routes with auth throttle
Route::middleware('throttle:auth')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/verify-otp', [AuthController::class, 'verifyOTP']);
});

// OTP requests with stricter throttle
Route::post('/auth/request-otp', [AuthController::class, 'requestOTP'])->middleware('throttle:otp');

use App\Http\Controllers\Api\CollectionController;

// Protected routes with API throttle
Route::middleware(['auth:api', 'throttle:api'])->group(function () {
    // Auth
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile'])->middleware('verify.collection.pin');
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    
    // Parts
    Route::get('/parts', [PartController::class, 'index']);
    Route::get('/parts/{partNumber}', [PartController::class, 'show']);
    Route::get('/parts/{partNumber}/stock', [OrderController::class, 'checkStock']);
    
    // Filters
    Route::get('/filters/vehicle-types', [FilterController::class, 'getVehicleTypes']);
    Route::get('/filters/categories', [FilterController::class, 'getCategories']);
    
    // Cart
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/add', [CartController::class, 'add']);
        Route::put('/{id}', [CartController::class, 'update']);
        Route::delete('/{id}', [CartController::class, 'destroy']);
        Route::delete('/clear', [CartController::class, 'clear']);
        Route::post('/checkout', [OrderController::class, 'checkout'])->middleware('throttle:checkout');
    });
    
    // Orders 
    Route::get('/orders', [OrderController::class, 'history']);
    Route::get('/orders/{noSo}/back-order', [OrderController::class, 'backOrder'])->where('noSo', '.*');
    Route::get('/orders/{noSo}', [OrderController::class, 'detail'])->where('noSo', '.*');
    
    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    
    // Campaigns
    Route::get('/campaigns', [CampaignController::class, 'index']);
    Route::get('/campaigns/my-achievement', [CampaignController::class, 'myAchievement']);
    Route::get('/campaigns/{id}', [CampaignController::class, 'show']);
    
    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::put('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::put('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::post('/test', [NotificationController::class, 'sendTest']);
        Route::post('/fcm-token', [NotificationController::class, 'updateFcmToken']); // Alias
    });
    
    // FCM Token
    Route::post('/fcm/update-token', [NotificationController::class, 'updateFcmToken']);
    Route::post('/notifications/update-token', [NotificationController::class, 'updateFcmToken']); // Additional alias
    
    // Collections
    Route::prefix('collections')->group(function () {
        Route::get('/pin/status', [CollectionController::class, 'checkPinStatus']);
        Route::post('/pin/setup', [CollectionController::class, 'setupPin']);
        Route::post('/pin/change', [CollectionController::class, 'changePin']);
        Route::post('/pin/verify', [CollectionController::class, 'verifyPin']);
        
        Route::middleware('verify.collection.pin')->group(function () {
            Route::get('/', [CollectionController::class, 'index']);
            Route::get('/summary', [CollectionController::class, 'summary']);
            Route::get('/reminders', [CollectionController::class, 'reminders']);
            Route::get('/{noFaktur}', [CollectionController::class, 'detail'])->where('noFaktur', '.*');
        });
    });
});
