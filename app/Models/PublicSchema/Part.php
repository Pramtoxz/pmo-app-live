<?php

namespace App\Models\PublicSchema;

use Illuminate\Database\Eloquent\Model;

class Part extends Model
{
    protected $connection = 'pgsql_dms';
    protected $table = 'public.tblpart';
    protected $primaryKey = 'kd_part';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'kd_part',
        'nm_part',
        'het',
        'min_stok',
        'fk_detail_sub_kelompok_part',
        'part_active',
    ];

    protected $casts = [
        'het' => 'decimal:2',
        'min_stok' => 'integer',
        'part_active' => 'boolean',
    ];

    public function stock()
    {
        return $this->hasMany(\App\Models\DataPart\StockPart::class, 'fk_part', 'kd_part');
    }

    public function category()
    {
        return $this->belongsTo(PartCategory::class, 'fk_detail_sub_kelompok_part', 'kd_detail_sub_kelompok_part');
    }

    public function getCurrentStock($bulan = null, $tahun = null)
    {
        return $this->stock()->first();
    }
}
