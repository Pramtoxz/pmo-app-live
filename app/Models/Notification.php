<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'pmov2.notifikasi';

    protected $fillable = [
        'kd_toko',
        'judul',
        'pesan',
        'tipe',
        'sudah_dibaca',
    ];

    protected $casts = [
        'sudah_dibaca' => 'boolean',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'kd_toko', 'kd_toko');
    }
}
