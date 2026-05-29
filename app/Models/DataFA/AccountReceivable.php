<?php

namespace App\Models\DataFA;

use Illuminate\Database\Eloquent\Model;

class AccountReceivable extends Model
{
    protected $connection = 'pgsql_dms';
    protected $table = 'data_fa.tblar';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'no_transaksi',
        'tgl_transaksi',
        'jenis_transaksi',
        'bulan',
        'tahun',
        'jumlah_transaksi',
        'jumlah_alokasi',
        'jumlah_approve',
        'saldo',
        'fk_cabang',
        'fk_jenis_cabang',
        'fk_customer',
        'is_data_migrasi',
        'fk_coa',
        'fk_faktur_pajak',
    ];

    protected $casts = [
        'tgl_transaksi' => 'datetime',
        'bulan' => 'integer',
        'tahun' => 'integer',
        'jumlah_transaksi' => 'decimal:2',
        'jumlah_alokasi' => 'decimal:2',
        'jumlah_approve' => 'decimal:2',
        'saldo' => 'decimal:2',
        'is_data_migrasi' => 'boolean',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'no_transaksi', 'no_faktur');
    }

    public function getIsPaidAttribute()
    {
        return $this->saldo == 0;
    }

    public function getIsOutstandingAttribute()
    {
        return $this->saldo > 0;
    }
}
