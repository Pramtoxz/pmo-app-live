<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use App\Helpers\Export\ShopsSheetExport;
use App\Helpers\Import\ShopsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopWebController extends Controller
{
    public function index()
    {
        $shops = Shop::orderBy('kd_toko')->get();
        
        // Get last collection cache refresh status
        $lastRefresh = DB::connection('pgsql')
            ->table('pmov2.collection_cache_status')
            ->where('status', 'success')
            ->orderBy('last_refresh_at', 'desc')
            ->first();
        
        return view('admin.shops.index', compact('shops', 'lastRefresh'));
    }

    public function show($kd_toko)
    {
        $shop = Shop::findOrFail($kd_toko);
        
        $user = DB::connection('pgsql')
            ->table('pmov2.users')
            ->where('fk_toko', $kd_toko)
            ->where('role', 'dealer')
            ->first();
        
        $userEmail = $user ? $user->email : '-';
        
        return view('admin.shops.show', compact('shop', 'userEmail'));
    }

    public function create()
    {
        return view('admin.shops.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kd_toko' => 'required|string|max:10|unique:pgsql.pmov2.tbltoko,kd_toko',
            'toko' => 'required|string|max:255',
            'no_telp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'npwp' => 'nullable|string|max:20',
            'kategori' => 'nullable|string|max:50',
            'kd_ahm' => 'nullable|string|max:10',
        ]);

        try {
            Shop::create([
                'kd_toko' => $request->kd_toko,
                'toko' => $request->toko,
                'no_telp' => $request->no_telp,
                'alamat' => $request->alamat,
                'npwp' => $request->npwp,
                'kategori' => $request->kategori,
                'kd_ahm' => $request->kd_ahm,
                'toko_active' => $request->has('toko_active'),
            ]);

            return redirect()->route('admin.shops.index')
                ->with('success', 'Toko berhasil ditambahkan');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Gagal menambahkan toko: ' . $e->getMessage());
        }
    }

    public function edit($kd_toko)
    {
        $shop = Shop::findOrFail($kd_toko);
        return view('admin.shops.edit', compact('shop'));
    }

    public function update(Request $request, $kd_toko)
    {
        $shop = Shop::findOrFail($kd_toko);

        $request->validate([
            'toko' => 'required|string|max:255',
            'no_telp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'npwp' => 'nullable|string|max:20',
            'kategori' => 'nullable|string|max:50',
            'kd_ahm' => 'nullable|string|max:10',
        ]);

        try {
            $shop->update([
                'toko' => $request->toko,
                'no_telp' => $request->no_telp,
                'alamat' => $request->alamat,
                'npwp' => $request->npwp,
                'kategori' => $request->kategori,
                'kd_ahm' => $request->kd_ahm,
                'toko_active' => $request->has('toko_active'),
            ]);

            return redirect()->route('admin.shops.index')
                ->with('success', 'Toko berhasil diupdate');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Gagal mengupdate toko: ' . $e->getMessage());
        }
    }

    public function destroy($kd_toko)
    {
        try {
            $shop = Shop::findOrFail($kd_toko);
            
            $userCount = DB::connection('pgsql')
                ->table('pmov2.users')
                ->where('fk_toko', $kd_toko)
                ->count();

            if ($userCount > 0) {
                return redirect()->route('admin.shops.index')
                    ->with('error', 'Tidak dapat menghapus toko. Masih ada ' . $userCount . ' user yang terdaftar.');
            }

            $shop->delete();

            return redirect()->route('admin.shops.index')
                ->with('success', 'Toko berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('admin.shops.index')
                ->with('error', 'Gagal menghapus toko: ' . $e->getMessage());
        }
    }

    public function export()
    {
        return ShopsSheetExport::download();
    }

    public function import(Request $request)
    {
        $ext = strtolower($request->file('file') ? $request->file('file')->getClientOriginalExtension() : '');
        if (!in_array($ext, ['csv', 'txt', 'xlsx'])) {
            return redirect()->route('admin.shops.index')
                ->with('error', 'Format file tidak valid. Gunakan CSV atau Excel (.xlsx).');
        }
        $request->validate([
            'file' => 'required|file|max:5120',
        ]);

        try {
            $result = ShopsImport::process($request->file('file')->getPathname());

            $message = 'Data toko berhasil diimport! (' . $result['processed'] . ' baris)';
            if (!empty($result['errors'])) {
                $firstErrors = array_slice($result['errors'], 0, 3);
                $message .= ' | ' . count($result['errors']) . ' error: ' . implode(' | ', $firstErrors);
            }

            return redirect()->route('admin.shops.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.shops.index')
                ->with('error', 'Gagal import data: ' . $e->getMessage());
        }
    }

    public function resetCollectionPin($kd_toko)
    {
        try {
            $shop = Shop::findOrFail($kd_toko);
            
            $usersUpdated = User::where('fk_toko', $kd_toko)
                ->whereNotNull('collection_pin')
                ->update(['collection_pin' => null]);

            if ($usersUpdated === 0) {
                return redirect()->back()
                    ->with('info', 'Tidak ada PIN yang perlu direset untuk toko ini');
            }

            return redirect()->back()
                ->with('success', 'PIN Collection berhasil direset untuk ' . $usersUpdated . ' user. User dapat setup PIN baru.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal reset PIN: ' . $e->getMessage());
        }
    }

    public function refreshCollectionCache()
    {
        $key  = env('INTERNAL_CRON_KEY', '');
        $host = parse_url(config('app.url'), PHP_URL_HOST) ?: '127.0.0.1';
        $cmd  = 'curl -s -k -X POST https://127.0.0.1/api/internal/refresh-cache'
              . ' -H ' . escapeshellarg('Host: ' . $host)
              . ' -H ' . escapeshellarg('X-Internal-Key: ' . $key)
              . ' > /dev/null 2>&1 &';
        exec($cmd);
        return redirect()->back()
            ->with('success', 'Refresh cache dimulai di background. Cek status di dashboard beberapa menit lagi.');
    }

}
