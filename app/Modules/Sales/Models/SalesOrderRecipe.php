<?php

namespace App\Modules\Sales\Models;


use App\Modules\Recipe\Models\Recipe;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class SalesOrderRecipe
 * @package App\Modules\Sales\Models
 */
class SalesOrderRecipe extends Model
{

    use SoftDeletes;

    public $timestamps = false;
    protected $table = 'sales_orders_recipes';
    protected $fillable = [
        'sales_order_id',
        'pcs',
        'meters',
        'total_meters',
        'recipe_id',
    ];

    protected $hidden = ['deleted_at'];

    /**
     * @return HasMany
     */
    public function partialOrders() {
        return $this->hasMany(RecipePartialOrder::class, 'sales_order_recipe_id', 'id');
    }

    /**
     * @return mixed
     */
    public function salesOrder() {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id', 'id');
    }


    /**
     * @return HasOne
     */
    public function remainingQuantity() {
        return $this->hasOne(RecipePartialOrder::class, 'sales_order_recipe_id', 'id')
                    ->selectRaw('sales_order_recipe_id,SUM(recipes_partial_orders.total_meters) AS total')
                    ->groupBy('sales_order_recipe_id');
    }

    /**
     * @return mixed
     */
    public function getRemainingMetersAttribute() {

        if (!$this->relationLoaded('remainingQuantity')) {
            $this->load('remainingQuantity');
        }
        if (!is_null($this->remainingQuantity)) {
            return $this->total_meters - $this->remainingQuantity->total;
        }

        return $this->total_meters;
    }


    /**
     * @return BelongsTo
     */
    public function recipe() {
        return $this->belongsTo(Recipe::class, 'recipe_id', 'id');
    }


}
