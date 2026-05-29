<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'pmov2.gambar_part';
    protected $primaryKey = 'kode_part';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_part',
        'nama',
        'deskripsi',
        'gambar',
    ];

    public function part()
    {
        return $this->belongsTo(\App\Models\PublicSchema\Part::class, 'kode_part', 'kd_part');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'kode_part', 'kode_part');
    }
}
