<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TargetProspek;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TargetProspekController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $flp = $user->flp;

        if (!$flp) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak terdaftar sebagai FLP',
            ], 403);
        }

        $bulan = $request->query('bulan', date('n'));
        $tahun = $request->query('tahun', date('Y'));

        $data = TargetProspek::getTargetProspekComparison($flp->id_flp, $bulan, $tahun);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
