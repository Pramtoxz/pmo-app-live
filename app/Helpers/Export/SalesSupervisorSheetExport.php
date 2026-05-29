<?php

namespace App\Helpers\Export;

use Illuminate\Support\Facades\DB;

class SalesSupervisorSheetExport
{
    public static function download()
    {
        $data = DB::connection('pgsql')->select("
            SELECT
                ss.nama,
                COALESCE(u.email, '') as email,
                ss.jabatan,
                COALESCE(ss.no_hp, '') as nohp,
                COALESCE(ss.kode_npk, '') as kode
            FROM pmov2.sales_supervisor ss
            LEFT JOIN pmov2.users u ON u.id = (
                SELECT id FROM pmov2.users
                WHERE name = ss.nama
                AND role IN ('sales', 'supervisor')
                LIMIT 1
            )
            WHERE ss.aktif = true
            ORDER BY ss.jabatan, ss.nama
        ");

        $rows = array_map(fn($r) => [
            $r->nama, $r->email, $r->jabatan, $r->nohp, $r->kode,
        ], $data);

        $xlsx = ShopsSheetExport::build([
            'Sales & SPV' => ['headers' => ['NAMA', 'EMAIL', 'JABATAN', 'NOHP', 'KODE'], 'rows' => $rows],
        ]);

        $filename = 'data-sales-spv-' . date('Y-m-d') . '.xlsx';

        return response($xlsx, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length'      => strlen($xlsx),
        ]);
    }
}
