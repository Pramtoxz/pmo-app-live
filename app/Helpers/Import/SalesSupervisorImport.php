<?php

namespace App\Helpers\Import;

use Illuminate\Support\Facades\DB;

class SalesSupervisorImport
{
    private const DEFAULT_PASSWORD_HASH = '$2y$10$7g7i7KLU4DFMVJLQ24ucfe/tjZ/gVRn6WYi7CGrlbQp6VZO2d.QFW';

    public static function process(string $filepath): array
    {
        if (self::isXlsx($filepath)) {
            return self::processRows(self::readXlsx($filepath, 0));
        }
        return self::processCsv($filepath);
    }

    // -------------------------------------------------------------------------

    private static function processCsv(string $filepath): array
    {
        $handle = fopen($filepath, 'r');
        if (!$handle) {
            return ['success' => false, 'message' => 'Tidak dapat membuka file'];
        }

        $rawHeader = fgetcsv($handle, 0, ';');
        $header    = array_map(fn($h) => strtolower(trim(ltrim($h, "\xEF\xBB\xBF"))), $rawHeader);

        $rows = [];
        while (($rawRow = fgetcsv($handle, 0, ';')) !== false) {
            if (count($rawRow) < count($header)) continue;
            $rows[] = array_combine($header, $rawRow);
        }
        fclose($handle);

        return self::processRows($rows, true);
    }

    private static function processRows(array $rows, bool $alreadyAssoc = false): array
    {
        if (empty($rows)) {
            return ['success' => true, 'processed' => 0, 'errors' => []];
        }

        if (!$alreadyAssoc) {
            $rawHeader = array_shift($rows);
            $header    = array_map(fn($h) => strtolower(trim(ltrim((string) $h, "\xEF\xBB\xBF"))), $rawHeader);
            $rows = array_map(function($rawRow) use ($header) {
                $padded = array_pad($rawRow, count($header), '');
                return array_combine($header, $padded);
            }, $rows);
        }

        $processed = 0;
        $errors    = [];

        foreach ($rows as $row) {
            if (empty($row['kode'])) continue;

            try {
                $kode_npk = (string) trim($row['kode']);

                $jabatan = 'salesman';
                foreach (['spv / salesman', 'spv_salesman', 'spv__salesman', 'jabatan', 'spv'] as $key) {
                    if (isset($row[$key]) && $row[$key] !== '') {
                        $jabatan = strtolower(trim($row[$key]));
                        break;
                    }
                }

                $nohp  = isset($row['nohp']) ? (string) $row['nohp'] : null;
                $email = !empty($row['email']) ? trim($row['email']) : strtolower($kode_npk) . '@pmo.com';
                $nama  = !empty($row['nama'])
                    ? strtoupper(trim($row['nama']))
                    : strtoupper(explode('@', $email)[0]);

                DB::connection('pgsql')->table('pmov2.sales_supervisor')
                    ->updateOrInsert(
                        ['kode_npk' => $kode_npk],
                        [
                            'nama'       => $nama,
                            'no_hp'      => $nohp,
                            'jabatan'    => $jabatan,
                            'aktif'      => true,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );

                DB::connection('pgsql')->table('pmov2.users')
                    ->updateOrInsert(
                        ['email' => $email],
                        [
                            'name'       => $nama,
                            'password'   => self::DEFAULT_PASSWORD_HASH,
                            'role'       => $jabatan === 'spv' ? 'supervisor' : 'sales',
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );

                $processed++;
            } catch (\Exception $e) {
                $errors[] = 'Baris kode ' . ($row['kode'] ?? '?') . ': ' . $e->getMessage();
            }
        }

        return ['success' => true, 'processed' => $processed, 'errors' => $errors];
    }

    // -------------------------------------------------------------------------
    // XLSX reader (native PHP — ZipArchive + DOMDocument, no package needed)
    // -------------------------------------------------------------------------

    private static function isXlsx(string $filepath): bool
    {
        $fh = fopen($filepath, 'rb');
        if (!$fh) return false;
        $magic = fread($fh, 4);
        fclose($fh);
        return $magic === "PK\x03\x04";
    }

    private static function readXlsx(string $filepath, int $sheetIndex = 0): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($filepath) !== true) {
            throw new \Exception('Tidak dapat membuka file Excel');
        }

        $sharedStrings = [];
        $ssContent = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssContent !== false) {
            $ssDom = new \DOMDocument();
            $ssDom->loadXML($ssContent);
            $ssXpath = new \DOMXPath($ssDom);
            $ssXpath->registerNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            foreach ($ssXpath->query('//x:si') as $si) {
                $text = '';
                foreach ($ssXpath->query('.//x:t', $si) as $t) {
                    $text .= $t->nodeValue;
                }
                $sharedStrings[] = $text;
            }
        }

        $sheetContent = $zip->getFromName('xl/worksheets/sheet' . ($sheetIndex + 1) . '.xml');
        $zip->close();

        if ($sheetContent === false) {
            throw new \Exception('Sheet tidak ditemukan di file Excel');
        }

        $dom = new \DOMDocument();
        $dom->loadXML($sheetContent);
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $result = [];
        foreach ($xpath->query('//x:sheetData/x:row') as $rowNode) {
            $cells  = [];
            $maxCol = 0;

            foreach ($xpath->query('.//x:c', $rowNode) as $cellNode) {
                $ref = $cellNode->getAttribute('r');
                preg_match('/([A-Z]+)/', $ref, $m);
                $colIdx = self::colLetterToIndex($m[1]);
                $type   = $cellNode->getAttribute('t');

                if ($type === 's') {
                    $vNode = $xpath->query('.//x:v', $cellNode)->item(0);
                    $value = $vNode ? ($sharedStrings[(int) $vNode->nodeValue] ?? '') : '';
                } elseif ($type === 'inlineStr') {
                    $tNode = $xpath->query('.//x:is/x:t', $cellNode)->item(0);
                    $value = $tNode ? $tNode->nodeValue : '';
                } else {
                    $vNode = $xpath->query('.//x:v', $cellNode)->item(0);
                    $value = $vNode ? $vNode->nodeValue : '';
                }

                $cells[$colIdx] = $value;
                $maxCol = max($maxCol, $colIdx);
            }

            $rowData = [];
            for ($i = 0; $i <= $maxCol; $i++) {
                $rowData[$i] = $cells[$i] ?? '';
            }
            $result[] = $rowData;
        }

        return $result;
    }

    private static function colLetterToIndex(string $col): int
    {
        $idx = 0;
        foreach (str_split($col) as $char) {
            $idx = $idx * 26 + (ord($char) - 64);
        }
        return $idx - 1;
    }
}
