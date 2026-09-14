<?php

namespace App\Models\DataPart;

use Illuminate\Database\Eloquent\Model;

class StockPart extends Model
{
    protected $connection = 'pgsql_dms';
    protected $table = 'data_part.tblstock_part';
    public $timestamps = false;

    public const ALLOWED_WAREHOUSES = ['GDG-1', 'GDG-2'];

    protected $fillable = [
        'fk_part',
        'fk_gudang',
        'qty_booking',
        'qty_on_hand',
        'qty_intransit',
        'hpp_terakhir',
        'on_sales',
        'on_koreksi_sales',
        'bulan',
        'tahun',
    ];

    public function scopeAllowedWarehouses($query)
    {
        return $query->whereIn('fk_gudang', self::ALLOWED_WAREHOUSES);
    }

    public function scopeForPeriod($query, $bulan = null, $tahun = null)
    {
        $bulan = (int) ($bulan ?? date('n'));
        $tahun = (int) ($tahun ?? date('Y'));

        return $query->where('bulan', $bulan)->where('tahun', $tahun);
    }

    public function part()
    {
        return $this->belongsTo(\App\Models\PublicSchema\Part::class, 'fk_part', 'kd_part');
    }

    public function getAvailableAttribute()
    {
        $part = $this->part;
        $minStock = ($part && is_numeric($part->min_stok) && (int) $part->min_stok > 0) ? (int) $part->min_stok : 0;
        return ((float) $this->qty_on_hand - (float) $this->qty_booking) - $minStock;
    }

    public function getIsAvailableAttribute()
    {
        return $this->available >= 1;
    }
}
