<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Serial;
use App\Models\DataPart\SalesOrder;
use App\Models\DataPart\SalesOrderDetail;
use App\Models\PublicSchema\Part;
use App\Providers\WhatsAppGateway;
use App\Helpers\NotificationHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function submitOrder($userId)
    {
        return DB::transaction(function () use ($userId) {
            $cart = Cart::where('user_id', $userId)
                ->where('status', 'active')
                ->with(['items.part', 'user.shop'])
                ->first();

            if (!$cart) {
                throw new \Exception('Keranjang belanja kosong atau sudah di-checkout');
            }

            if ($cart->items->isEmpty()) {
                throw new \Exception('Keranjang belanja kosong');
            }

            // 2. Tentukan jenis order (OIL vs non-OIL)
            $jenisOrder = $this->determineOrderType($cart);

            // 3. Generate nomor SO dengan lock
            $noSo = Serial::generateSO();

            // 4. Hitung grand total
            $grandTotal = $cart->items->sum('subtotal');

            // 5. Insert SO header
            $so = SalesOrder::create([
                'no_so' => $noSo,
                'jenis_so' => $jenisOrder,
                'tgl_so' => now(),
                'jenis_pembayaran' => 'Cash',
                'fk_salesman' => $cart->user->shop->fk_sales ?? null,
                'tipe_source' => 'OTHER',
                'fk_toko' => $cart->user->fk_toko,
                'tipe_penjualan' => 'Reguler',
                'tgl_jatuh_tempo' => now()->addMonth(),
                'grand_total' => $grandTotal,
                'status_outstanding' => true,
                'status_approve_reject' => 'Waiting For Approval',
                'keterangan'=> 'Order by PMO'
            ]);

            // 6. Insert SO detail
            foreach ($cart->items as $item) {
                SalesOrderDetail::create([
                    'fk_so' => $noSo,
                    'fk_part' => $item->kode_part,
                    'harga' => $item->harga,
                    'qty_so' => $item->qty,
                    'total_harga' => $item->subtotal,
                    'qty_sisa' => $item->qty,
                    'fk_tipe' => '',
                ]);
            }

            // 7. Kirim notifikasi WA ke grup
            $this->sendOrderNotification($cart, $noSo, $grandTotal);

            // 8. Kirim push notification ke user
            NotificationHelper::sendOrderNotification($userId, $noSo, 'created');

            // 9. Clear cart
            $cart->items()->delete();
            $cart->delete();

            return [
                'no_so' => $noSo,
                'jenis_so' => $jenisOrder,
                'grand_total' => $grandTotal,
                'status' => 'Waiting For Approval',
            ];
        });
    }

    private function sendOrderNotification($cart, $noSo, $grandTotal)
    {
        try {
            $wa = new WhatsAppGateway(2);
            $shopName = $cart->user->shop->nama;
            $userToko = $cart->user->fk_toko;
            $itemCount = $cart->items->count();

            $message = "🔔 *ORDER BARU - PMO*\n\n";
            $message .= "No. SO: *{$noSo}*\n";
            $message .= "Toko: *{$shopName}*\n";
            $message .= "Kode Toko: {$userToko}\n";
            $message .= "Jumlah Item: {$itemCount}\n";
            $message .= "Total: *Rp " . number_format($grandTotal, 0, ',', '.') . "*\n\n";
            $message .= "Waktu Order " . now()->format('d/m/Y H:i:s');
            $wa->sendToGroup(null, $message);

        } catch (\Exception $e) {
            Log::error('Error kirim notifikasi WA: ' . $e->getMessage());
        }
    }

    private function determineOrderType($cart)
    {
        $countOil = 0;
        $countPart = 0;
        $firstItem = null;

        foreach ($cart->items as $item) {
            if (!$firstItem) {
                $firstItem = $item;
            }
            $part = Part::where('kd_part', $item->kode_part)->first();

            if ($part) {
                // Cek apakah fk_detail_sub_kelompok_part == 'OIL'
                if ($part->fk_detail_sub_kelompok_part == 'OIL') {
                    $countOil++;
                } else {
                    $countPart++;
                }
            } else {
                $countPart++;
            }
        }

        if ($countPart < $countOil) {
            return 'Oli Regular';
        } elseif ($countPart > $countOil) {
            return 'Other';
        } else {
            $firstPart = Part::where('kd_part', $firstItem->kode_part)->first();

            if ($firstPart && $firstPart->fk_detail_sub_kelompok_part == 'OIL') {
                return 'Oli Regular';
            }
            return 'Other';
        }
    }

    public function checkStock($partCode, $bulan = null, $tahun = null)
    {
        $bulan = $bulan ?? date('n');
        $tahun = $tahun ?? date('Y');

        $part = Part::where('kd_part', $partCode)->first();

        if (!$part) {
            return [
                'available' => false,
                'message' => 'Part not found',
                'qty' => 0,
            ];
        }

        $stock = $part->getCurrentStock($bulan, $tahun);

        if (!$stock) {
            return [
                'available' => false,
                'message' => 'Stock not found',
                'qty' => 0,
            ];
        }

        $available = $stock->available;

        return [
            'available' => $stock->is_available,
            'message' => $stock->is_available
                ? "Available {$available} pcs"
                : 'Not Available',
            'qty' => max(0, $available),
            'qty_on_hand' => $stock->qty_on_hand,
            'qty_booking' => $stock->qty_booking,
            'min_stock' => $part->min_stok,
        ];
    }
}
