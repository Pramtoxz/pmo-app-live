<?php

namespace App\Models\DataFA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\DataPart\DeliveryOrder;
use App\Models\DataPart\SalesOrder;

class Invoice extends Model
{
    protected $connection = 'pgsql_dms';
    protected $table = 'data_fa.tblinvoice_dealer_part';
    protected $primaryKey = 'pk_id_dealer_part';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'pk_id_dealer_part',
        'no_faktur',
        'tgl_faktur',
        'fk_do_part',
    ];

    protected $casts = [
        'tgl_faktur' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(InvoiceDetail::class, 'fk_invoice', 'pk_id_dealer_part');
    }

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class, 'fk_do_part', 'no_do');
    }

    public function accountReceivable()
    {
        return $this->hasOne(AccountReceivable::class, 'no_transaksi', 'no_faktur');
    }

    /**
     * Get collections for a shop with AR data
     * Outstanding: Current month only (AR data is copied monthly) with pagination
     * Paid: Only with date range filter
     */
    public static function getCollections($kdToko, $dari = null, $sampai = null, $page = 1, $perPage = 50)
    {
        $bulanSekarang = date('n'); // 1-12
        $tahunSekarang = date('Y');

        // Get outstanding summary (current month only)
        $outstandingSummary = DB::connection('pgsql_dms')
            ->table('data_fa.tblinvoice_dealer_part as a')
            ->leftJoin('data_fa.tblar as c', 'c.no_transaksi', '=', 'a.no_faktur')
            ->leftJoin('data_part.tbldo as d', 'a.fk_do_part', '=', 'd.no_do')
            ->leftJoin('data_part.tblso as e', 'e.no_so', '=', 'd.fk_so')
            ->selectRaw('COUNT(DISTINCT a.no_faktur) as total_count, SUM(c.saldo) as total_saldo, SUM(c.jumlah_transaksi) as total_nilai')
            ->where('e.fk_toko', $kdToko)
            ->where('c.bulan', $bulanSekarang)
            ->where('c.tahun', $tahunSekarang)
            ->where('c.saldo', '>', 0)
            ->where('a.no_faktur', 'LIKE', '%FAK%')
            ->whereIn('e.jenis_so', ['Other', 'Oli Regular'])
            ->whereNotNull('e.fk_toko')
            ->first();

        // Get outstanding (current month with pagination)
        $offset = ($page - 1) * $perPage;
        $outstanding = DB::connection('pgsql_dms')
            ->table('data_fa.tblinvoice_dealer_part as a')
            ->leftJoin('data_fa.tblinvoice_dealer_part_detail as b', 'a.pk_id_dealer_part', '=', 'b.fk_invoice')
            ->leftJoin('data_fa.tblar as c', 'c.no_transaksi', '=', 'a.no_faktur')
            ->leftJoin('data_part.tbldo as d', 'a.fk_do_part', '=', 'd.no_do')
            ->leftJoin('data_part.tblso as e', 'e.no_so', '=', 'd.fk_so')
            ->selectRaw("
                a.tgl_faktur,
                e.jenis_pembayaran,
                a.no_faktur,
                a.fk_do_part,
                e.no_so,
                ROUND(SUM(b.qty_do * (b.harga - b.diskon))) as nilai_faktur,
                COALESCE(c.saldo, 0) as saldo,
                'Outstanding' as status
            ")
            ->where('e.fk_toko', $kdToko)
            ->where('c.bulan', $bulanSekarang)
            ->where('c.tahun', $tahunSekarang)
            ->where('c.saldo', '>', 0)
            ->where('a.no_faktur', 'LIKE', '%FAK%')
            ->whereIn('e.jenis_so', ['Other', 'Oli Regular'])
            ->whereNotNull('e.fk_toko')
            ->groupBy(['a.tgl_faktur', 'a.no_faktur', 'a.fk_do_part', 'a.pk_id_dealer_part', 'c.saldo', 'e.no_so', 'e.fk_toko', 'e.jenis_so', 'e.jenis_pembayaran'])
            ->orderBy('a.tgl_faktur', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        // Get paid - only if date range is provided, otherwise empty
        $paid = collect([]);
        
        if ($dari && $sampai) {
            $paid = DB::connection('pgsql_dms')
                ->table('data_fa.tblinvoice_dealer_part as a')
                ->leftJoin('data_fa.tblinvoice_dealer_part_detail as b', 'a.pk_id_dealer_part', '=', 'b.fk_invoice')
                ->leftJoin('data_fa.tblar as c', 'c.no_transaksi', '=', 'a.no_faktur')
                ->leftJoin('data_part.tbldo as d', 'a.fk_do_part', '=', 'd.no_do')
                ->leftJoin('data_part.tblso as e', 'e.no_so', '=', 'd.fk_so')
                ->selectRaw("
                    a.tgl_faktur,
                    e.jenis_pembayaran,
                    a.no_faktur,
                    a.fk_do_part,
                    e.no_so,
                    ROUND(SUM(b.qty_do * (b.harga - b.diskon))) as nilai_faktur,
                    COALESCE(c.saldo, 0) as saldo,
                    'Paid' as status
                ")
                ->where('e.fk_toko', $kdToko)
                ->where('c.saldo', '=', 0)
                ->whereDate('a.tgl_faktur', '>=', $dari)
                ->whereDate('a.tgl_faktur', '<=', $sampai)
                ->where('a.no_faktur', 'LIKE', '%FAK%')
                ->whereIn('e.jenis_so', ['Other', 'Oli Regular'])
                ->whereNotNull('e.fk_toko')
                ->groupBy(['a.tgl_faktur', 'a.no_faktur', 'a.fk_do_part', 'a.pk_id_dealer_part', 'c.saldo', 'e.no_so', 'e.fk_toko', 'e.jenis_so', 'e.jenis_pembayaran'])
                ->orderBy('a.tgl_faktur', 'desc')
                ->limit(100)
                ->get();
        }

        // Return as array with summary
        return [
            'collections' => $outstanding->merge($paid),
            'outstandingSummary' => $outstandingSummary
        ];
    }

    /**
     * Get collections by date range
     */
    public static function getCollectionsByDateRange($kdToko, $dari, $sampai)
    {
        $query = self::selectRaw("
            a.tgl_faktur, 
            e.fk_toko, 
            e.jenis_so, 
            e.jenis_pembayaran, 
            a.no_faktur,
            a.fk_do_part, 
            e.no_so, 
            a.pk_id_dealer_part,
            ROUND(SUM(b.qty_do * (b.harga - b.diskon))) as nilai_faktur, 
            COALESCE(c.saldo, 0) as saldo,
            CASE 
                WHEN COALESCE(c.saldo, 0) = 0 THEN 'Paid'
                WHEN c.saldo > 0 THEN 'Outstanding'
                ELSE 'Unknown'
            END as status
        ")
        ->from('data_fa.tblinvoice_dealer_part as a')
        ->leftJoin('data_fa.tblinvoice_dealer_part_detail as b', 'a.pk_id_dealer_part', '=', 'b.fk_invoice')
        ->leftJoin('data_fa.tblar as c', 'c.no_transaksi', '=', 'a.no_faktur')
        ->leftJoin('data_part.tbldo as d', 'a.fk_do_part', '=', 'd.no_do')
        ->leftJoin('data_part.tblso as e', 'e.no_so', '=', 'd.fk_so')
        ->where('e.fk_toko', $kdToko)
        ->whereDate('a.tgl_faktur', '>=', $dari)
        ->whereDate('a.tgl_faktur', '<=', $sampai)
        ->where('a.no_faktur', 'LIKE', '%FAK%')
        ->whereIn('e.jenis_so', ['Other', 'Oli Regular'])
        ->groupBy([
            'a.tgl_faktur', 
            'a.no_faktur', 
            'a.fk_do_part', 
            'a.pk_id_dealer_part', 
            'c.saldo', 
            'e.no_so', 
            'e.fk_toko', 
            'e.jenis_so', 
            'e.jenis_pembayaran'
        ])
        ->orderBy('a.tgl_faktur', 'desc');

        return $query->get();
    }

    /**
     * Get invoice detail with items
     */
    public static function getInvoiceDetail($noFaktur)
    {
        $invoice = self::selectRaw("
            a.pk_id_dealer_part,
            a.no_faktur,
            a.tgl_faktur,
            a.fk_do_part
        ")
        ->from('data_fa.tblinvoice_dealer_part as a')
        ->where('a.no_faktur', $noFaktur)
        ->first();

        if (!$invoice) {
            return null;
        }

        // Get invoice details from DO (Delivery Order)
        $details = DB::connection('pgsql_dms')
            ->table('data_part.tbldo_detail as a')
            ->leftJoin('public.tblpart as b', 'a.fk_part', '=', 'b.kd_part')
            ->select([
                'a.fk_part',
                'a.qty_do',
                'a.harga',
                'a.diskon',
                DB::raw('COALESCE(b.nm_part, \'-\') as part_name'),
                DB::raw('(a.harga - a.diskon) * a.qty_do as subtotal')
            ])
            ->where('a.fk_do', $invoice->fk_do_part)
            ->get();

        $invoice->details_data = $details;

        // Load relationships for other data
        $invoice = self::with([
            'deliveryOrder.salesOrder',
            'accountReceivable'
        ])
        ->where('no_faktur', $noFaktur)
        ->first();

        if ($invoice) {
            $invoice->details_data = $details;
        }

        return $invoice;
    }
}
