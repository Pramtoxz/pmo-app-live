<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TblTipe_Kendaraan extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $table = 'public.tbltipe_kendaraan';
    protected $primaryKey = 'kd_tipe_kendaraan';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'kd_tipe_kendaraan',
        'desc_tipe_cust',
        'fk_jenis_kendaraan',
        'kd_ptm',
        'digit_no_mesin'
    ];
}
