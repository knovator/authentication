<?php

namespace App\Modules\Purchase\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class PurchasePartialOrder
 * @package App\Modules\Purchase\Models
 */
class PurchasePartialOrder extends Model
{

    public $timestamps = false;

    protected $table = 'purchase_partial_orders';


    protected $fillable = [
        'purchase_order_thread_id',
        'kg_qty',
        'delivery_id',
    ];


    /**
     * @return mixed
     */
    public function delivery() {
        return $this->belongsTo(PurchaseDelivery::class, 'delivery_id',
            'id');
    }

}
