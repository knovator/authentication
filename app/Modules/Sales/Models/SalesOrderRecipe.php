<?php

namespace App\Modules\Sales\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class SalesOrderRecipe
 * @package App\Modules\Sales\Models
 */
class SalesOrderRecipe extends Model
{

    use SoftDeletes;

    protected $table = 'sales_orders_recipes';

    protected $fillable = [
        'sales_order_id',
        'pcs',
        'meters',
        'total_meters',
        'recipe_id',
    ];

}
