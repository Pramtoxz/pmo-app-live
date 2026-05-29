<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Services\OrderService;
use App\Helpers\ApiResponse;
use App\Helpers\PartHelper;
use Illuminate\Http\Request;
use App\Models\DataPart\SalesOrder;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function checkout(CheckoutRequest $request)
    {
        try {
            $user = $request->user();
            $result = $this->orderService->submitOrder($user->id);

            return ApiResponse::success($result, 'Order submitted successfully');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function checkStock(Request $request, $partCode)
    {
        $bulan = $request->get('bulan');
        $tahun = $request->get('tahun');

        $result = $this->orderService->checkStock($partCode, $bulan, $tahun);

        return ApiResponse::success($result);
    }

    public function history(Request $request)
    {
        $user = $request->user();
        
        $query = SalesOrder::with('details');
        
        if ($user->fk_toko) {
            $query->where('fk_toko', $user->fk_toko);
        } else {
            $query->where('fk_salesman', $user->id);
        }
        
        $hasDateFilter = $request->has('dari') && $request->has('sampai');
        if ($hasDateFilter) {
            $query->whereBetween('tgl_so', [
                $request->dari . ' 00:00:00',
                $request->sampai . ' 23:59:59'
            ]);
        }
        
        $filter = $request->get('filter');
        
        if ($filter === 'pending') {
            $query->where('status_approve_reject', 'Waiting For Approval');
        } elseif ($filter === 'completed' || $filter === 'back_order') {
            $query->where('status_approve_reject', 'Approve');
        }
        
        $query->orderBy('tgl_so', 'desc');
        
        if (!$hasDateFilter) {
            $limit = $request->get('limit', 20);
            $query->limit($limit);
        }
        
        $orders = $query->get();

        if ($filter === 'completed') {
            $orders = $orders->filter(function($order) {
                $totalBackOrder = $order->details->sum('qty_sisa');
                return $totalBackOrder == 0;
            })->values();
        } elseif ($filter === 'back_order') {
            $orders = $orders->filter(function($order) {
                $totalBackOrder = $order->details->sum('qty_sisa');
                return $totalBackOrder > 0;
            })->values();
        }

        return ApiResponse::success([
            'items' => $orders->map(function($order) {
                $totalQtyOrder = $order->details->sum('qty_so');
                $totalQtySisa = $order->details->sum('qty_sisa');
                $totalQtyDelivered = $totalQtyOrder - $totalQtySisa;
                
                return [
                    'id' => $order->no_so,
                    'orderNumber' => $order->no_so,
                    'orderType' => $order->jenis_so,
                    'orderDate' => $order->tgl_so->format('Y-m-d H:i:s'),
                    'grandTotal' => (float) $order->grand_total,
                    'status' => $order->status_approve_reject,
                    'fulfillment' => [
                        'totalQtyOrder' => $totalQtyOrder,
                        'totalQtyDelivered' => $totalQtyDelivered,
                        'totalQtyBackOrder' => $totalQtySisa,
                        'isCompleted' => $totalQtySisa == 0,
                    ]
                ];
            })
        ]);
    }

    public function detail(Request $request, $noSo)
    {
        $user = $request->user();
        
        $order = SalesOrder::with([
            'details.part',
            'deliveryOrders.details.part'
        ])
            ->where('no_so', $noSo)
            ->firstOrFail();

        // Authorization check
        if ($user->fk_toko && $order->fk_toko !== $user->fk_toko) {
            return ApiResponse::error('Unauthorized access to order', 403);
        }
        
        if (!$user->fk_toko && $order->fk_salesman !== $user->id) {
            return ApiResponse::error('Unauthorized access to order', 403);
        }

        // Hitung summary
        $totalQtyOrder = $order->details->sum('qty_so');
        $totalQtySisa = $order->details->sum('qty_sisa');
        $totalQtyDelivered = $totalQtyOrder - $totalQtySisa;

        return ApiResponse::success([
            'orderNumber' => $order->no_so,
            'orderType' => $order->jenis_so,
            'orderDate' => $order->tgl_so->format('Y-m-d H:i:s'),
            'grandTotal' => (float) $order->grand_total,
            'status' => $order->status_approve_reject,
            'summary' => [
                'totalItems' => $order->details->count(),
                'totalQtyOrder' => $totalQtyOrder,
                'totalQtyDelivered' => $totalQtyDelivered,
                'totalQtyBackOrder' => $totalQtySisa,
            ],
            'items' => $order->details->map(function($detail) {
                $qtyDelivered = $detail->qty_so - $detail->qty_sisa;
                
                return [
                    'partNumber' => $detail->fk_part,
                    'partName' => PartHelper::getPartName($detail->part, null),
                    'image' => PartHelper::getPartImage($detail->fk_part, null, $detail->part),
                    'orderQty' => $detail->qty_so,
                    'deliveryQty' => $qtyDelivered,
                    'backOrderQty' => $detail->qty_sisa,
                    'price' => (float) $detail->harga,
                    'subtotal' => (float) $detail->total_harga,
                ];
            }),
            'deliveryOrders' => $order->deliveryOrders->map(function($do) {
                return [
                    'noDo' => $do->no_do,
                    'tanggal' => $do->tgl_do->format('Y-m-d H:i:s'),
                    'status' => $do->status_approve_reject,
                    'grandTotal' => (float) $do->grand_total,
                    'items' => $do->details->map(function($detail) {
                        return [
                            'partNumber' => $detail->fk_part,
                            'partName' => PartHelper::getPartName($detail->part, null),
                            'qtyDo' => $detail->qty_do,
                            'price' => (float) $detail->harga,
                            'diskon' => (float) $detail->diskon,
                            'subtotal' => (float) $detail->total_harga,
                        ];
                    })
                ];
            })
        ]);
    }

    public function backOrder(Request $request, $noSo)
    {
        $user = $request->user();
        
        $order = SalesOrder::with(['details.part'])
            ->where('no_so', $noSo)
            ->firstOrFail();

        // Authorization check
        if ($user->fk_toko && $order->fk_toko !== $user->fk_toko) {
            return ApiResponse::error('Unauthorized access to order', 403);
        }
        
        if (!$user->fk_toko && $order->fk_salesman !== $user->id) {
            return ApiResponse::error('Unauthorized access to order', 403);
        }

        // Filter hanya yang qty_sisa > 0
        $backOrderItems = $order->details->filter(function($detail) {
            return $detail->qty_sisa > 0;
        });

        return ApiResponse::success([
            'orderNumber' => $order->no_so,
            'orderDate' => $order->tgl_so->format('Y-m-d H:i:s'),
            'totalBackOrderQty' => $backOrderItems->sum('qty_sisa'),
            'backOrderItems' => $backOrderItems->map(function($detail) {
                return [
                    'partNumber' => $detail->fk_part,
                    'partName' => PartHelper::getPartName($detail->part, null),
                    'image' => PartHelper::getPartImage($detail->fk_part, null, $detail->part),
                    'orderQty' => $detail->qty_so,
                    'deliveryQty' => $detail->qty_so - $detail->qty_sisa,
                    'backOrderQty' => $detail->qty_sisa,
                    'price' => (float) $detail->harga,
                ];
            })->values()
        ]);
    }
}
