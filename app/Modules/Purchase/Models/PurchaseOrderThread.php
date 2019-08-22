<?php

namespace App\Modules\Purchase\Models;

use App\Modules\Thread\Models\ThreadColor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;

/**
 * Class PurchaseOrderThread
 * @package App\Modules\Purchase\Models
 */
class PurchaseOrderThread extends Model
{

    public $timestamps = false;

    use SoftDeletes;

    protected $table = 'purchase_order_threads';

    protected $fillable = [
        'thread_color_id',
        'purchase_order_id',
        'kg_qty'
    ];


    protected $hidden = [
        'deleted_at',
    ];


    /**
     * @return mixed
     */
    public function threadColor() {
        return $this->belongsTo(ThreadColor::class, 'thread_color_id', 'id');
    }

    /**
     * @return mixed
     */
    public function purchaseOrder() {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id', 'id');
    }


    /**
     * @return HasOne|Builder
     */
    public function partialOrders() {
        return $this->hasMany(PurchasePartialOrder::class, 'purchase_order_thread_id', 'id');
    }

    /**
     * @return HasOne|Builder
     */
    public function remainingQuantity() {
        return $this->hasOne(PurchasePartialOrder::class, 'purchase_order_thread_id', 'id')
                    ->selectRaw('purchase_order_thread_id,SUM(purchase_partial_orders.kg_qty) AS total')
                    ->groupBy('purchase_order_thread_id');
    }

    /**
     * @return mixed
     */
    public function getRemainingKgQtyAttribute() {

        if (!$this->relationLoaded('remainingQuantity')) {
            $this->load('remainingQuantity');
        }
        if (!is_null($this->remainingQuantity)) {
            return $this->kg_qty - $this->remainingQuantity->total;
        }

        return $this->kg_qty;
    }

}
