<?php

namespace App\Modules\Purchase\Models;

use App\Exceptions\UnloadedRelationException;
use App\Modules\Customer\Models\Customer;
use App\Modules\Stock\Models\Stock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Knovators\Masters\Models\Master;
use Knovators\Support\Traits\HasModelEvent;

/**
 * Class PurchaseOrder
 * @package App\Modules\Purchase\Models
 */
class PurchaseOrder extends Model
{

    use SoftDeletes, HasModelEvent;

    protected $table = 'purchase_orders';

    protected $fillable = [
        'order_no',
        'order_date',
        'customer_id',
        'status_id',
        'created_by',
        'deleted_by',
        'challan_no',
        'total_kg',
    ];


    protected $hidden = [
        'created_by',
        'deleted_by',
        'deleted_at',
        //        'created_at',
        'updated_at'
    ];


    public static function boot() {
        parent::boot();
        self::creatingEvent();
        static::deleting(function (PurchaseOrder $model) {
            $model->threads()->delete();
            $model->orderStocks()->delete();
        });
        self::deletedEvent();
    }


    /**
     * @return mixed
     */
    public function threads() {
        return $this->hasMany(PurchaseOrderThread::class, 'purchase_order_id', 'id');
    }

    /**
     * @return mixed
     */
    public function orderStocks() {
        return $this->morphMany(Stock::class, 'order', 'order_type', 'order_id', 'id');
    }

    /**
     * @return mixed
     */
    public function quantity() {
        return $this->threadQty();
    }

    /**
     * @return mixed
     */
    public function threadQty() {
        return $this->hasOne(PurchaseOrderThread::class, 'purchase_order_id', 'id')
                    ->groupBy('purchase_order_id')
                    ->selectRaw('sum(kg_qty) as total,purchase_order_id');
    }

    /**
     * @return mixed
     */
    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    /**
     * @return mixed
     */
    public function status() {
        return $this->belongsTo(Master::class, 'status_id', 'id');
    }

    /**
     * @return mixed
     */
    public function deliveries() {
        return $this->hasMany(PurchaseDelivery::class, 'purchase_order_id', 'id');
    }


    /**
     * @return mixed
     */
    public function partialOrders() {
        return $this->hasManyThrough(PurchasePartialOrder::class, PurchaseDelivery::class,
            'purchase_order_id', 'delivery_id', 'id', 'id');
    }


    /**
     * @return mixed
     */
    public function deliveredMeters() {
        return $this->hasOne(PurchaseDelivery::class, 'purchase_order_id', 'id')
                    ->selectRaw('SUM(total_kg) as total,purchase_order_id')
                    ->groupBy('purchase_order_id');
    }

    /**
     * @return UnloadedRelationException
     */
    public function getPendingKgAttribute() {

        if (!$this->relationLoaded('deliveredMeters')) {
            throw UnloadedRelationException::make(get_class($this), 'deliveredMeters');
        }

        $total = $this->total_kg;

        if (!is_null($this->deliveredMeters)) {
            $total = $total - $this->deliveredMeters->total;
        }

        return $total;
    }
}
