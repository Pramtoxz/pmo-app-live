<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Shop;
use App\Models\DataFA\Invoice;
use App\Providers\WhatsAppGateway;

class RefreshCollectionCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;

    public function handle(): void
    {
        $startTime = now();

        DB::connection('pgsql')->table('pmov2.collection_cache_status')->insert([
            'status'          => 'running',
            'last_refresh_at' => $startTime,
            'created_at'      => $startTime,
            'updated_at'      => $startTime,
        ]);

        try {
            (new WhatsAppGateway(2))->sendToGroup(null,
                "[PMO] Refresh cache piutang dimulai pada " . $startTime->format('d/m/Y H:i:s') . ".\nProses berlangsung ±40 menit."
            );
        } catch (\Exception $e) {
            Log::warning('RefreshCollectionCache: WA notif start gagal', ['error' => $e->getMessage()]);
        }

        $bulan = date('n');
        $tahun = date('Y');

        try {
            $shops      = Shop::where('toko_active', true)->pluck('kd_toko');
            $totalShops = $shops->count();
            $processed  = 0;
            $failed     = 0;

            foreach ($shops as $kdToko) {
                try {
                    $this->refreshShopCollection($kdToko, $bulan, $tahun);
                    $processed++;
                } catch (\Exception $e) {
                    $failed++;
                    Log::error("RefreshCollectionCache: Gagal toko {$kdToko}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $endTime  = now();
            $duration = (int) abs($startTime->diffInSeconds($endTime));

            $totalRecords = DB::connection('pgsql')
                ->table('pmov2.collections_cache')
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->count();

            DB::connection('pgsql')->table('pmov2.collection_cache_status')->insert([
                'status'                => 'success',
                'last_refresh_at'       => $endTime,
                'total_shops_processed' => $processed,
                'total_records'         => $totalRecords,
                'duration_seconds'      => $duration,
                'created_at'            => $endTime,
                'updated_at'            => $endTime,
            ]);

            try {
                (new WhatsAppGateway(2))->sendToGroup(null,
                    "[PMO] Refresh cache piutang SELESAI.\nToko: {$processed}/{$totalShops} | Gagal: {$failed} | Data: {$totalRecords} | Durasi: {$duration} detik."
                );
            } catch (\Exception $e) {
                Log::warning('RefreshCollectionCache: WA notif selesai gagal', ['error' => $e->getMessage()]);
            }
        } catch (\Exception $e) {
            Log::error('RefreshCollectionCache: Fatal error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            DB::connection('pgsql')->table('pmov2.collection_cache_status')->insert([
                'status'          => 'failed',
                'last_refresh_at' => now(),
                'error_message'   => $e->getMessage(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            try {
                (new WhatsAppGateway(2))->sendToGroup(null,
                    "[PMO] Refresh cache piutang GAGAL.\nError: " . $e->getMessage()
                );
            } catch (\Exception $waEx) {
                Log::warning('RefreshCollectionCache: WA notif gagal', ['error' => $waEx->getMessage()]);
            }

            throw $e;
        }
    }

    private function refreshShopCollection($kdToko, $bulan, $tahun): void
    {
        DB::connection('pgsql')->table('pmov2.collections_cache')
            ->where('kd_toko', $kdToko)
            ->delete();

        $result      = Invoice::getCollections($kdToko, null, null, 1, 9999);
        $collections = $result['collections'] ?? collect();

        $outstanding = $collections->where('status', 'Outstanding');

        $insertData = [];

        foreach ($outstanding as $item) {
            $insertData[] = [
                'kd_toko'          => $kdToko,
                'tgl_faktur'       => is_string($item->tgl_faktur) ? $item->tgl_faktur : $item->tgl_faktur->format('Y-m-d'),
                'jenis_pembayaran' => $item->jenis_pembayaran ?? null,
                'no_faktur'        => $item->no_faktur,
                'fk_do_part'       => $item->fk_do_part ?? null,
                'no_so'            => $item->no_so ?? null,
                'nilai_faktur'     => $item->nilai_faktur ?? 0,
                'saldo'            => $item->saldo ?? 0,
                'status'           => 'Outstanding',
                'bulan'            => $bulan,
                'tahun'            => $tahun,
                'cached_at'        => now(),
            ];
        }

        $last30Days = now()->subDays(30)->format('Y-m-d');
        $today      = now()->format('Y-m-d');

        $paidLast30Days = Invoice::getCollectionsByDateRange($kdToko, $last30Days, $today)
            ->where('status', 'Paid');

        foreach ($paidLast30Days as $item) {
            $insertData[] = [
                'kd_toko'          => $kdToko,
                'tgl_faktur'       => is_string($item->tgl_faktur) ? $item->tgl_faktur : $item->tgl_faktur->format('Y-m-d'),
                'jenis_pembayaran' => $item->jenis_pembayaran ?? null,
                'no_faktur'        => $item->no_faktur,
                'fk_do_part'       => $item->fk_do_part ?? null,
                'no_so'            => $item->no_so ?? null,
                'nilai_faktur'     => $item->nilai_faktur ?? 0,
                'saldo'            => 0,
                'status'           => 'Paid',
                'bulan'            => $bulan,
                'tahun'            => $tahun,
                'cached_at'        => now(),
            ];
        }

        if (empty($insertData)) {
            return;
        }

        foreach (array_chunk($insertData, 100) as $chunk) {
            DB::connection('pgsql')->table('pmov2.collections_cache')->insert($chunk);
        }
    }
}
