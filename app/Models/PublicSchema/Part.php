<?php

namespace App\Models\PublicSchema;

use Illuminate\Database\Eloquent\Model;

class Part extends Model
{
    protected $connection = 'pgsql_dms';
    protected $table = 'public.tblpart';
    protected $primaryKey = 'kd_part';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'kd_part',
        'nm_part',
        'het',
        'min_stok',
        'fk_detail_sub_kelompok_part',
        'part_active',
    ];

    protected $casts = [
        'het' => 'decimal:2',
        'min_stok' => 'integer',
        'part_active' => 'boolean',
    ];

    public function stock()
    {
        $bulan = (int) date('n');
        $tahun = (int) date('Y');

        return $this->hasMany(\App\Models\DataPart\StockPart::class, 'fk_part', 'kd_part')
            ->whereIn('fk_gudang', \App\Models\DataPart\StockPart::ALLOWED_WAREHOUSES)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun);
    }

    public function category()
    {
        return $this->belongsTo(PartCategory::class, 'fk_detail_sub_kelompok_part', 'kd_detail_sub_kelompok_part');
    }

    public function getStockSummary($bulan = null, $tahun = null)
    {
        $targetBulan = (int) ($bulan ?? date('n'));
        $targetTahun = (int) ($tahun ?? date('Y'));

        $stocks = ($this->relationLoaded('stock') && $targetBulan === (int) date('n') && $targetTahun === (int) date('Y'))
            ? $this->stock
            : $this->hasMany(\App\Models\DataPart\StockPart::class, 'fk_part', 'kd_part')
                ->whereIn('fk_gudang', \App\Models\DataPart\StockPart::ALLOWED_WAREHOUSES)
                ->where('bulan', $targetBulan)
                ->where('tahun', $targetTahun)
                ->get();

        $totalOnHand = (float) $stocks->sum('qty_on_hand');
        $totalBooking = (float) $stocks->sum('qty_booking');
        $minStock = (is_numeric($this->min_stok) && (int) $this->min_stok > 0) ? (int) $this->min_stok : 0;

        $available = ($totalOnHand - $totalBooking) - $minStock;
        $isReady = $available >= 1;

        return (object) [
            'qty_on_hand' => $totalOnHand,
            'qty_booking' => $totalBooking,
            'min_stock' => $minStock,
            'available' => $available,
            'available_qty' => max(0, $available),
            'is_available' => $isReady,
            'is_ready' => $isReady,
        ];
    }

    public function getCurrentStock($bulan = null, $tahun = null)
    {
        return $this->getStockSummary($bulan, $tahun);
    }
}
