<?php

namespace App\Modules\Wastage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class WastageOrderRecipe
 * @package App\Modules\Wastage\Models
 */
class WastageOrderRecipe extends Model
{

    use SoftDeletes;

    protected $table = 'wastage_order_recipes';

    protected $fillable = [
        'wastage_order_id',
        'pcs',
        'meters',
        'total_meters',
        'recipe_id',
    ];


}
