<?php

namespace App\Modules\Design\Models;


use App\Modules\Recipe\Models\Recipe;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DesignBeam
 * @package App\Modules\Design\Models
 */
class DesignBeam extends Model
{

    use SoftDeletes;

    public $timestamps = false;

    protected $table = 'design_beams';

    protected $fillable = [
        'design_id',
        'thread_color_id',
    ];


    protected $hidden = [
        'deleted_at'
    ];


    /**
     * @return mixed
     */
    public function recipes() {
        return $this->belongsToMany(Recipe::class, 'beams_recipes', 'thread_color_id',
            'recipe_id');
    }


}
