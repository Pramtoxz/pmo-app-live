<?php

namespace App\Models\DataFA;

use Illuminate\Database\Eloquent\Model;
use App\Models\PublicSchema\Part;
use App\Models\Product;

class InvoiceDetail extends Model
{
    protected $connection = 'pgsql_dms';
    protected $table = 'data_fa.tblinvoice_dealer_part_detail';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'fk_invoice',
        'fk_part',
        'qty_do',
        'harga',
        'diskon',
    ];

    protected $casts = [
        'qty_do' => 'integer',
        'harga' => 'decimal:2',
        'diskon' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'fk_invoice', 'pk_id_dealer_part');
    }

    public function part()
    {
        return $this->belongsTo(Part::class, 'fk_part', 'kd_part');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'fk_part', 'kode_part');
    }

    public function getSubtotalAttribute()
    {
        return ($this->harga - $this->diskon) * $this->qty_do;
    }
}
