<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PopularPart extends Model
{
    protected $table = 'pmov2.part_populer';

    protected $fillable = [
        'kode_part',
        'total_qty_terjual',
        'total_order',
        'total_omzet',
        'peringkat',
        'tanggal_generate',
    ];

    protected $casts = [
        'total_omzet' => 'decimal:2',
        'tanggal_generate' => 'datetime',
    ];

    public function part()
    {
        return $this->belongsTo(\App\Models\PublicSchema\Part::class, 'kode_part', 'kd_part');
    }
}
