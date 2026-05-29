<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PartCategoryImage;
use App\Models\PublicSchema\PartCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartCategoryImageWebController extends Controller
{
    public function index()
    {
        $categories = PartCategoryImage::orderBy('nama_kelompok')->get();
        return view('admin.category-images.index', compact('categories'));
    }

    public function create()
    {
        // Ambil kelompok yang belum ada gambarnya
        $existingCodes = PartCategoryImage::pluck('kode_kelompok')->toArray();
        
        $availableCategories = DB::connection('pgsql_dms')
            ->table('public.tbldetail_sub_kelompok_part_id')
            ->where('active', true)
            ->whereNotIn('kd_detail_sub_kelompok_part', $existingCodes)
            ->orderBy('detail_sub_kelompok_part')
            ->get();

        return view('admin.category-images.create', compact('availableCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_kelompok' => 'required|string|max:50|unique:pmov2.gambar_kelompok_part,kode_kelompok',
            'nama_kelompok' => 'required|string|max:255',
            'gambar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'deskripsi' => 'nullable|string',
        ]);

        $category = new PartCategoryImage();
        $category->kode_kelompok = $request->kode_kelompok;
        $category->nama_kelompok = $request->nama_kelompok;
        $category->deskripsi = $request->deskripsi;

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = $request->kode_kelompok . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/category'), $filename);
            $category->gambar = $filename;
        }

        $category->save();

        return redirect()->route('admin.category-images.index')
            ->with('success', 'Gambar kelompok berhasil ditambahkan');
    }

    public function edit($id)
    {
        $category = PartCategoryImage::findOrFail($id);
        return view('admin.category-images.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $category = PartCategoryImage::findOrFail($id);

        $request->validate([
            'nama_kelompok' => 'required|string|max:255',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'deskripsi' => 'nullable|string',
        ]);

        $category->nama_kelompok = $request->nama_kelompok;
        $category->deskripsi = $request->deskripsi;

        if ($request->hasFile('gambar')) {
            // Hapus gambar lama
            if ($category->gambar && file_exists(public_path('images/category/' . $category->gambar))) {
                unlink(public_path('images/category/' . $category->gambar));
            }

            $file = $request->file('gambar');
            $filename = $category->kode_kelompok . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/category'), $filename);
            $category->gambar = $filename;
        }

        $category->save();

        return redirect()->route('admin.category-images.index')
            ->with('success', 'Gambar kelompok berhasil diupdate');
    }

    public function destroy($id)
    {
        $category = PartCategoryImage::findOrFail($id);

        // Hapus gambar
        if ($category->gambar && file_exists(public_path('images/category/' . $category->gambar))) {
            unlink(public_path('images/category/' . $category->gambar));
        }

        $category->delete();

        return redirect()->route('admin.category-images.index')
            ->with('success', 'Gambar kelompok berhasil dihapus');
    }

    public function getCategories()
    {
        // API untuk mendapatkan list kelompok dari DMS
        $categories = DB::connection('pgsql_dms')
            ->table('public.tbldetail_sub_kelompok_part_id')
            ->where('active', true)
            ->orderBy('detail_sub_kelompok_part')
            ->get();

        return response()->json($categories);
    }
}
