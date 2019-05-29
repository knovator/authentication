<?php

namespace App\Modules\Design\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DesignImage
 * @package App\Modules\DesignDetail\Models
 */
class DesignImage extends Model
{
    public $timestamps = false;

    use SoftDeletes;

    protected $table = 'design_images';

    protected $fillable = [
        'design_id',
        'file_id',
        'type'
    ];
    protected $hidden = [
        'deleted_at'
    ];

}
