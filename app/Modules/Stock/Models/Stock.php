<?php

namespace App\Modules\Stock\Models;


use App\Constants\Master as MasterConstant;
use App\Models\Master;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Stock
 * @package App\Modules\Stock\Models
 */
class Stock extends Model
{

    public const AVAILABLE_STATUSES = [
        MasterConstant::PO_DELIVERED,
        MasterConstant::SO_MANUFACTURING,
        MasterConstant::SO_DELIVERED,
        MasterConstant::WASTAGE_DELIVERED,
        MasterConstant::WASTAGE_PENDING,
    ];
    public $morphOrderTypes = [
        'yarn',
        'purchase',
        'sales',
        'wastage',
    ];
    protected $table = 'stocks';
    protected $fillable = [
        'product_type',
        'product_id',
        'order_type',
        'order_id',
        'kg_qty',
        'status_id',
        'partial_order_id',
        'partial_order_type',
        'order_recipe_id',
        'purchased_thread_id',
        'wastage_recipe_id',
    ];

    /**
     * @return mixed
     */
    public function status() {
        return $this->belongsTo(Master::class, 'status_id', 'id');
    }


    /**
     * @return mixed
     */
    public function product() {
        return $this->morphTo('product', 'product_type', 'product_id');
    }

    /**
     * @return mixed
     */
    public function order() {
        return $this->morphTo('order', 'order_type', 'order_id');
    }


}
