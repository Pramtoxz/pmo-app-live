<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PopularPart;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PopularPartController extends Controller
{
    public function generate(Request $request)
    {
        $limit = $request->get('limit', 100);
        
        try {
            $popularParts = DB::connection('pgsql_dms')->select("
                SELECT 
                    sd.fk_part as part_number,
                    CAST(SUM(sd.qty_so) AS INTEGER) as total_qty_sold,
                    COUNT(DISTINCT sd.fk_so) as total_orders,
                    SUM(sd.total_harga) as total_revenue
                FROM data_part.tblso_detail sd
                INNER JOIN data_part.tblso so ON so.no_so = sd.fk_so
                WHERE so.tgl_so >= CURRENT_DATE - INTERVAL '6 months'
                GROUP BY sd.fk_part
                ORDER BY total_qty_sold DESC
                LIMIT {$limit}
            ");

            PopularPart::truncate();

            $rank = 1;
            foreach ($popularParts as $part) {
                PopularPart::create([
                    'kode_part' => $part->part_number,
                    'total_qty_terjual' => $part->total_qty_sold,
                    'total_order' => $part->total_orders,
                    'total_omzet' => $part->total_revenue,
                    'peringkat' => $rank++,
                    'tanggal_generate' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Berhasil generate {$limit} part terlaris!",
                'total' => count($popularParts),
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate popular parts: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function index()
    {
        $popularParts = PopularPart::with('part')
            ->orderBy('peringkat')
            ->get();

        // Check if request is API or web
        // if (request()->wantsJson() || request()->is('api/*')) {
        //     return response()->json([
        //         'success' => true,
        //         'data' => $popularParts,
        //         'last_generated' => $popularParts->first()?->tanggal_generate,
        //     ]);
        // }

        // Return web view
        return view('admin.popular-parts.index', compact('popularParts'));
    }
}
