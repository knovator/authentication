<?php

namespace App\Modules\Design\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DesignBeam
 * @package App\Modules\Design\Models
 */
class DesignBeam extends Model
{

    use SoftDeletes;

    protected $table = 'design_beams';

    protected $fillable = [
        'design_id',
        'thread_color_id',
    ];


    protected $hidden = [
        'deleted_at'
    ];

}
