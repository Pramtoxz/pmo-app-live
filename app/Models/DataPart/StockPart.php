<?php

namespace App\Models\DataPart;

use Illuminate\Database\Eloquent\Model;

class StockPart extends Model
{
    protected $connection = 'pgsql_dms';
    protected $table = 'data_part.tblstock_part';
    public $timestamps = false;

    protected $fillable = [
        'fk_part',
        'fk_gudang',
        'qty_booking',
        'qty_on_hand',
        'qty_intransit',
        'hpp_terakhir',
        'on_sales',
        'on_koreksi_sales',
    ];

    public function part()
    {
        return $this->belongsTo(\App\Models\PublicSchema\Part::class, 'fk_part', 'kd_part');
    }

    public function getAvailableAttribute()
    {
        $part = $this->part;
        $minStock = $part ? $part->min_stok : 0;
        return ($this->qty_on_hand - $this->qty_booking) - $minStock;
    }

    public function getIsAvailableAttribute()
    {
        return $this->available >= 1;
    }
}
