<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $query = Campaign::query();

        if ($request->has('type')) {
            $query->where('badge', 'LIKE', "%{$request->type}%");
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $campaigns = $query->get();

        return ApiResponse::success($campaigns->map(function($campaign) {
            return [
                'id' => (string) $campaign->id,
                'title' => $campaign->judul,
                'badge' => $campaign->badge,
                'description' => $campaign->deskripsi,
                'image' => $campaign->gambar ? url('images/kampanye/' . $campaign->gambar) : null,
                'startDate' => $campaign->tanggal_mulai->format('Y-m-d'),
                'endDate' => $campaign->tanggal_selesai->format('Y-m-d'),
                'status' => $campaign->status,
            ];
        }));
    }

    public function show($id)
    {
        $campaign = Campaign::findOrFail($id);

        return ApiResponse::success([
            'id' => (string) $campaign->id,
            'title' => $campaign->judul,
            'badge' => $campaign->badge,
            'description' => $campaign->deskripsi,
            'image' => $campaign->gambar ? url('images/kampanye/' . $campaign->gambar) : null,
            'startDate' => $campaign->tanggal_mulai->format('Y-m-d'),
            'endDate' => $campaign->tanggal_selesai->format('Y-m-d'),
            'status' => $campaign->status,
            'fullDescription' => $campaign->deskripsi_lengkap,
            'partsIncluded' => $campaign->part_termasuk ?? [],
            'termsAndConditions' => $campaign->syarat_ketentuan,
            'rewards' => $campaign->hadiah ?? [],
        ]);
    }

    public function myAchievement(Request $request)
    {
        $campaign = Campaign::where('status', 'active')->first();

        if (!$campaign) {
            return ApiResponse::success(['currentCampaign' => null]);
        }

        return ApiResponse::success([
            'currentCampaign' => [
                'id' => (string) $campaign->id,
                'title' => $campaign->judul,
                'endDate' => $campaign->tanggal_selesai->format('Y-m-d H:i:s'),
                'achievementPercentage' => 0,
                'achievementLabel' => '0%',
            ]
        ]);
    }
}
