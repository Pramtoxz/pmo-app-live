<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalesSupervisor extends Model
{
    use HasFactory;

    protected $table = 'pmov2.sales_supervisor';

    protected $fillable = [
        'nama',
        'kode_npk',
        'no_hp',
        'jabatan',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function tokoAsSales()
    {
        return $this->hasMany(Shop::class, 'fk_sales', 'kode_npk');
    }

    public function tokoAsSupervisor()
    {
        return $this->hasMany(Shop::class, 'fk_spv', 'kode_npk');
    }
}
