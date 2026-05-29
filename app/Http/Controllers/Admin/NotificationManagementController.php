<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\NotificationHelper;
use App\Models\User;
use App\Models\Shop;
use Illuminate\Http\Request;

class NotificationManagementController extends Controller
{
    /**
     * Display notification test page
     */
    public function index()
    {
        return view('admin.notifications.index');
    }

    /**
     * Send notification to specific user
     */
    public function sendToUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:100',
            'message' => 'required|string|max:500',
            'type' => 'nullable|in:order,campaign,payment,stock,announcement,general',
        ]);

        $result = NotificationHelper::sendToUser(
            $request->user_id,
            $request->title,
            $request->message,
            $request->type ?? 'general',
            $request->data ?? []
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Notification sent successfully',
                'data' => $result
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 500);
    }

    /**
     * Send notification to specific shop
     */
    public function sendToShop(Request $request)
    {
        $request->validate([
            'kd_toko' => 'required|string',
            'title' => 'required|string|max:100',
            'message' => 'required|string|max:500',
            'type' => 'nullable|in:order,campaign,payment,stock,announcement,general',
        ]);

        $result = NotificationHelper::sendToShop(
            $request->kd_toko,
            $request->title,
            $request->message,
            $request->type ?? 'general',
            $request->data ?? []
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Notification sent to shop successfully',
                'data' => $result
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 500);
    }

    /**
     * Send announcement to all users
     */
    public function sendAnnouncement(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'message' => 'required|string|max:500',
        ]);

        $result = NotificationHelper::sendAnnouncement(
            $request->title,
            $request->message
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Announcement sent to all shops successfully',
                'data' => $result
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 500);
    }

    /**
     * Get list of users with FCM token
     */
    public function getUsersWithToken()
    {
        $users = User::whereNotNull('fcm_token')
            ->with('shop')
            ->select('id', 'name', 'email', 'fk_toko')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Get list of shops
     */
    public function getShops()
    {
        $shops = Shop::select('kd_toko', 'toko')
            ->orderBy('toko')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $shops
        ]);
    }

    /**
     * Send campaign notification to multiple users
     */
    public function sendCampaignNotification(Request $request)
    {
        $request->validate([
            'campaign_id' => 'required|integer',
            'campaign_title' => 'required|string',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $results = [];
        foreach ($request->user_ids as $userId) {
            $result = NotificationHelper::sendCampaignNotification(
                $userId,
                $request->campaign_title,
                $request->campaign_id
            );
            $results[] = $result;
        }

        return response()->json([
            'success' => true,
            'message' => 'Campaign notifications sent',
            'data' => $results
        ]);
    }

    /**
     * Send stock notification to shop
     */
    public function sendStockNotification(Request $request)
    {
        $request->validate([
            'kd_toko' => 'required|string',
            'part_number' => 'required|string',
            'part_name' => 'required|string',
        ]);

        $result = NotificationHelper::sendStockNotification(
            $request->kd_toko,
            $request->part_number,
            $request->part_name
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Stock notification sent successfully',
                'data' => $result
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 500);
    }
}
