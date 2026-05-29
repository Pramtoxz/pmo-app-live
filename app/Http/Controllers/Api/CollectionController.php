<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DataFA\Invoice;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CollectionController extends Controller
{
    public function setupPin(Request $request)
    {
        try {
            $user = $request->user();

            $request->validate([
                'pin' => 'required|digits:4',
                'pin_confirmation' => 'required|same:pin'
            ], [
                'pin.required' => 'PIN harus diisi',
                'pin.digits' => 'PIN harus 4 digit angka',
                'pin_confirmation.required' => 'Konfirmasi PIN harus diisi',
                'pin_confirmation.same' => 'Konfirmasi PIN tidak cocok'
            ]);

            $user->collection_pin = Hash::make($request->pin);
            $user->save();

            return ApiResponse::success([
                'message' => 'PIN berhasil diatur'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error($e->validator->errors()->first(), 422);
        } catch (\Exception $e) {
            Log::error('Setup PIN Error: ' . $e->getMessage());
            return ApiResponse::error('Gagal mengatur PIN: ' . $e->getMessage(), 500);
        }
    }

    public function changePin(Request $request)
    {
        try {
            $user = $request->user();

            $request->validate([
                'old_pin' => 'required|digits:4',
                'new_pin' => 'required|digits:4',
                'new_pin_confirmation' => 'required|same:new_pin'
            ], [
                'old_pin.required' => 'PIN lama harus diisi',
                'old_pin.digits' => 'PIN lama harus 4 digit angka',
                'new_pin.required' => 'PIN baru harus diisi',
                'new_pin.digits' => 'PIN baru harus 4 digit angka',
                'new_pin_confirmation.required' => 'Konfirmasi PIN baru harus diisi',
                'new_pin_confirmation.same' => 'Konfirmasi PIN baru tidak cocok'
            ]);

            if (!Hash::check($request->old_pin, $user->collection_pin)) {
                return ApiResponse::error('PIN lama salah', 403);
            }

            $user->collection_pin = Hash::make($request->new_pin);
            $user->save();

            return ApiResponse::success([
                'message' => 'PIN berhasil diubah'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error($e->validator->errors()->first(), 422);
        } catch (\Exception $e) {
            Log::error('Change PIN Error: ' . $e->getMessage());
            return ApiResponse::error('Gagal mengubah PIN: ' . $e->getMessage(), 500);
        }
    }

    public function verifyPin(Request $request)
    {
        try {
            $user = $request->user();

            $request->validate([
                'pin' => 'required|digits:4'
            ], [
                'pin.required' => 'PIN harus diisi',
                'pin.digits' => 'PIN harus 4 digit angka'
            ]);

            if (!$user->collection_pin) {
                return response()->json([
                    'success' => false,
                    'message' => 'PIN belum diatur',
                    'requires_setup' => true
                ], 403);
            }

            if (!Hash::check($request->pin, $user->collection_pin)) {
                return ApiResponse::error('PIN salah', 403);
            }

            return ApiResponse::success([
                'message' => 'PIN valid',
                'verified' => true
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error($e->validator->errors()->first(), 422);
        } catch (\Exception $e) {
            Log::error('Verify PIN Error: ' . $e->getMessage());
            return ApiResponse::error('Gagal verifikasi PIN: ' . $e->getMessage(), 500);
        }
    }

    public function checkPinStatus(Request $request)
    {
        try {
            $user = $request->user();

            return ApiResponse::success([
                'has_pin' => !empty($user->collection_pin),
                'requires_setup' => empty($user->collection_pin)
            ]);

        } catch (\Exception $e) {
            Log::error('Check PIN Status Error: ' . $e->getMessage());
            return ApiResponse::error('Gagal cek status PIN: ' . $e->getMessage(), 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user->fk_toko) {
                return ApiResponse::error('User tidak terdaftar di toko', 400);
            }

            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 50);
            $filter = $request->get('filter');
            $dari = $request->get('dari');
            $sampai = $request->get('sampai');
            $bulan = date('n');
            $tahun = date('Y');
            
            $last30Days = now()->subDays(30)->format('Y-m-d');
            $today = now()->format('Y-m-d');
            $isLast30Days = ($dari === $last30Days && $sampai === $today);

            $cacheKey = "collections_summary_{$user->fk_toko}_{$bulan}_{$tahun}";
            
            $summary = Cache::remember($cacheKey, 300, function() use ($user) {
                $outstanding = DB::connection('pgsql')
                    ->table('pmov2.collections_cache')
                    ->where('kd_toko', $user->fk_toko)
                    ->where('status', 'Outstanding');
                
                $paid = DB::connection('pgsql')
                    ->table('pmov2.collections_cache')
                    ->where('kd_toko', $user->fk_toko)
                    ->where('status', 'Paid');

                return [
                    'total_count' => $outstanding->count(),
                    'total_saldo' => $outstanding->sum('saldo'),
                    'total_nilai' => $outstanding->sum('nilai_faktur'),
                    'paid_count' => $paid->count(),
                    'paid_nilai' => $paid->sum('nilai_faktur'),
                ];
            });

            $outstanding = DB::connection('pgsql')
                ->table('pmov2.collections_cache')
                ->where('kd_toko', $user->fk_toko)
                ->where('status', 'Outstanding')
                ->orderBy('tgl_faktur', 'desc')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get();

            if ($dari && $sampai && $isLast30Days) {
                $paid = DB::connection('pgsql')
                    ->table('pmov2.collections_cache')
                    ->where('kd_toko', $user->fk_toko)
                    ->where('status', 'Paid')
                    ->orderBy('tgl_faktur', 'desc')
                    ->limit(100)
                    ->get();
                
                $paidSummary = [
                    'paid_count' => $summary['paid_count'],
                    'paid_nilai' => $summary['paid_nilai'],
                ];
            } elseif ($dari && $sampai) {
                $paid = Invoice::getCollectionsByDateRange($user->fk_toko, $dari, $sampai)
                    ->where('status', 'Paid');
                
                $paidSummary = [
                    'paid_count' => $paid->count(),
                    'paid_nilai' => $paid->sum('nilai_faktur'),
                ];
            } else {
                $paid = collect([]);
                $paidSummary = [
                    'paid_count' => 0,
                    'paid_nilai' => 0,
                ];
            }

            $summaryResponse = [
                'totalTagihan' => (float) ($summary['total_nilai'] ?? 0),
                'totalTerbayar' => (float) ($paidSummary['paid_nilai'] ?? 0),
                'totalOutstanding' => (float) ($summary['total_saldo'] ?? 0),
                'jumlahInvoice' => ($summary['total_count'] ?? 0) + ($paidSummary['paid_count'] ?? 0),
                'jumlahOutstanding' => (int) ($summary['total_count'] ?? 0),
                'jumlahPaid' => (int) ($paidSummary['paid_count'] ?? 0),
                'outstandingDisplayed' => $outstanding->count(),
                'currentPage' => (int) $page,
                'perPage' => (int) $perPage,
                'hasMore' => $outstanding->count() >= $perPage,
                'cached' => $isLast30Days,
            ];

            $outstandingData = $outstanding->map(function($item) {
                return $this->formatInvoiceItem($item);
            })->values();

            $paidData = $paid->map(function($item) {
                return $this->formatInvoiceItem($item);
            })->values();

            return ApiResponse::success([
                'summary' => $summaryResponse,
                'outstanding' => $outstandingData,
                'paid' => $paidData,
            ]);

        } catch (\Exception $e) {
            Log::error('Collection Index Error: ' . $e->getMessage(), [
                'user_id' => $request->user()->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            return ApiResponse::error('Failed to get collections: ' . $e->getMessage(), 500);
        }
    }

    public function summary(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user->fk_toko) {
                return ApiResponse::error('User tidak terdaftar di toko', 400);
            }

            $bulan = $request->get('bulan', date('n'));
            $tahun = $request->get('tahun', date('Y'));

            $result = Invoice::getCollections($user->fk_toko, $bulan, $tahun);
            $collections = $result['collections'];

            $summary = [
                'totalTagihan' => (float) $collections->sum('nilai_faktur'),
                'totalTerbayar' => (float) $collections->where('saldo', 0)->sum('nilai_faktur'),
                'totalOutstanding' => (float) $collections->sum('saldo'),
                'jumlahInvoice' => $collections->count(),
                'jumlahOutstanding' => $collections->where('saldo', '>', 0)->count(),
                'jumlahPaid' => $collections->where('saldo', 0)->count(),
            ];

            return ApiResponse::success($summary);

        } catch (\Exception $e) {
            Log::error('Collection Summary Error: ' . $e->getMessage());
            return ApiResponse::error('Failed to get summary: ' . $e->getMessage(), 500);
        }
    }

    public function detail(Request $request, $noFaktur)
    {
        try {
            $user = $request->user();
            
            if (!$user->fk_toko) {
                return ApiResponse::error('User tidak terdaftar di toko', 400);
            }

            $invoice = Invoice::getInvoiceDetail($noFaktur);

            if (!$invoice) {
                return ApiResponse::error('Invoice not found', 404);
            }

            $deliveryOrder = $invoice->deliveryOrder;
            if (!$deliveryOrder) {
                return ApiResponse::error('Delivery order not found', 404);
            }
            
            $salesOrder = $deliveryOrder->salesOrder;
            if (!$salesOrder || $salesOrder->fk_toko !== $user->fk_toko) {
                return ApiResponse::error('Unauthorized access to invoice', 403);
            }

            $nilaiGross = $invoice->details_data->sum(function($detail) {
                return $detail->harga * $detail->qty_do;
            });

            $totalDiskon = $invoice->details_data->sum(function($detail) {
                return $detail->diskon * $detail->qty_do;
            });

            $nilaiNett = $nilaiGross - $totalDiskon;

            $data = [
                'noFaktur' => $invoice->no_faktur,
                'tanggal' => $invoice->tgl_faktur->format('Y-m-d H:i:s'),
                'noDo' => $invoice->fk_do_part,
                'noSo' => $salesOrder->no_so ?? null,
                'jenisPembayaran' => $salesOrder->jenis_pembayaran ?? 'Unknown',
                'nilaiGross' => (float) $nilaiGross,
                'totalDiskon' => (float) $totalDiskon,
                'nilaiNett' => (float) $nilaiNett,
                'saldo' => (float) ($invoice->accountReceivable->saldo ?? $nilaiNett),
                'status' => ($invoice->accountReceivable && $invoice->accountReceivable->saldo == 0) ? 'Paid' : 'Outstanding',
                'items' => $invoice->details_data->map(function($detail) {
                    return [
                        'partCode' => $detail->fk_part,
                        'partName' => $detail->part_name,
                        'qty' => $detail->qty_do,
                        'harga' => (float) $detail->harga,
                        'diskon' => (float) $detail->diskon,
                        'subtotal' => (float) $detail->subtotal,
                    ];
                })
            ];

            return ApiResponse::success($data);

        } catch (\Exception $e) {
            Log::error('Collection Detail Error: ' . $e->getMessage(), [
                'no_faktur' => $noFaktur,
                'trace' => $e->getTraceAsString()
            ]);
            return ApiResponse::error('Failed to get invoice detail: ' . $e->getMessage(), 500);
        }
    }

    public function reminders(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user->fk_toko) {
                return ApiResponse::error('User tidak terdaftar di toko', 400);
            }

            $bulan = $request->get('bulan', date('n'));
            $tahun = $request->get('tahun', date('Y'));

           $result = Invoice::getCollections($user->fk_toko, $bulan, $tahun);
           $collections = $result['collections'];

            $outstanding = $collections->where('saldo', '>', 0);

            $reminders = $outstanding->map(function($item) {
                $tanggal = is_string($item->tgl_faktur) 
                    ? date('Y-m-d', strtotime($item->tgl_faktur))
                    : $item->tgl_faktur->format('Y-m-d');
                    
                return [
                    'noFaktur' => $item->no_faktur,
                    'tanggal' => $tanggal,
                    'jatuhTempo' => null,
                    'sisaHari' => null,
                    'saldo' => (float) $item->saldo,
                    'message' => 'Tagihan belum dibayar',
                ];
            })->values();

            return ApiResponse::success(['reminders' => $reminders]);

        } catch (\Exception $e) {
            Log::error('Collection Reminders Error: ' . $e->getMessage());
            return ApiResponse::error('Failed to get reminders: ' . $e->getMessage(), 500);
        }
    }

    private function formatInvoiceItem($item)
    {
        $tanggal = is_string($item->tgl_faktur) 
            ? $item->tgl_faktur 
            : $item->tgl_faktur->format('Y-m-d H:i:s');
            
        return [
            'noFaktur' => $item->no_faktur,
            'tanggal' => $tanggal,
            'noDo' => $item->fk_do_part,
            'noSo' => $item->no_so,
            'nilaiFaktur' => (float) $item->nilai_faktur,
            'saldo' => (float) $item->saldo,
            'status' => $item->status,
            'jenisPembayaran' => $item->jenis_pembayaran,
        ];
    }
}
