<?php

namespace App\Modules\Recipe\Models;


use App\Modules\Design\Models\DesignBeam;
use App\Modules\Sales\Models\SalesOrderRecipe;
use App\Modules\Thread\Models\ThreadColor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Knovators\Support\Traits\HasModelEvent;

/**
 * Class Recipe
 * @package App\Modules\Recipe\Models
 */
class Recipe extends Model
{

    use SoftDeletes, HasModelEvent;

    protected $table = 'recipes';

    protected $fillable = [
        'type',
        'name',
        'total_fiddles',
        'is_active',
        'created_by',
        'deleted_by',
    ];


    protected $hidden = [
        'created_by',
        'deleted_by',
        'deleted_at'
    ];


    /**
     * @return mixed
     */
    public function fiddles() {
        return $this->belongsToMany(ThreadColor::class, 'recipes_fiddles', 'recipe_id',
            'thread_color_id')->withPivot('fiddle_no')->orderBy('recipes_fiddles.fiddle_no');
    }


    /**
     * @return mixed
     */
    public function feeders() {
        return $this->hasMany(RecipeFiddle::class, 'recipe_id', 'id');
    }


    /**
     * @return mixed
     */
    public function salesOrders() {
        return $this->hasMany(SalesOrderRecipe::class, 'recipe_id', 'id');
    }


    /**
     * @return mixed
     */
    public function designBeams() {
        return $this->belongsToMany(DesignBeam::class, 'beams_recipes', 'recipe_id',
            'design_beam_id');
    }

}
