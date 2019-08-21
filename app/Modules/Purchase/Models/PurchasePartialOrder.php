<?php

namespace App\Modules\Purchase\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class PurchasePartialOrder
 * @package App\Modules\Purchase\Models
 */
class PurchasePartialOrder extends Model
{

    use SoftDeletes;

    public $timestamps = false;

    protected $table = 'purchase_partial_orders';


    protected $fillable = [
        'purchase_order_thread_id',
        'kg_qty',
        'delivery_id',
    ];

    protected $hidden = [
        'deleted_at'
    ];

}
