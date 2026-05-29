<?php

namespace App\Helpers;

use App\Models\Notification;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Log;

class NotificationHelper
{
    /**
     * Send notification to user
     */
    public static function sendToUser($userId, $title, $message, $type = 'general', $data = [])
    {
        try {
            $user = User::find($userId);
            
            if (!$user) {
                Log::warning('NotificationHelper: User not found', ['user_id' => $userId]);
                return ['success' => false, 'message' => 'User not found'];
            }

            // Save to database
            $notification = Notification::create([
                'kd_toko' => $user->fk_toko,
                'judul' => $title,
                'pesan' => $message,
                'tipe' => $type,
                'sudah_dibaca' => false,
            ]);

            Log::info('Notification saved to database', [
                'notification_id' => $notification->id,
                'user_id' => $userId,
                'type' => $type
            ]);

            // Send push notification if user has FCM token
            if ($user->fcm_token) {
                $firebase = new FirebaseService();
                
                // Ensure required fields
                $data['type'] = $type;
                $data['notification_id'] = (string) $notification->id;
                
                $result = $firebase->sendToDevice(
                    $user->fcm_token,
                    $title,
                    $message,
                    $data
                );

                // Handle invalid token - soft delete
                if (!$result['success'] && isset($result['error_type']) && 
                    in_array($result['error_type'], ['invalid_token', 'token_not_found'])) {
                    Log::warning('Removing invalid FCM token', ['user_id' => $userId]);
                    $user->fcm_token = null;
                    $user->save();
                }

                return $result;
            }

            Log::info('Notification saved but not sent (no FCM token)', ['user_id' => $userId]);
            return ['success' => true, 'message' => 'Notification saved but not sent (no FCM token)'];
        } catch (\Exception $e) {
            Log::error('NotificationHelper Error: ' . $e->getMessage(), [
                'user_id' => $userId,
                'type' => $type
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send notification to shop (all users in shop)
     */
    public static function sendToShop($kdToko, $title, $message, $type = 'general', $data = [])
    {
        try {
            // Save to database
            $notification = Notification::create([
                'kd_toko' => $kdToko,
                'judul' => $title,
                'pesan' => $message,
                'tipe' => $type,
                'sudah_dibaca' => false,
            ]);

            Log::info('Shop notification saved to database', [
                'notification_id' => $notification->id,
                'shop_code' => $kdToko,
                'type' => $type
            ]);

            // Get all users with FCM token
            $users = User::where('fk_toko', $kdToko)
                ->whereNotNull('fcm_token')
                ->get();

            if ($users->isEmpty()) {
                Log::info('No users with FCM token found', ['shop_code' => $kdToko]);
                return ['success' => true, 'message' => 'Notification saved but no users with FCM token'];
            }

            $fcmTokens = $users->pluck('fcm_token')->toArray();
            $firebase = new FirebaseService();
            
            // Ensure required fields
            $data['type'] = $type;
            $data['notification_id'] = (string) $notification->id;

            $result = $firebase->sendToMultipleDevices(
                $fcmTokens,
                $title,
                $message,
                $data
            );

            return $result;
        } catch (\Exception $e) {
            Log::error('NotificationHelper Shop Error: ' . $e->getMessage(), [
                'shop_code' => $kdToko,
                'type' => $type
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send order notification
     */
    public static function sendOrderNotification($userId, $orderNumber, $status)
    {
        $titles = [
            'created' => 'Order Berhasil Dibuat',
            'processing' => 'Order Sedang Diproses',
            'shipped' => 'Order Telah Dikirim',
            'delivered' => 'Order Telah Sampai',
            'cancelled' => 'Order Dibatalkan',
        ];

        $messages = [
            'created' => "Order #{$orderNumber} telah berhasil dibuat",
            'processing' => "Order #{$orderNumber} sedang diproses",
            'shipped' => "Order #{$orderNumber} telah dikirim",
            'delivered' => "Order #{$orderNumber} telah sampai di tujuan",
            'cancelled' => "Order #{$orderNumber} telah dibatalkan",
        ];

        return self::sendToUser(
            $userId,
            $titles[$status] ?? 'Update Order',
            $messages[$status] ?? "Order #{$orderNumber} telah diupdate",
            'order',
            ['order_number' => $orderNumber, 'status' => $status]
        );
    }

    /**
     * Send campaign notification
     */
    public static function sendCampaignNotification($userId, $campaignTitle, $campaignId)
    {
        return self::sendToUser(
            $userId,
            'Campaign Baru',
            "Campaign '{$campaignTitle}' tersedia untuk Anda",
            'campaign',
            ['campaign_id' => $campaignId]
        );
    }

    /**
     * Send payment notification
     */
    public static function sendPaymentNotification($userId, $orderNumber)
    {
        return self::sendToUser(
            $userId,
            'Pembayaran Berhasil',
            "Pembayaran untuk order #{$orderNumber} telah dikonfirmasi",
            'payment',
            ['order_number' => $orderNumber]
        );
    }

    /**
     * Send stock notification
     */
    public static function sendStockNotification($kdToko, $partNumber, $partName)
    {
        return self::sendToShop(
            $kdToko,
            'Stok Tersedia',
            "Part {$partNumber} - {$partName} sudah tersedia kembali",
            'stock',
            ['part_number' => $partNumber]
        );
    }

    /**
     * Send delivery notification
     */
    public static function sendDeliveryNotification($kdToko, $doNumber, $soNumber, $status = 'shipped')
    {
        $titles = [
            'shipped' => 'Pesanan Dikirim',
            'delivered' => 'Pesanan Diterima',
            'approved' => 'Pengiriman Disetujui',
            'rejected' => 'Pengiriman Ditolak',
        ];

        $messages = [
            'shipped' => "Pesanan Anda dengan DO #{$doNumber} telah dikirim",
            'delivered' => "Pesanan DO #{$doNumber} telah sampai di tujuan",
            'approved' => "Pengiriman DO #{$doNumber} telah disetujui",
            'rejected' => "Pengiriman DO #{$doNumber} ditolak",
        ];

        return self::sendToShop(
            $kdToko,
            $titles[$status] ?? 'Update Pengiriman',
            $messages[$status] ?? "DO #{$doNumber} telah diupdate",
            'delivery',
            [
                'no_do' => $doNumber,
                'no_so' => $soNumber,
                'status' => $status,
            ]
        );
    }

    /**
     * Send announcement to all shops
     */
    public static function sendAnnouncement($title, $message)
    {
        try {
            $shops = User::whereNotNull('fk_toko')
                ->distinct('fk_toko')
                ->pluck('fk_toko');

            $results = [];
            foreach ($shops as $kdToko) {
                $results[] = self::sendToShop($kdToko, $title, $message, 'announcement');
            }

            return ['success' => true, 'results' => $results];
        } catch (\Exception $e) {
            Log::error('NotificationHelper Announcement Error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
