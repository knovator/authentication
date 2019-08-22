<?php

namespace App\Modules\Stock\Models;


use App\Models\Master;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Stock
 * @package App\Modules\Stock\Models
 */
class Stock extends Model
{

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
        'order_recipe_id'
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
    public function order() {
        return $this->morphTo('order', 'order_type', 'order_id');
    }


}
