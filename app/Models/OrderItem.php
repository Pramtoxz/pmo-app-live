<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'pmov2.item_pesanan';

    protected $fillable = [
        'pesanan_id',
        'gambar_part_id',
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

    public function order()
    {
        return $this->belongsTo(Order::class, 'pesanan_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'gambar_part_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->subtotal = ($item->harga * $item->qty) - $item->diskon;
        });
    }
}
