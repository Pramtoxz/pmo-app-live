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

        $filename = 'data-sales-spv-' . date('Y-m-d') . '.xls';

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
              . '<?mso-application progid="Excel.Sheet"?>' . "\n"
              . '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"'
              . ' xmlns:o="urn:schemas-microsoft-com:office:office"'
              . ' xmlns:x="urn:schemas-microsoft-com:office:excel"'
              . ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";

        $xml .= '<Worksheet ss:Name="Sales &amp; SPV">' . "\n" . '<Table>' . "\n";
        $xml .= self::row(['nama', 'email', 'spv / salesman', 'nohp', 'kode']);
        foreach ($data as $row) {
            $xml .= self::row([
                $row->nama,
                $row->email,
                $row->jabatan,
                $row->nohp,
                $row->kode,
            ]);
        }
        $xml .= '</Table>' . "\n" . '</Worksheet>' . "\n";
        $xml .= '</Workbook>';

        return response($xml, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private static function row(array $values): string
    {
        $cells = '';
        foreach ($values as $v) {
            $cells .= '<Cell><Data ss:Type="String">'
                . htmlspecialchars((string)$v, ENT_XML1, 'UTF-8')
                . '</Data></Cell>';
        }
        return '<Row>' . $cells . '</Row>' . "\n";
    }
}
