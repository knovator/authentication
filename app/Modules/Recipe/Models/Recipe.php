<?php

namespace App\Modules\Recipe\Models;


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
        'name',
        'total_fiddles',
        'is_active',
        'created_by',
        'deleted_by',
    ];


    protected $hidden = [
        'created_by',
        'deleted_by'
    ];


    /**
     * @return mixed
     */
    public function fiddles() {
        return $this->belongsToMany(ThreadColor::class, 'recipes_fiddles', 'recipe_id',
            'thread_color_id');
    }

}
