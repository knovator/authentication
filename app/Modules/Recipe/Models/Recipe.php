<?php

namespace App\Modules\Recipe\Models;


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
        'fiddles',
        'is_active',
        'created_by',
        'deleted_by',
    ];

}
