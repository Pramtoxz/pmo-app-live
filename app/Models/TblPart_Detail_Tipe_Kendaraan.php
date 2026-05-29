<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TblPart_Detail_Tipe_Kendaraan extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $table = 'public.tblpart_detail_tipe_kendaraan';
    protected $primaryKey = 'id_api';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'kd_toko',
        'fk_tipe_kendaraan',
        'fk_part',
    ];
}
