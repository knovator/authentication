<?php

namespace App\Modules\Sales\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class SalesOrderQuantity
 * @package App\Modules\Sales\Models
 */
class SalesOrderQuantity extends Model
{

    use SoftDeletes;

    public $timestamps = false;

    protected $table = 'sales_orders_quantities';

    protected $fillable = [
        'fiddle_no',
        'sales_order_recipe_id',
        'thread_color_id',
        'kg_qty',
    ];
}
