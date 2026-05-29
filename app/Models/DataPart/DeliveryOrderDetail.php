<?php

namespace App\Models\DataPart;

use Illuminate\Database\Eloquent\Model;

class DeliveryOrderDetail extends Model
{
    protected $connection = 'pgsql_dms';
    protected $table = 'data_part.tbldo_detail';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'fk_do',
        'fk_part',
        'qty_do',
        'harga',
        'diskon',
        'total_harga',
        'fk_tipe',
    ];

    protected $casts = [
        'qty_do' => 'integer',
        'harga' => 'decimal:2',
        'diskon' => 'decimal:2',
        'total_harga' => 'decimal:2',
    ];

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class, 'fk_do', 'no_do');
    }

    public function part()
    {
        return $this->belongsTo(\App\Models\PublicSchema\Part::class, 'fk_part', 'kd_part');
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class, 'fk_part', 'kode_part');
    }
}
