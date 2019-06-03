<?php

namespace App\Modules\Sales\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class RecipePartialOrder
 * @package App\Modules\Sales\Models
 */
class RecipePartialOrder extends Model
{

    use SoftDeletes;

    protected $table = 'recipes_partial_orders';

    protected $fillable = [
        'sales_order_recipe_id',
        'total_meters',
        'machine_id',
    ];

}
