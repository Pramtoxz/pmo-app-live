<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PublicSchema\Part;
use App\Models\PopularPart;
use App\Helpers\ApiResponse;
use App\Helpers\PartHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartController extends Controller
{
    public function index(Request $request)
    {
        $limit = min($request->get('limit', 20), 50);
        $page = $request->get('page', 1);
        
        if ($request->has('vehicle_type') || $request->has('category')) {
            return $this->filteredParts($request, $limit, $page);
        }
        
        if ($request->has('search')) {
            return $this->searchParts($request, $limit, $page);
        }
        
        $totalPopular = cache()->remember('total_popular_parts', 3600, function() {
            return PopularPart::count();
        });
        
        if (($page - 1) * $limit < $totalPopular) {
            $popularParts = PopularPart::with('part')
                ->orderBy('peringkat')
                ->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();
            
            $parts = $popularParts->map(function($pop) {
                return $pop->part;
            })->filter();
            
            $hasMore = (($page) * $limit) < $totalPopular || Part::where('part_active', true)->whereRaw('CAST(het AS NUMERIC) > 0')->count() > 0;
            
        } else {
            $skip = (($page - 1) * $limit) - $totalPopular;
            
            $query = Part::query();
            $query->where('part_active', true)
                  ->whereRaw('CAST(het AS NUMERIC) > 0');
            $popularPartNumbers = cache()->remember('popular_part_numbers', 3600, function() {
                return PopularPart::pluck('kode_part')->toArray();
            });
            
            if (!empty($popularPartNumbers)) {
                $query->whereNotIn('kd_part', $popularPartNumbers);
            }

            $query->orderBy('nm_part', 'asc');
            
            $parts = $query->skip($skip)->take($limit)->get();
            $hasMore = $query->count() > ($skip + $limit);
        }
        
        return $this->formatPartsResponse($parts, $page, $limit, $hasMore);
    }

    private function searchParts($request, $limit, $page)
    {
        $query = Part::query();
        // Search bisa tampilkan semua part (termasuk discontinued) untuk cek harga
        $query->whereRaw('CAST(het AS NUMERIC) > 0');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kd_part', 'ILIKE', "%{$search}%")
                  ->orWhere('nm_part', 'ILIKE', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('fk_detail_sub_kelompok_part', $request->category);
        }

        $query->orderBy('nm_part', 'asc');
        
        $parts = $query->skip(($page - 1) * $limit)->take($limit)->get();
        $hasMore = $query->count() > ($page * $limit);
        
        return $this->formatPartsResponse($parts, $page, $limit, $hasMore);
    }

    private function filteredParts($request, $limit, $page)
    {
        $query = Part::query();
        // Filter bisa tampilkan semua part (termasuk discontinued) untuk cek harga
        $query->whereRaw('CAST(het AS NUMERIC) > 0');

        if ($request->has('vehicle_type')) {
            $vehicleType = $request->vehicle_type;
            
            $compatibleParts = DB::connection('pgsql_dms')
                ->table('public.tblpart_detail_tipe_kendaraan')
                ->where('fk_tipe_kendaraan', $vehicleType)
                ->pluck('fk_part')
                ->toArray();
            
            if (!empty($compatibleParts)) {
                $query->whereIn('kd_part', $compatibleParts);
            } else {
                return $this->formatPartsResponse(collect([]), $page, $limit, false);
            }
        }

        if ($request->has('category')) {
            $query->where('fk_detail_sub_kelompok_part', $request->category);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kd_part', 'ILIKE', "%{$search}%")
                  ->orWhere('nm_part', 'ILIKE', "%{$search}%");
            });
        }

        $query->orderBy('nm_part', 'asc');
        
        $parts = $query->skip(($page - 1) * $limit)->take($limit)->get();
        $hasMore = $query->count() > ($page * $limit);
        
        return $this->formatPartsResponse($parts, $page, $limit, $hasMore);
    }

    private function formatPartsResponse($parts, $page, $limit, $hasMore)
    {
        $partNumbers = $parts->pluck('kd_part')->toArray();
        
        $partImages = Product::whereIn('kode_part', $partNumbers)
            ->select('kode_part', 'gambar', 'nama', 'deskripsi')
            ->get()
            ->keyBy('kode_part');

        $parts->load('stock');

        return ApiResponse::success([
            'items' => $parts->map(function($part) use ($partImages) {
                $partImage = $partImages->get($part->kd_part);
                $stock = $part->stock->first(); 
                
                $name = PartHelper::getPartName($part, $partImage);
                $description = PartHelper::getPartDescription($part, $partImage);
                $imageUrl = PartHelper::getPartImage($part->kd_part, $partImage, $part);
                $isReady = $stock ? $stock->is_available : false;
                $isDiscontinued = !$part->part_active;
                
                return [
                    'id' => (string) $part->kd_part,
                    'image' => $imageUrl,
                    'partNumber' => $part->kd_part,
                    'name' => $name,
                    'description' => $description,
                    'price' => (float) $part->het,
                    'category' => $part->fk_detail_sub_kelompok_part,
                    'isReady' => $isReady,
                    'isDiscontinued' => $isDiscontinued,
                    'canOrder' => !$isDiscontinued,
                ];
            }),
            'pagination' => [
                'currentPage' => (int) $page,
                'perPage' => $limit,
                'hasMore' => $hasMore,
            ]
        ]);
    }

    public function show($partNumber)
    {
        // Tidak filter part_active agar bisa lihat detail discontinued part untuk cek harga
        $part = Part::with('stock')->where('kd_part', $partNumber)->firstOrFail();
        $partImage = Product::where('kode_part', $part->kd_part)->first();
        $stock = $part->stock->first(); 

        $name = PartHelper::getPartName($part, $partImage);
        $description = PartHelper::getPartDescription($part, $partImage);
        $imageUrl = PartHelper::getPartImage($part->kd_part, $partImage, $part);
        $isDiscontinued = !$part->part_active;

        return ApiResponse::success([
            'id' => (string) $part->kd_part,
            'image' => $imageUrl,
            'partNumber' => $part->kd_part,
            'name' => $name,
            'description' => $description,
            'price' => (float) $part->het,
            'isReady' => $stock ? $stock->is_available : false,
            'stock' => $stock ? max(0, $stock->available) : 0,
            'category' => $part->fk_detail_sub_kelompok_part,
            'isDiscontinued' => $isDiscontinued,
            'canOrder' => !$isDiscontinued,
            'discontinuedMessage' => $isDiscontinued ? 'Part ini sudah tidak diproduksi (discontinued). Hanya untuk referensi harga.' : null,
        ]);
    }
}
