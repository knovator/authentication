<?php

namespace App\Modules\Design\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DesignFiddlePick
 * @package App\Modules\DesignDetail\Models
 */
class DesignFiddlePick extends Model
{
    public $timestamps = false;

    use SoftDeletes;

    protected $table = 'design_fiddle_picks';

    protected $fillable = [
        'design_id',
        'pick',
        'fiddle_no'
    ];

    protected $hidden = [
        'deleted_at'
    ];
}
