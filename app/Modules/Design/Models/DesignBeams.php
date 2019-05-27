<?php

namespace App\Modules\Design\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Knovators\Support\Traits\HasModelEvent;

/**
 * Class DesignBeams
 * @package App\Modules\Design\Models
 */
class DesignBeams extends Model
{

    use SoftDeletes, HasModelEvent;

    protected $table = 'design_beams';

    protected $fillable = [
        'design_id',
        'thread_color_id',
    ];


    protected $hidden = [
        'deleted_at'
    ];

}
