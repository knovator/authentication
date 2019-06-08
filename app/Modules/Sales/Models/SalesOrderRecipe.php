<?php

namespace App\Modules\Sales\Models;


use App\Modules\Recipe\Models\Recipe;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class SalesOrderRecipe
 * @package App\Modules\Sales\Models
 */
class SalesOrderRecipe extends Model
{

    use SoftDeletes;

    protected $table = 'sales_orders_recipes';

    public $timestamps = false;

    protected $fillable = [
        'sales_order_id',
        'pcs',
        'meters',
        'total_meters',
        'recipe_id',
    ];

    /**
     * @return HasMany
     */
    public function partialOrders() {
        return $this->hasMany(RecipePartialOrder::class, 'sales_order_recipe_id', 'id');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function recipe() {
        return $this->belongsTo(Recipe::class, 'recipe_id', 'id');
    }


}
