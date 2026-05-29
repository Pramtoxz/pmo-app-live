<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $table = 'pmov2.pesanan';

    protected $fillable = [
        'nomor_pesanan',
        'toko_id',
        'total_harga',
        'total_diskon',
        'grand_total',
        'status',
        'catatan',
        'ref_so_id',
        'ref_so_number',
        'tanggal_submit',
        'tanggal_approve',
    ];

    protected $casts = [
        'total_harga' => 'decimal:2',
        'total_diskon' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'tanggal_submit' => 'datetime',
        'tanggal_approve' => 'datetime',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'toko_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'pesanan_id');
    }
}
