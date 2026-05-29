<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KatalogKendaraan extends Model
{
    protected $table = 'pmov2.katalog_kendaraan';

    protected $fillable = [
        'kode_motor',
        'nama_motor',
        'tahun_motor',
        'no_rangka',
        'nama_file',
        'kategori',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getTahunMotorArrayAttribute()
    {
        if (!$this->tahun_motor) return [];
        return array_filter(array_map('trim', explode(',', $this->tahun_motor)));
    }

    public function getNoRangkaArrayAttribute()
    {
        if (!$this->no_rangka || $this->no_rangka === '-') return [];
        return array_filter(array_map('trim', explode(',', $this->no_rangka)));
    }

    public function getPdfPathAttribute()
    {
        return 'pdf/' . $this->nama_file;
    }
}
