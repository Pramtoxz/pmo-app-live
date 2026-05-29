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
            FROM pmov2.tbltoko t
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

        $shopsRows = array_map(fn($r) => [
            $r->nama_toko, $r->salesman, $r->spv, $r->kode, $r->email, $r->nohp, $r->status,
        ], $shops);

        $spvRows = array_map(fn($r) => [
            $r->nama, $r->email, $r->jabatan, $r->nohp, $r->kode,
        ], $spvSales);

        $xlsx = self::build([
            'Toko'       => ['headers' => ['NAMA TOKO', 'SALESMAN', 'SPV', 'KODE', 'EMAIL', 'NOHP', 'STATUS'], 'rows' => $shopsRows],
            'Sales & SPV' => ['headers' => ['NAMA', 'EMAIL', 'JABATAN', 'NOHP', 'KODE'],                        'rows' => $spvRows],
        ]);

        $filename = 'data-toko-' . date('Y-m-d') . '.xlsx';

        return response($xlsx, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length'      => strlen($xlsx),
        ]);
    }

    // -----------------------------------------------------------------------

    public static function build(array $sheets): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx_');
        $zip = new \ZipArchive();
        $zip->open($tmp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $names = array_keys($sheets);
        $count = count($names);

        $zip->addFromString('[Content_Types].xml',        self::contentTypes($count));
        $zip->addFromString('_rels/.rels',                self::rels());
        $zip->addFromString('xl/workbook.xml',            self::workbook($names));
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::workbookRels($count));
        $zip->addFromString('xl/styles.xml',              self::styles());

        $i = 1;
        foreach ($sheets as $sheet) {
            $zip->addFromString("xl/worksheets/sheet{$i}.xml", self::worksheet($sheet['headers'], $sheet['rows']));
            $i++;
        }

        $zip->close();
        $content = file_get_contents($tmp);
        unlink($tmp);

        return $content;
    }

    private static function contentTypes(int $sheetCount): string
    {
        $overrides = '';
        for ($i = 1; $i <= $sheetCount; $i++) {
            $overrides .= '<Override PartName="/xl/worksheets/sheet' . $i . '.xml"'
                . ' ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' . "\n";
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' . "\n"
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' . "\n"
            . '<Default Extension="xml"  ContentType="application/xml"/>' . "\n"
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' . "\n"
            . '<Override PartName="/xl/styles.xml"   ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' . "\n"
            . $overrides
            . '</Types>';
    }

    private static function rels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private static function workbook(array $names): string
    {
        $sheets = '';
        foreach ($names as $i => $name) {
            $id = $i + 1;
            $sheets .= '<sheet name="' . htmlspecialchars($name, ENT_XML1, 'UTF-8') . '" sheetId="' . $id . '" r:id="rId' . $id . '"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets>' . $sheets . '</sheets>'
            . '</workbook>';
    }

    private static function workbookRels(int $count): string
    {
        $rels = '';
        for ($i = 1; $i <= $count; $i++) {
            $rels .= '<Relationship Id="rId' . $i . '"'
                . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"'
                . ' Target="worksheets/sheet' . $i . '.xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . $rels
            . '</Relationships>';
    }

    private static function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/></cellXfs>'
            . '</styleSheet>';
    }

    private static function worksheet(array $headers, array $rows): string
    {
        $data = '<sheetData>';
        $data .= self::row(1, $headers);
        foreach ($rows as $ri => $row) {
            $data .= self::row($ri + 2, $row);
        }
        $data .= '</sheetData>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . $data
            . '</worksheet>';
    }

    private static function row(int $rowNum, array $values): string
    {
        $cells = '';
        foreach ($values as $ci => $value) {
            $col   = self::colLetter($ci + 1);
            $ref   = $col . $rowNum;
            $cells .= '<c r="' . $ref . '" t="inlineStr">'
                . '<is><t>' . htmlspecialchars((string) $value, ENT_XML1, 'UTF-8') . '</t></is>'
                . '</c>';
        }

        return '<row r="' . $rowNum . '">' . $cells . '</row>';
    }

    private static function colLetter(int $col): string
    {
        $letter = '';
        while ($col > 0) {
            $col--;
            $letter = chr(65 + ($col % 26)) . $letter;
            $col    = (int) ($col / 26);
        }

        return $letter;
    }
}
