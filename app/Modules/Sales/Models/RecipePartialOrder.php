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

    public $timestamps = false;

    protected $fillable = [
        'sales_order_recipe_id',
        'pcs',
        'meters',
        'total_meters',
        'machine_id',
        'status_id',
    ];

}
