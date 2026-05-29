<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TargetProspek extends Model
{
    protected $connection = 'pgsql_nms';
    protected $table = 'H1_DOS.tbl_target_prospek';
    protected $primaryKey = 'id_flp_sales';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_flp_sales',
        'nama_sales',
        'target',
        'bulan',
        'tahun',
        'id_koordinator',
        'fk_dealer',
    ];

    public static function getTargetProspekComparison($idFlp, $bulan = null, $tahun = null)
    {
        $bulan = $bulan ?? date('n');
        $tahun = $tahun ?? date('Y');

        $target = DB::connection('pgsql_nms')
            ->table('H1_DOS.tbl_target_prospek')
            ->where('id_flp_sales', $idFlp)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->first();

        $actual = DB::connection('pgsql_nms')
            ->table('H1_DOS.guestbook')
            ->where('id_flp', $idFlp)
            ->whereRaw('EXTRACT(MONTH FROM "Tanggal") = ?', [$bulan])
            ->whereRaw('EXTRACT(YEAR FROM "Tanggal") = ?', [$tahun])
            ->count();

        $targetValue = $target ? (int)$target->target : 0;
        $selisih = $actual - $targetValue;
        $persentase = $targetValue > 0 ? round(($actual / $targetValue) * 100, 2) : 0;

        return [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'target' => $targetValue,
            'actual' => $actual,
            'selisih' => $selisih,
            'persentase' => $persentase,
            'tercapai' => $actual >= $targetValue,
        ];
    }
}
