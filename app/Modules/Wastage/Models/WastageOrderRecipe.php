<?php

namespace App\Modules\Wastage\Models;

use App\Modules\Recipe\Models\Recipe;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class WastageOrderRecipe
 * @package App\Modules\Wastage\Models
 */
class WastageOrderRecipe extends Model
{

    use SoftDeletes;

    public $timestamps = false;

    protected $table = 'wastage_order_recipes';

    protected $fillable = [
        'wastage_order_id',
        'pcs',
        'meters',
        'total_meters',
        'recipe_id',
    ];
    /**
     * @return BelongsTo
     */
    public function recipe() {
        return $this->belongsTo(Recipe::class, 'recipe_id', 'id');
    }

}
