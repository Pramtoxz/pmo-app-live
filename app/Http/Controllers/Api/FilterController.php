<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FilterController extends Controller
{
   
    public function getVehicleTypes(Request $request)
    {
        $query = DB::connection('pgsql_dms')
            ->table('public.tbltipe_kendaraan_id')
            ->select('kd_ptm as code', 'desc_tipe_cust as name')
            ->where('tipe_active', 1);

        // Jika ada search, filter berdasarkan nama
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('desc_tipe_cust', 'ILIKE', "%{$search}%");
        }

        $types = $query->groupBy('kd_ptm', 'desc_tipe_cust')
            ->orderBy('desc_tipe_cust')
            ->limit(20) // Limit 20 hasil untuk performa
            ->get()
            ->map(function($item) {
                return [
                    'code' => $item->code,
                    'name' => $item->name,
                ];
            });

        return ApiResponse::success($types);
    }

    public function getCategories(Request $request)
    {
        $query = DB::connection('pgsql_dms')
            ->table('public.tbldetail_sub_kelompok_part_id')
            ->select('kd_detail_sub_kelompok_part as code', 'detail_sub_kelompok_part as name')
            ->where('active', true);

        // Jika ada search, filter berdasarkan nama
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('detail_sub_kelompok_part', 'ILIKE', "%{$search}%");
        }

        $categories = $query->groupBy('kd_detail_sub_kelompok_part', 'detail_sub_kelompok_part')
            ->orderBy('detail_sub_kelompok_part')
            ->limit(20) // Limit 20 hasil untuk performa
            ->get()
            ->map(function($item) {
                return [
                    'code' => $item->code,
                    'name' => $item->name,
                ];
            });

        return ApiResponse::success($categories);
    }
}
