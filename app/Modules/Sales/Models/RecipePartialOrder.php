<?php

namespace App\Modules\Sales\Models;


use App\Models\Master;
use App\Modules\Machine\Models\Machine;
use App\Modules\Stock\Models\Stock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class RecipePartialOrder
 * @package App\Modules\Sales\Models
 */
class RecipePartialOrder extends Model
{

    use SoftDeletes;

    public $timestamps = false;
    protected $table = 'recipes_partial_orders';
    protected $fillable = [
        'sales_order_recipe_id',
        'pcs',
        'meters',
        'total_meters',
        'machine_id',
        'delivery_id',
    ];


    protected $hidden = [
        'deleted_at'
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
    public function orderRecipe() {
        return $this->belongsTo(SalesOrderRecipe::class, 'sales_order_recipe_id',
            'id');
    }


    /**
     * @return mixed
     */
    public function delivery() {
        return $this->belongsTo(Delivery::class, 'delivery_id',
            'id');
    }


    /**
     * @return mixed
     */
    public function machine() {
        return $this->belongsTo(Machine::class, 'machine_id',
            'id');
    }


    /**
     * @return mixed
     */
    public function assignedMachine() {
        return $this->hasOne(PartialMachine::class, 'partial_order_id', 'id');
    }

    /**
     * @return mixed
     */
    public function stocks() {
        return $this->morphMany(Stock::class, 'partial_order',
            'partial_order_type', 'partial_order_id');
    }

}
