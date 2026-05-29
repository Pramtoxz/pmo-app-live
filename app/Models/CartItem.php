<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CartItem extends Model
{
    use HasFactory;

    protected $table = 'pmov2.item_keranjang';

    protected $fillable = [
        'keranjang_id',
        'kode_part',
        'qty',
        'harga',
        'diskon',
        'subtotal',
    ];

    protected $casts = [
        'qty' => 'integer',
        'harga' => 'decimal:2',
        'diskon' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'keranjang_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'kode_part', 'kode_part');
    }

    public function part()
    {
        return $this->belongsTo(\App\Models\PublicSchema\Part::class, 'kode_part', 'kd_part');
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->subtotal = ($item->harga * $item->qty) - $item->diskon;
        });
    }
}
