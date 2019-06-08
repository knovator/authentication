<?php

namespace App\Modules\Sales\Models;


use App\Models\Master;
use App\Modules\Machine\Models\Machine;
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


    /**
     * @return mixed
     */
    public function status() {
        return $this->belongsTo(Master::class, 'status_id',
            'id');
    }



    /**
     * @return mixed
     */
    public function machine() {
        return $this->belongsTo(Machine::class, 'machine_id',
            'id');
    }

}
