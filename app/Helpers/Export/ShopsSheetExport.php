<?php

namespace App\Helpers\Export;

use Illuminate\Support\Facades\DB;

class ShopsSheetExport
{
    public static function download()
    {
        $shops = DB::connection('pgsql')->select("
            SELECT
                t.toko as nama_toko,
                COALESCE(t.fk_sales, '') as salesman,
                COALESCE(t.fk_spv, '') as spv,
                t.kd_toko as kode,
                COALESCE(u.email, '') as email,
                COALESCE(t.no_telp, '') as nohp,
                CASE WHEN t.toko_active = true THEN 'AKTIF' ELSE 'NONAKTIF' END as status
            FROM public.tbltoko t
            LEFT JOIN pmov2.users u ON u.fk_toko = t.kd_toko AND u.role = 'dealer'
            ORDER BY t.toko
        ");

        $spvSales = DB::connection('pgsql')->select("
            SELECT
                ss.nama,
                COALESCE(u.email, '') AS email,
                ss.jabatan,
                COALESCE(ss.no_hp, '') AS nohp,
                COALESCE(ss.kode_npk, '') AS kode
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

        $filename = 'data-toko-' . date('Y-m-d') . '.xml';

        $xml  = self::openWorkbook();
        $xml .= self::sheet('Toko', ['NAMA TOKO', 'SALESMAN', 'SPV', 'KODE', 'EMAIL', 'NOHP', 'STATUS'], $shops, [
            'nama_toko', 'salesman', 'spv', 'kode', 'email', 'nohp', 'status',
        ]);
        $xml .= self::sheet('Sales & SPV', ['nama', 'email', 'spv / salesman', 'nohp', 'kode'], $spvSales, [
            'nama', 'email', 'jabatan', 'nohp', 'kode',
        ]);
        $xml .= self::closeWorkbook();

        return response($xml, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private static function openWorkbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<?mso-application progid="Excel.Sheet"?>' . "\n"
            . '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"'
            . ' xmlns:o="urn:schemas-microsoft-com:office:office"'
            . ' xmlns:x="urn:schemas-microsoft-com:office:excel"'
            . ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";
    }

    private static function closeWorkbook(): string
    {
        return '</Workbook>';
    }

    private static function sheet(string $name, array $headers, array $rows, array $fields): string
    {
        $out  = '<Worksheet ss:Name="' . htmlspecialchars($name, ENT_XML1, 'UTF-8') . '">' . "\n";
        $out .= '<Table>' . "\n";
        $out .= self::row($headers);
        foreach ($rows as $row) {
            $values = array_map(fn($f) => (string)($row->$f ?? ''), $fields);
            $out   .= self::row($values);
        }
        $out .= '</Table>' . "\n";
        $out .= '</Worksheet>' . "\n";
        return $out;
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
