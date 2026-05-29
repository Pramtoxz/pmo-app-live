<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ActualSpkController extends Controller
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

        $perPage = $request->query('per_page', 15);
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun');
        $status = $request->query('status');

        $query = DB::connection('pgsql_nms')
            ->table('H1_DOS.spk')
            ->select(
                'spk.IDSpk',
                'spk.TglSPK',
                'spk.IDCustomer',
                'mastercustomer.NamaCustomer',
                'mastercustomer.NoHp',
                'spk.IDJenisPembayaran',
                'setupjenispembayaran.JenisPembayaran',
                'spk.NamaLeasing',
                DB::raw("\"SpkDetail\".\"fk_tipe\" || '-' || \"SpkDetail\".\"fk_warna\" as tipe"),
                'mastergroupsegmenmotor.DeskripsiType as nama_tipe',
                'tblwarna.warna as nama_warna',
                'SpkDetail.harga_unit',
                'SpkDetail.diskon_unit',
                'spk.DP',
                'spk.Cicilan',
                'spk.Tenor',
                'spk.status_spk',
                'spk.created_at'
            )
            ->leftJoin('H1_DOS.mastercustomer', 'mastercustomer.IDCustomer', '=', 'spk.IDCustomer')
            ->leftJoin('H1_DOS.SpkDetail', 'SpkDetail.IdSPK', '=', 'spk.IDSpk')
            ->leftJoin('H1_DOS.setupjenispembayaran', 'setupjenispembayaran.IDJenisPembayaran', '=', 'spk.IDJenisPembayaran')
            ->leftJoin('H1_DOS.mastergroupsegmenmotor', 'mastergroupsegmenmotor.KodeType', '=', 'SpkDetail.fk_tipe')
            ->leftJoin('public.tblwarna', 'tblwarna.kd_warna', '=', 'SpkDetail.fk_warna')
            ->where('spk.id_flp', $flp->id_flp)
            ->orderBy('spk.TglSPK', 'desc');

        if ($bulan) {
            $query->whereRaw('EXTRACT(MONTH FROM "TglSPK") = ?', [$bulan]);
        }

        if ($tahun) {
            $query->whereRaw('EXTRACT(YEAR FROM "TglSPK") = ?', [$tahun]);
        }

        if ($status) {
            $query->where('spk.status_spk', $status);
        }

        $spk = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $spk->items(),
            'meta' => [
                'current_page' => $spk->currentPage(),
                'last_page' => $spk->lastPage(),
                'per_page' => $spk->perPage(),
                'total' => $spk->total(),
            ],
        ]);
    }

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $flp = $user->flp;

        if (!$flp) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak terdaftar sebagai FLP',
            ], 403);
        }

        $id = $request->query('id');

        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter id diperlukan',
            ], 422);
        }

        $spk = DB::connection('pgsql_nms')
            ->table('H1_DOS.spk')
            ->select(
                'spk.IDSpk',
                'spk.TglSPK',
                'spk.IDCustomer',
                'mastercustomer.NamaCustomer',
                'mastercustomer.NoHp',
                'mastercustomer.Alamat',
                'spk.IDJenisPembayaran',
                'setupjenispembayaran.JenisPembayaran',
                'spk.NamaLeasing',
                DB::raw("\"SpkDetail\".\"fk_tipe\" || '-' || \"SpkDetail\".\"fk_warna\" as tipe"),
                'mastergroupsegmenmotor.DeskripsiType as nama_tipe',
                'tblwarna.warna as nama_warna',
                'SpkDetail.harga_unit',
                'SpkDetail.diskon_unit',
                'spk.DP',
                'spk.Cicilan',
                'spk.Tenor',
                'spk.status_spk',
                'spk.created_at',
                'spk.updated_at'
            )
            ->leftJoin('H1_DOS.mastercustomer', 'mastercustomer.IDCustomer', '=', 'spk.IDCustomer')
            ->leftJoin('H1_DOS.SpkDetail', 'SpkDetail.IdSPK', '=', 'spk.IDSpk')
            ->leftJoin('H1_DOS.setupjenispembayaran', 'setupjenispembayaran.IDJenisPembayaran', '=', 'spk.IDJenisPembayaran')
            ->leftJoin('H1_DOS.mastergroupsegmenmotor', 'mastergroupsegmenmotor.KodeType', '=', 'SpkDetail.fk_tipe')
            ->leftJoin('public.tblwarna', 'tblwarna.kd_warna', '=', 'SpkDetail.fk_warna')
            ->where('spk.IDSpk', $id)
            ->where('spk.id_flp', $flp->id_flp)
            ->first();

        if (!$spk) {
            return response()->json([
                'success' => false,
                'message' => 'SPK tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $spk,
        ]);
    }
}
