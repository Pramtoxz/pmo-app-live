<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\MSendHO;
use App\Models\Serial;
use App\Models\DataPart\SalesOrder;
use App\Models\DataPart\SalesOrderDetail;
use App\Models\PublicSchema\Part;
use App\Providers\WhatsAppGateway;
use App\Helpers\NotificationHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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

            // Cek deadline checkout dari MSendHO id=2
            $deadline = MSendHO::find(2);
            if ($deadline) {
                $deadlineDateTime = Carbon::parse($deadline->tgl_kirim_akhir->format('Y-m-d') . ' ' . $deadline->jam);
                if (now()->greaterThan($deadlineDateTime)) {
                    throw new \Exception('Checkout ditutup sementara. Silakan tunggu periode selanjutnya.');
                }
            }

            // 2. Kelompokkan item keranjang ke dalam 4 kelompok: OIL, GMO, Tire, HGP
            $groupedItems = [
                self::GROUP_OIL  => [],
                self::GROUP_GMO  => [],
                self::GROUP_TIRE => [],
                self::GROUP_HGP  => [],
            ];

            foreach ($cart->items as $item) {
                $fkDetail = $item->part->fk_detail_sub_kelompok_part ?? null;
                if (!$fkDetail && $item->part === null) {
                    $part = Part::where('kd_part', $item->kode_part)->first();
                    $fkDetail = $part->fk_detail_sub_kelompok_part ?? null;
                }
                $group = $this->getPartGroup($fkDetail);
                $groupedItems[$group][] = $item;
            }

            $orders = [];
            $totalAllGrandTotal = 0;
            $totalAllItemsCount = 0;

            // 3. Buat SO untuk tiap kelompok yang memiliki item
            foreach ($groupedItems as $groupName => $items) {
                if (empty($items)) {
                    continue;
                }

                $groupTotal = 0;
                foreach ($items as $item) {
                    $groupTotal += (float) $item->subtotal;
                }
                $totalAllGrandTotal += $groupTotal;
                $totalAllItemsCount += count($items);

                $noSo = Serial::generateSO();
                $jenisOrder = $this->getJenisSoByGroup($groupName);
                $keterangan = $this->getKeteranganByGroup($groupName);

                // Insert SO header ke data_part.tblso
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
                    'grand_total' => $groupTotal,
                    'status_outstanding' => true,
                    'status_approve_reject' => 'Waiting For Approval',
                    'keterangan' => $keterangan,
                ]);

                // Insert SO detail ke data_part.tblso_detail
                foreach ($items as $item) {
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

                $orders[] = [
                    'kelompok' => $groupName,
                    'no_so' => $noSo,
                    'jenis_so' => $jenisOrder,
                    'keterangan' => $keterangan,
                    'grand_total' => $groupTotal,
                    'items_count' => count($items),
                ];
            }

            if (empty($orders)) {
                throw new \Exception('Tidak ada item valid untuk diproses.');
            }

            // 4. Kirim notifikasi WA ke grup (1 pesan gabungan sesuai Opsi 1)
            $this->sendOrderNotification($cart, $orders, $totalAllItemsCount);

            // 5. Kirim push notification ke user
            $allSoNumbers = array_column($orders, 'no_so');
            $soSummary = implode(', ', $allSoNumbers);
            NotificationHelper::sendOrderNotification($userId, $soSummary, 'created');

            // 6. Clear cart
            $cart->items()->delete();
            $cart->delete();

            $firstOrder = $orders[0];

            return [
                'no_so' => $firstOrder['no_so'],
                'jenis_so' => count($orders) > 1 ? 'Multi SO' : $firstOrder['jenis_so'],
                'grand_total' => $totalAllGrandTotal,
                'status' => 'Waiting For Approval',
                'orders' => $orders,
            ];
        });
    }

    public const GROUP_OIL = 'OIL';
    public const GROUP_GMO = 'GMO';
    public const GROUP_TIRE = 'Tire';
    public const GROUP_HGP = 'HGP';

    public function getPartGroup(?string $fkDetailSubKelompok): string
    {
        $code = strtoupper(trim((string) $fkDetailSubKelompok));
        if ($code === 'OIL') {
            return self::GROUP_OIL;
        }
        if ($code === 'GMO') {
            return self::GROUP_GMO;
        }
        if ($code === 'TIRE' || $code === 'TIRE1') {
            return self::GROUP_TIRE;
        }
        return self::GROUP_HGP;
    }

    public function getJenisSoByGroup(string $group): string
    {
        return $group === self::GROUP_OIL ? 'Oli Regular' : 'Other';
    }

    public function getKeteranganByGroup(string $group): string
    {
        return 'Order by PMO - ' . $group;
    }

    private function sendOrderNotification($cart, array $orders, int $totalItemCount)
    {
        try {
            $wa = new WhatsAppGateway(2);
            $shopName = $cart->user->shop->nama ?? 'Toko';
            $userToko = $cart->user->fk_toko ?? '-';

            $message = "🔔 *ORDER BARU - PMO*\n\n";
            $message .= "Toko: *{$shopName}*\n";
            $message .= "Kode Toko: {$userToko}\n";
            $message .= "Total Item: {$totalItemCount} item\n\n";
            $message .= "*No. Sales Order:*\n";

            foreach ($orders as $order) {
                $kelompok = $order['kelompok'];
                $noSo = $order['no_so'];
                $itemCount = $order['items_count'];
                $message .= "• [{$kelompok}] {$noSo} ({$itemCount} item)\n";
            }

            $message .= "\nWaktu Order: " . now()->format('d/m/Y H:i:s');
            $wa->sendToGroup(null, $message);

        } catch (\Exception $e) {
            Log::error('Error kirim notifikasi WA: ' . $e->getMessage());
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
            'min_stock' => $stock->min_stock,
        ];
    }
}
