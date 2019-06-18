<?php

namespace App\Modules\Stock\Models;


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
        'partial_order_id'
    ];

}
