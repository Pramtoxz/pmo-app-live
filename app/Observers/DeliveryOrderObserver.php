<?php

namespace App\Observers;

use App\Models\DataPart\DeliveryOrder;
use App\Models\DataPart\SalesOrder;
use App\Helpers\NotificationHelper;
use Illuminate\Support\Facades\Log;

class DeliveryOrderObserver
{
    /**
     * Handle the DeliveryOrder "created" event.
     * Triggered when new POD/DO created from DMS
     */
    public function created(DeliveryOrder $deliveryOrder)
    {
        try {
            Log::info('DeliveryOrder created, sending notification', [
                'no_do' => $deliveryOrder->no_do,
                'fk_so' => $deliveryOrder->fk_so,
            ]);

            // Get related Sales Order to find shop code
            $salesOrder = SalesOrder::where('no_so', $deliveryOrder->fk_so)->first();
            
            if (!$salesOrder) {
                Log::warning('SalesOrder not found for DO', [
                    'no_do' => $deliveryOrder->no_do,
                    'fk_so' => $deliveryOrder->fk_so,
                ]);
                return;
            }

            $kdToko = $salesOrder->fk_customer;
            
            if (!$kdToko) {
                Log::warning('Shop code not found in SalesOrder', [
                    'no_so' => $salesOrder->no_so,
                ]);
                return;
            }

            // Send notification to shop
            $title = 'Pesanan Dikirim';
            $message = "Pesanan Anda dengan DO #{$deliveryOrder->no_do} telah dikirim. Total: Rp " . number_format($deliveryOrder->grand_total, 0, ',', '.');
            
            NotificationHelper::sendToShop(
                $kdToko,
                $title,
                $message,
                'delivery',
                [
                    'no_do' => $deliveryOrder->no_do,
                    'no_so' => $deliveryOrder->fk_so,
                    'grand_total' => $deliveryOrder->grand_total,
                    'tgl_do' => $deliveryOrder->tgl_do?->format('Y-m-d H:i:s'),
                ]
            );

            Log::info('Delivery notification sent successfully', [
                'no_do' => $deliveryOrder->no_do,
                'kd_toko' => $kdToko,
            ]);

        } catch (\Exception $e) {
            Log::error('DeliveryOrderObserver Error: ' . $e->getMessage(), [
                'no_do' => $deliveryOrder->no_do ?? 'unknown',
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Handle the DeliveryOrder "updated" event.
     * Triggered when DO status changed (e.g., approved/rejected)
     */
    public function updated(DeliveryOrder $deliveryOrder)
    {
        try {
            // Check if status_approve_reject changed
            if ($deliveryOrder->isDirty('status_approve_reject')) {
                $status = $deliveryOrder->status_approve_reject;
                
                Log::info('DeliveryOrder status changed', [
                    'no_do' => $deliveryOrder->no_do,
                    'status' => $status,
                ]);

                // Get related Sales Order
                $salesOrder = SalesOrder::where('no_so', $deliveryOrder->fk_so)->first();
                
                if (!$salesOrder || !$salesOrder->fk_customer) {
                    return;
                }

                $kdToko = $salesOrder->fk_customer;

                // Send notification based on status
                if ($status === 'APPROVED') {
                    NotificationHelper::sendToShop(
                        $kdToko,
                        'Pengiriman Disetujui',
                        "Pengiriman DO #{$deliveryOrder->no_do} telah disetujui",
                        'delivery',
                        [
                            'no_do' => $deliveryOrder->no_do,
                            'no_so' => $deliveryOrder->fk_so,
                            'status' => 'approved',
                        ]
                    );
                } elseif ($status === 'REJECTED') {
                    $reason = $deliveryOrder->alasan_approve_reject ?? 'Tidak ada alasan';
                    NotificationHelper::sendToShop(
                        $kdToko,
                        'Pengiriman Ditolak',
                        "Pengiriman DO #{$deliveryOrder->no_do} ditolak. Alasan: {$reason}",
                        'delivery',
                        [
                            'no_do' => $deliveryOrder->no_do,
                            'no_so' => $deliveryOrder->fk_so,
                            'status' => 'rejected',
                            'reason' => $reason,
                        ]
                    );
                }
            }

        } catch (\Exception $e) {
            Log::error('DeliveryOrderObserver Update Error: ' . $e->getMessage(), [
                'no_do' => $deliveryOrder->no_do ?? 'unknown',
            ]);
        }
    }
}
