<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::orderBy('created_at', 'desc')->get();
        return view('admin.campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        return view('admin.campaigns.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'badge' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'status' => 'required|in:active,inactive,expired',
            'deskripsi_lengkap' => 'nullable|string',
            'syarat_ketentuan' => 'nullable|string',
        ]);

        try {
            $data = $request->except('gambar');

            if ($request->hasFile('gambar')) {
                $file = $request->file('gambar');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('images/kampanye'), $filename);
                $data['gambar'] = $filename;
            }

            Campaign::create($data);

            return redirect()->route('admin.campaigns.index')
                ->with('success', 'Kampanye berhasil ditambahkan');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Gagal menambahkan kampanye: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $campaign = Campaign::findOrFail($id);
        return view('admin.campaigns.show', compact('campaign'));
    }

    public function edit($id)
    {
        $campaign = Campaign::findOrFail($id);
        return view('admin.campaigns.edit', compact('campaign'));
    }

    public function update(Request $request, $id)
    {
        $campaign = Campaign::findOrFail($id);

        $request->validate([
            'judul' => 'required|string|max:255',
            'badge' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'status' => 'required|in:active,inactive,expired',
            'deskripsi_lengkap' => 'nullable|string',
            'syarat_ketentuan' => 'nullable|string',
        ]);

        try {
            $data = $request->except('gambar');

            if ($request->hasFile('gambar')) {
                if ($campaign->gambar && File::exists(public_path('images/kampanye/' . $campaign->gambar))) {
                    File::delete(public_path('images/kampanye/' . $campaign->gambar));
                }

                $file = $request->file('gambar');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('images/kampanye'), $filename);
                $data['gambar'] = $filename;
            }

            $campaign->update($data);

            return redirect()->route('admin.campaigns.index')
                ->with('success', 'Kampanye berhasil diupdate');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Gagal mengupdate kampanye: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $campaign = Campaign::findOrFail($id);

            if ($campaign->gambar && File::exists(public_path('images/kampanye/' . $campaign->gambar))) {
                File::delete(public_path('images/kampanye/' . $campaign->gambar));
            }

            $campaign->delete();

            return redirect()->route('admin.campaigns.index')
                ->with('success', 'Kampanye berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('admin.campaigns.index')
                ->with('error', 'Gagal menghapus kampanye: ' . $e->getMessage());
        }
    }
}
