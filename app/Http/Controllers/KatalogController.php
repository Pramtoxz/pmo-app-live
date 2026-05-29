<?php

namespace App\Http\Controllers;

use App\Models\KatalogKendaraan;
use Illuminate\Http\Request;

class KatalogController extends Controller
{
    public function index()
    {
        $maticCount = KatalogKendaraan::where('kategori', 'M')
            ->where('is_active', true)
            ->count();

        $cubCount = KatalogKendaraan::where('kategori', 'C')
            ->where('is_active', true)
            ->count();

        $sportCount = KatalogKendaraan::where('kategori', 'S')
            ->where('is_active', true)
            ->count();

        $electricCount = KatalogKendaraan::where('kategori', 'A')
            ->where('is_active', true)
            ->count();

        return view('katalog.index', compact('maticCount', 'cubCount', 'sportCount', 'electricCount'));
    }

    public function kategori($type)
    {
        $validTypes = ['matic', 'cub', 'sport', 'electric'];
        
        if (!in_array($type, $validTypes)) {
            abort(404);
        }

        $kategoriMap = [
            'matic' => 'M',
            'cub' => 'C',
            'sport' => 'S',
            'electric' => 'A'
        ];

        $katalogs = KatalogKendaraan::where('kategori', $kategoriMap[$type])
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        $kategoriName = ucfirst($type);

        return view('katalog.kategori', compact('katalogs', 'type', 'kategoriName'));
    }

    public function download($id)
    {
        $katalog = KatalogKendaraan::findOrFail($id);

        if (!$katalog->is_active) {
            abort(404);
        }

        $filePath = public_path($katalog->pdf_path);

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath, $katalog->nama_file);
    }

    public function view($id)
    {
        $katalog = KatalogKendaraan::findOrFail($id);

        if (!$katalog->is_active) {
            abort(404);
        }

        return view('katalog.view', compact('katalog'));
    }
}
