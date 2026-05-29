<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KatalogKendaraan;
use Illuminate\Http\Request;

class KatalogController extends Controller
{
    public function index()
    {
        $katalogs = KatalogKendaraan::orderBy('created_at', 'desc')->get();
        return view('admin.katalog.index', compact('katalogs'));
    }

    public function create()
    {
        return view('admin.katalog.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_motor' => 'required|string|max:20',
            'nama_motor' => 'required|string|max:100',
            'tahun_motor' => 'nullable|string',
            'no_rangka' => 'nullable|string',
            'kategori' => 'required|in:M,C,S,A',
            'file_pdf' => 'required|file|mimes:pdf|max:10240',
        ]);

        try {
            if (!file_exists(public_path('pdf'))) {
                mkdir(public_path('pdf'), 0755, true);
            }

            $file = $request->file('file_pdf');
            $filename = $request->kode_motor . '_' . time() . '.pdf';
            $file->move(public_path('pdf'), $filename);

            KatalogKendaraan::create([
                'kode_motor' => $request->kode_motor,
                'nama_motor' => $request->nama_motor,
                'tahun_motor' => $request->tahun_motor,
                'no_rangka' => $request->no_rangka ?: '-',
                'nama_file' => $filename,
                'kategori' => $request->kategori,
                'is_active' => $request->has('is_active'),
            ]);

            return redirect()->route('admin.katalog.index')
                ->with('success', 'Katalog berhasil ditambahkan');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Gagal menambahkan katalog: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $katalog = KatalogKendaraan::findOrFail($id);
        return view('admin.katalog.edit', compact('katalog'));
    }

    public function update(Request $request, $id)
    {
        $katalog = KatalogKendaraan::findOrFail($id);

        $request->validate([
            'nama_motor' => 'required|string|max:100',
            'tahun_motor' => 'nullable|string',
            'no_rangka' => 'nullable|string',
            'kategori' => 'required|in:M,C,S,A',
            'file_pdf' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        try {
            $katalog->nama_motor = $request->nama_motor;
            $katalog->tahun_motor = $request->tahun_motor;
            $katalog->no_rangka = $request->no_rangka ?: '-';
            $katalog->kategori = $request->kategori;
            $katalog->is_active = $request->has('is_active');

            if ($request->hasFile('file_pdf')) {
                if (file_exists(public_path('pdf/' . $katalog->nama_file))) {
                    unlink(public_path('pdf/' . $katalog->nama_file));
                }

                $file = $request->file('file_pdf');
                $filename = $katalog->kode_motor . '_' . time() . '.pdf';
                $file->move(public_path('pdf'), $filename);
                $katalog->nama_file = $filename;
            }

            $katalog->save();

            return redirect()->route('admin.katalog.index')
                ->with('success', 'Katalog berhasil diupdate');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Gagal mengupdate katalog: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $katalog = KatalogKendaraan::findOrFail($id);
            
            if (file_exists(public_path('pdf/' . $katalog->nama_file))) {
                unlink(public_path('pdf/' . $katalog->nama_file));
            }

            $katalog->delete();

            return redirect()->route('admin.katalog.index')
                ->with('success', 'Katalog berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('admin.katalog.index')
                ->with('error', 'Gagal menghapus katalog: ' . $e->getMessage());
        }
    }
}
