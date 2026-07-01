<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MSendHO extends Model
{
    protected $connection = 'pgsql_dms';
    protected $table = 'H3.tb_send_ho';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'tgl_kirim_akhir',
        'tgl_mulai_kirim',
        'tgl_hot_awal',
        'pesan_hotline',
        'jam',
    ];

    protected $casts = [
        'tgl_kirim_akhir' => 'date',
    ];
}
