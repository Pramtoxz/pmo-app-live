<?php

namespace App\Helpers\Import;

use Illuminate\Support\Facades\DB;

class SalesSupervisorImport
{
    private const DEFAULT_PASSWORD_HASH = '$2y$10$7g7i7KLU4DFMVJLQ24ucfe/tjZ/gVRn6WYi7CGrlbQp6VZO2d.QFW';

    public static function process(string $filepath): array
    {
        $handle = fopen($filepath, 'r');
        if (!$handle) {
            return ['success' => false, 'message' => 'Tidak dapat membuka file'];
        }

        $rawHeader = fgetcsv($handle, 0, ';');
        $header    = array_map(fn($h) => strtolower(trim(ltrim($h, "\xEF\xBB\xBF"))), $rawHeader);

        $processed = 0;
        $errors    = [];

        while (($rawRow = fgetcsv($handle, 0, ';')) !== false) {
            if (count($rawRow) < count($header)) {
                continue;
            }

            $row = array_combine($header, $rawRow);

            if (empty($row['kode'])) {
                continue;
            }

            try {
                $kode_npk = (string) trim($row['kode']);

                $jabatan = 'salesman';
                foreach (['spv / salesman', 'spv_salesman', 'spv__salesman', 'spv'] as $key) {
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

        fclose($handle);

        return [
            'success'   => true,
            'processed' => $processed,
            'errors'    => $errors,
        ];
    }
}
