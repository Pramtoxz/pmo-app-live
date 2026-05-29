<?php

namespace App\Helpers\Import;

use Illuminate\Support\Facades\DB;

class ShopsImport
{
    private const DEFAULT_PASSWORD_HASH = '$2y$10$7g7i7KLU4DFMVJLQ24ucfe/tjZ/gVRn6WYi7CGrlbQp6VZO2d.QFW';

    public static function process(string $filepath): array
    {
        $handle = fopen($filepath, 'r');
        if (!$handle) {
            return ['success' => false, 'message' => 'Tidak dapat membuka file'];
        }

        // Baca header baris pertama (skip BOM jika ada)
        $rawHeader = fgetcsv($handle, 0, ';');
        $header = array_map(fn($h) => strtolower(trim(ltrim($h, "\xEF\xBB\xBF"))), $rawHeader);

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
                $kd_toko  = trim($row['kode']);
                $nohp     = isset($row['nohp']) ? (string) $row['nohp'] : null;
                $email    = !empty($row['email']) ? trim($row['email']) : strtolower($kd_toko) . '@pmo.com';

                $tokoActive = true;
                if (isset($row['status'])) {
                    $statusValue = strtoupper(trim($row['status']));
                    $tokoActive  = in_array($statusValue, ['AKTIF', '1', 'TRUE', 'YES', 'Y']);
                }

                $existingToko = DB::connection('pgsql')
                    ->table('public.tbltoko')
                    ->where('kd_toko', $kd_toko)
                    ->first();

                $tokoData = [
                    'toko'         => $row['nama_toko'] ?? 'Unknown',
                    'fk_sales'     => !empty($row['salesman']) ? (string) $row['salesman'] : null,
                    'fk_spv'       => !empty($row['spv']) ? (string) $row['spv'] : null,
                    'no_telp'      => $nohp,
                    'toko_active'  => $tokoActive,
                ];

                if (!$existingToko) {
                    $tokoData = array_merge($tokoData, [
                        'alamat'            => '-',
                        'npwp'              => '-',
                        'tipe_diskon'       => 'Persen',
                        'plafon_part'       => 0,
                        'top_part'          => 0,
                        'kategori'          => 'CHANNEL',
                        'kd_ahm'            => $kd_toko,
                        'up_pemilik'        => '-',
                        'head_toko'         => '',
                        'diskon_fix_order'  => 0,
                        'diskon_regular'    => 0,
                        'diskon_hotline'    => 0,
                        'diskon_urgent'     => 0,
                        'top_oli'           => 0,
                        'jenis_dealer'      => 'H3',
                        'toko_cabang'       => 'TIDAK',
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

        fclose($handle);

        return [
            'success'   => true,
            'processed' => $processed,
            'errors'    => $errors,
        ];
    }
}
