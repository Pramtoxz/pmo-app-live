<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shop extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $table = 'pmov2.tbltoko';
    protected $primaryKey = 'kd_toko';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'kd_toko',
        'toko',
        'no_telp',
        'alamat',
        'npwp',
        'kategori',
        'kd_ahm',
        'toko_active',
    ];

    protected $appends = ['nama', 'kode', 'aktif', 'no_hp', 'kota', 'provinsi'];

    protected $casts = [
        'toko_active' => 'boolean',
    ];

    // Accessor untuk compatibility dengan kode lama
    public function getNamaAttribute()
    {
        return $this->toko;
    }

    public function getKodeAttribute()
    {
        return $this->kd_toko;
    }

    public function getAktifAttribute()
    {
        return $this->toko_active;
    }

    public function getNoHpAttribute()
    {
        return $this->no_telp;
    }

    public function getKotaAttribute()
    {
        return null; // DMS tidak punya kolom kota
    }

    public function getProvinsiAttribute()
    {
        return null; // DMS tidak punya kolom provinsi
    }

    public function users()
    {
        return $this->hasMany(\App\Models\User::class, 'fk_toko', 'kd_toko');
    }
}
