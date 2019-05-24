<?php

namespace App\Modules\Thread\Models;


use App\Modules\Recipe\Models\Recipe;
use App\Modules\SalesOrder\Models\SalesOrderQuantity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Knovators\Masters\Models\Master;

/**
 * Class ThreadColor
 * @package App\Modules\Thread\Models
 */
class ThreadColor extends Model
{

    use SoftDeletes;

    protected $table = 'threads_colors';

    public $timestamps = false;

    protected $fillable = [
        'color_id',
        'thread_id',
    ];


    /**
     * @return mixed
     */
    public function color() {
        return $this->belongsTo(Master::class, 'color_id', 'id');
    }

    /**
     * @return mixed
     */
    public function thread() {
        return $this->belongsTo(Thread::class, 'thread_id', 'id');
    }

    /**
     * @return mixed
     */
    public function salesOrderQuantities() {
        return $this->hasMany(SalesOrderQuantity::class, 'thread_color_id', 'id');
    }

    /**
     * @return mixed
     */
    public function recipes() {
        return $this->belongsToMany(Recipe::class, 'recipes_fiddles', 'thread_color_id',
            'recipe_id');
    }

}
