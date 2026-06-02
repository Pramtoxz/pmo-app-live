<?php

namespace App\Helpers\Import;

use Illuminate\Support\Facades\DB;

class ShopsImport
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

        // If rows are numeric arrays (from xlsx), first row is header
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
                $kd_toko = trim($row['kode']);
                $nohp    = isset($row['nohp']) ? (string) $row['nohp'] : null;
                $email   = !empty($row['email']) ? trim($row['email']) : strtolower($kd_toko) . '@pmo.com';

                $tokoActive = true;
                if (isset($row['status'])) {
                    $statusValue = strtoupper(trim($row['status']));
                    $tokoActive  = in_array($statusValue, ['AKTIF', '1', 'TRUE', 'YES', 'Y']);
                }

                $existingToko = DB::connection('pgsql')
                    ->table('pmov2.tbltoko')
                    ->where('kd_toko', $kd_toko)
                    ->first();

                $tokoData = [
                    'toko'        => $row['nama_toko'] ?? 'Unknown',
                    'fk_sales'    => !empty($row['salesman']) ? (string) $row['salesman'] : null,
                    'fk_spv'      => !empty($row['spv']) ? (string) $row['spv'] : null,
                    'no_telp'     => $nohp,
                    'toko_active' => $tokoActive,
                ];

                if (!$existingToko) {
                    $tokoData = array_merge($tokoData, [
                        'alamat'           => '-',
                        'npwp'             => '-',
                        'tipe_diskon'      => 'Persen',
                        'plafon_part'      => 0,
                        'top_part'         => 0,
                        'kategori'         => 'CHANNEL',
                        'kd_ahm'           => $kd_toko,
                        'up_pemilik'       => '-',
                        'head_toko'        => '',
                        'diskon_fix_order' => 0,
                        'diskon_regular'   => 0,
                        'diskon_hotline'   => 0,
                        'diskon_urgent'    => 0,
                        'top_oli'          => 0,
                        'jenis_dealer'     => 'H3',
                        'toko_cabang'      => 'TIDAK',
                    ]);
                }

                DB::connection('pgsql')->table('public.tbltoko')
                    ->updateOrInsert(['kd_toko' => $kd_toko], $tokoData);

                DB::connection('pgsql')->table('pmov2.users')
                    ->updateOrInsert(
                        ['email' => $email],
                        [
                            'name'       => $row['nama_toko'] ?? 'Unknown',
                            'password'   => self::DEFAULT_PASSWORD_HASH,
                            'role'       => 'dealer',
                            'fk_toko'    => $kd_toko,
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

        // Shared strings (Excel saves cell text as shared strings)
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
