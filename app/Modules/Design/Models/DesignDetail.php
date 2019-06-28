<?php

namespace App\Modules\Design\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DesignDetail
 * @package App\Modules\DesignDetail\Models
 */
class DesignDetail extends Model
{

    public $timestamps = false;

    use SoftDeletes;

    protected $table = 'design_details';

    protected $fillable = [
        'design_id',
        'designer_no',
        'creming',
        'avg_pick',
        'pick_on_loom',
        'panno',
        'additional_panno',
        'reed',
    ];
    protected $hidden = [
        'deleted_at'
    ];

}
