<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Serial extends Model
{
    protected $connection = 'pgsql_live';
    protected $table = 'public.tblserial';
    protected $primaryKey = 'name';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'counter',
        'last_date',
    ];

    protected $casts = [
        'counter' => 'integer',
        'last_date' => 'datetime',
    ];

    public static function generateSO()
    {
        return DB::connection('pgsql_live')->transaction(function () {
            $serial = self::where('name', 'POD-PD')
                ->lockForUpdate()
                ->first();

            if (!$serial) {
                $serial = self::create([
                    'name' => 'POD-PD',
                    'counter' => 0,
                    'last_date' => now(),
                ]);
            }

            $tahun = date('Y');
            $newCounter = $serial->counter + 1;
            $nomax = sprintf('%06d', $newCounter);
            $no_so = "$tahun/$nomax/POD-PD";

            $serial->update([
                'counter' => $newCounter,
                'last_date' => now(),
            ]);

            return $no_so;
        });
    }
}
