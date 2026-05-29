<?php

namespace App\Models\DataPart;

use Illuminate\Database\Eloquent\Model;

class SalesOrderDetail extends Model
{
    protected $connection = 'pgsql_dms';
    protected $table = 'data_part.tblso_detail';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'fk_so',
        'fk_part',
        'harga',
        'qty_so',
        'total_harga',
        'qty_sisa',
        'fk_tipe',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'total_harga' => 'decimal:2',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'fk_so', 'no_so');
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
