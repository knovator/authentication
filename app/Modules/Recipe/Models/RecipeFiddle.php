<?php

namespace App\Modules\Recipe\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class RecipeFiddle
 * @package App\Modules\Recipe\Models
 */
class RecipeFiddle extends Model
{
    protected $table = 'recipes_fiddles';

    protected $fillable = [
        'recipe_id',
        'thread_color_id',
        'fiddle_no'
    ];

}
