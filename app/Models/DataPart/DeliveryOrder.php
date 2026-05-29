<?php

namespace App\Models\DataPart;

use Illuminate\Database\Eloquent\Model;

class DeliveryOrder extends Model
{
    protected $connection = 'pgsql_dms';
    protected $table = 'data_part.tbldo';
    protected $primaryKey = 'no_do';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'no_do',
        'tgl_do',
        'fk_so',
        'jenis_do',
        'fk_gudang_part',
        'keterangan',
        'total_gross',
        'total_diskon',
        'grand_total',
        'fk_customer',
        'status_approve_reject',
        'alasan_approve_reject',
        'approve_by',
        'tgl_approve',
        'is_last',
    ];

    protected $casts = [
        'tgl_do' => 'datetime',
        'tgl_approve' => 'datetime',
        'total_gross' => 'decimal:2',
        'total_diskon' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'fk_so', 'no_so');
    }

    public function details()
    {
        return $this->hasMany(DeliveryOrderDetail::class, 'fk_do', 'no_do');
    }

    public function invoice()
    {
        return $this->hasOne(\App\Models\DataFA\Invoice::class, 'fk_do_part', 'no_do');
    }
}
