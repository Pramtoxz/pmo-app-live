<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Update FCM Token
     */
    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        try {
            $user = $request->user();
            $user->fcm_token = $request->fcm_token;
            $user->save();

            return ApiResponse::success([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'fcm_token' => $user->fcm_token,
                'shop_code' => $user->fk_toko,
                'role' => $user->role,
            ], 'FCM token updated successfully');
        } catch (\Exception $e) {
            Log::error('FCM Token Update Error: ' . $e->getMessage());
            return ApiResponse::error('Failed to update FCM token', 500);
        }
    }

    /**
     * Get all notifications for authenticated user
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $notifications = Notification::where('kd_toko', $user->fk_toko)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return ApiResponse::success(
            NotificationResource::collection($notifications),
            'Notifications retrieved successfully'
        );
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();
        
        $count = Notification::where('kd_toko', $user->fk_toko)
            ->where('sudah_dibaca', false)
            ->count();

        return ApiResponse::success(['count' => $count], 'Unread count retrieved successfully');
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();
        
        $notification = Notification::where('id', $id)
            ->where('kd_toko', $user->fk_toko)
            ->firstOrFail();

        $notification->sudah_dibaca = true;
        $notification->save();

        return ApiResponse::success(
            new NotificationResource($notification),
            'Notification marked as read'
        );
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        
        Notification::where('kd_toko', $user->fk_toko)
            ->where('sudah_dibaca', false)
            ->update(['sudah_dibaca' => true]);

        return ApiResponse::success(null, 'All notifications marked as read');
    }

    /**
     * Send test notification (for testing purposes)
     */
    public function sendTest(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        $user = $request->user();

        if (!$user->fcm_token) {
            Log::warning('FCM Test Failed: User has no FCM token', ['user_id' => $user->id]);
            return ApiResponse::error('FCM token not found. Please update your FCM token first.', 400);
        }

        Log::info('Sending test notification', [
            'user_id' => $user->id,
            'fcm_token' => substr($user->fcm_token, 0, 20) . '...',
            'title' => $request->title,
        ]);

        $result = $this->firebaseService->sendToDevice(
            $user->fcm_token,
            $request->title,
            $request->body,
            ['type' => 'test', 'notification_id' => '0']
        );

        if ($result['success']) {
            Log::info('Test notification sent successfully', ['user_id' => $user->id]);
            return ApiResponse::success($result, 'Test notification sent successfully');
        }

        Log::error('Test notification failed', [
            'user_id' => $user->id,
            'error' => $result['message']
        ]);

        return ApiResponse::error('Failed to send notification: ' . $result['message'], 500);
    }

    /**
     * Send notification to specific user (Admin only)
     */
    public function sendToUser(Request $request)
    {
        // Authorization check - only admin can send notifications
        if ($request->user()->role !== 'admin') {
            return ApiResponse::error('Unauthorized. Only admin can send notifications.', 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string',
            'body' => 'required|string',
            'type' => 'nullable|string',
            'data' => 'nullable|array',
        ]);

        $user = User::findOrFail($request->user_id);

        if (!$user->fcm_token) {
            return ApiResponse::error('User does not have FCM token', 400);
        }

        // Save to database
        $notification = Notification::create([
            'kd_toko' => $user->fk_toko,
            'judul' => $request->title,
            'pesan' => $request->body,
            'tipe' => $request->type ?? 'general',
            'sudah_dibaca' => false,
        ]);

        // Send push notification
        $data = $request->data ?? [];
        $data['type'] = $request->type ?? 'general';
        $data['notification_id'] = (string) $notification->id;

        $result = $this->firebaseService->sendToDevice(
            $user->fcm_token,
            $request->title,
            $request->body,
            $data
        );

        if ($result['success']) {
            return ApiResponse::success($result, 'Notification sent successfully');
        }

        return ApiResponse::error($result['message'], 500);
    }

    /**
     * Send notification to shop (Admin only)
     */
    public function sendToShop(Request $request)
    {
        // Authorization check - only admin can send notifications
        if ($request->user()->role !== 'admin') {
            return ApiResponse::error('Unauthorized. Only admin can send notifications.', 403);
        }

        $request->validate([
            'kd_toko' => 'required|string',
            'title' => 'required|string',
            'body' => 'required|string',
            'type' => 'nullable|string',
            'data' => 'nullable|array',
        ]);

        // Save to database
        $notification = Notification::create([
            'kd_toko' => $request->kd_toko,
            'judul' => $request->title,
            'pesan' => $request->body,
            'tipe' => $request->type ?? 'general',
            'sudah_dibaca' => false,
        ]);

        // Get all users from this shop
        $users = User::where('fk_toko', $request->kd_toko)
            ->whereNotNull('fcm_token')
            ->get();

        if ($users->isEmpty()) {
            return ApiResponse::error('No users with FCM token found for this shop', 400);
        }

        $fcmTokens = $users->pluck('fcm_token')->toArray();
        $data = $request->data ?? [];
        $data['type'] = $request->type ?? 'general';
        $data['notification_id'] = (string) $notification->id;

        $result = $this->firebaseService->sendToMultipleDevices(
            $fcmTokens,
            $request->title,
            $request->body,
            $data
        );

        if ($result['success']) {
            return ApiResponse::success($result, 'Notification sent to shop successfully');
        }

        return ApiResponse::error($result['message'], 500);
    }
}
