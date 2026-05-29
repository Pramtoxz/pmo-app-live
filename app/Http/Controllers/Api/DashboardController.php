<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $user = $request->user();
        $cart = $user->activeCart()->first();
        $cartCount = $cart ? $cart->totalItems : 0;

        // Get last successful collection cache refresh
        $lastRefresh = DB::connection('pgsql')
            ->table('pmov2.collection_cache_status')
            ->where('status', 'success')
            ->orderBy('last_refresh_at', 'desc')
            ->first();

        $collectionLastUpdate = null;
        if ($lastRefresh) {
            $collectionLastUpdate = [
                'last_refresh_at' => $lastRefresh->last_refresh_at,
                'total_shops_processed' => $lastRefresh->total_shops_processed,
                'total_records' => $lastRefresh->total_records,
                'duration_seconds' => $lastRefresh->duration_seconds,
            ];
        }

        return ApiResponse::success([
            'deliveryProgress' => '0%',
            'monthlyBuyIn' => 'Rp 0',
            'cartCount' => $cartCount,
            'collectionLastUpdate' => $collectionLastUpdate
        ]);
    }
}
