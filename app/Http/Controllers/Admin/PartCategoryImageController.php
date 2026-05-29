<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PartCategoryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PartCategoryImageController extends Controller
{
    public function index()
    {
        $categories = PartCategoryImage::orderBy('nama_kelompok')->paginate(20);
        
        return response()->json([
            'success' => true,
            'data' => $categories->map(function($cat) {
                return [
                    'id' => $cat->id,
                    'kode_kelompok' => $cat->kode_kelompok,
                    'nama_kelompok' => $cat->nama_kelompok,
                    'gambar' => $cat->gambar ? url('images/category/' . $cat->gambar) : null,
                    'deskripsi' => $cat->deskripsi,
                    'has_image' => !is_null($cat->gambar),
                ];
            }),
            'pagination' => [
                'current_page' => $categories->currentPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
                'last_page' => $categories->lastPage(),
            ]
        ]);
    }

    public function show($id)
    {
        $category = PartCategoryImage::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $category->id,
                'kode_kelompok' => $category->kode_kelompok,
                'nama_kelompok' => $category->nama_kelompok,
                'gambar' => $category->gambar ? url('images/category/' . $category->gambar) : null,
                'deskripsi' => $category->deskripsi,
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kelompok' => 'sometimes|string|max:255',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $category = PartCategoryImage::findOrFail($id);

        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada
            if ($category->gambar && file_exists(public_path('images/category/' . $category->gambar))) {
                unlink(public_path('images/category/' . $category->gambar));
            }

            $file = $request->file('gambar');
            $filename = $category->kode_kelompok . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/category'), $filename);
            
            $category->gambar = $filename;
        }

        if ($request->has('nama_kelompok')) {
            $category->nama_kelompok = $request->nama_kelompok;
        }

        if ($request->has('deskripsi')) {
            $category->deskripsi = $request->deskripsi;
        }

        $category->save();

        return response()->json([
            'success' => true,
            'message' => 'Gambar kelompok berhasil diupdate',
            'data' => [
                'id' => $category->id,
                'kode_kelompok' => $category->kode_kelompok,
                'nama_kelompok' => $category->nama_kelompok,
                'gambar' => $category->gambar ? url('images/category/' . $category->gambar) : null,
                'deskripsi' => $category->deskripsi,
            ]
        ]);
    }

    public function deleteImage($id)
    {
        $category = PartCategoryImage::findOrFail($id);

        if ($category->gambar && file_exists(public_path('images/category/' . $category->gambar))) {
            unlink(public_path('images/category/' . $category->gambar));
        }

        $category->gambar = null;
        $category->save();

        return response()->json([
            'success' => true,
            'message' => 'Gambar berhasil dihapus'
        ]);
    }
}
