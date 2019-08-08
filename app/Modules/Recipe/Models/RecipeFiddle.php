<?php

namespace App\Modules\Recipe\Models;


use Illuminate\Database\Eloquent\Model;

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

    /**
     * @return mixed
     */
    public function recipe() {
        return $this->belongsTo(Recipe::class, 'recipe_id', 'id');
    }

}
