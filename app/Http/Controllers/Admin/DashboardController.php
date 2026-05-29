<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\PopularPart;
use App\Models\Campaign;
use App\Models\PublicSchema\Part;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalShops = Shop::count();
        $totalParts = Part::where('part_active', true)->count();
        $popularParts = PopularPart::count();
        $activeCampaigns = Campaign::where('status', 'active')->count();

        $lastRefresh = DB::connection('pgsql')
            ->table('pmov2.collection_cache_status')
            ->where('status', 'success')
            ->orderBy('last_refresh_at', 'desc')
            ->first();

        $isRefreshing = DB::connection('pgsql')
            ->table('pmov2.collection_cache_status')
            ->where('status', 'running')
            ->where('last_refresh_at', '>=', now()->subHours(2))
            ->exists();

        return view('admin.dashboard', compact(
            'totalShops',
            'totalParts',
            'popularParts',
            'activeCampaigns',
            'lastRefresh',
            'isRefreshing'
        ));
    }
}
