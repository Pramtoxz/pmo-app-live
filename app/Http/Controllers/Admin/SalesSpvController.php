<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesSupervisor;
use App\Helpers\Export\SalesSupervisorSheetExport;
use App\Helpers\Import\SalesSupervisorImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SalesSpvController extends Controller
{
    public function index()
    {
        $salesSpv = SalesSupervisor::orderBy('nama')->get();
        return view('admin.sales-spv.index', compact('salesSpv'));
    }

    public function show($id)
    {
        $salesSpv = SalesSupervisor::findOrFail($id);
        
        $user = DB::connection('pgsql')
            ->table('pmov2.users')
            ->where('name', $salesSpv->nama)
            ->whereIn('role', ['sales', 'supervisor'])
            ->first();
        
        $userEmail = $user ? $user->email : '-';
        
        return view('admin.sales-spv.show', compact('salesSpv', 'userEmail'));
    }

    public function export()
    {
        return SalesSupervisorSheetExport::download();
    }

    public function import(Request $request)
    {
        $ext = strtolower($request->file('file') ? $request->file('file')->getClientOriginalExtension() : '');
        if (!in_array($ext, ['csv', 'txt', 'xlsx'])) {
            return redirect()->route('admin.sales-spv.index')
                ->with('error', 'Format file tidak valid. Gunakan CSV atau Excel (.xlsx).');
        }
        $request->validate([
            'file' => 'required|file|max:5120',
        ]);

        try {
            $result = SalesSupervisorImport::process($request->file('file')->getPathname());

            $message = 'Data sales & supervisor berhasil diimport! (' . $result['processed'] . ' baris)';
            if (!empty($result['errors'])) {
                $message .= ' | ' . count($result['errors']) . ' error diabaikan.';
            }

            return redirect()->route('admin.sales-spv.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.sales-spv.index')
                ->with('error', 'Gagal import data: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $salesSpv = SalesSupervisor::findOrFail($id);
            $salesSpv->delete();

            return redirect()->route('admin.sales-spv.index')
                ->with('success', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('admin.sales-spv.index')
                ->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
