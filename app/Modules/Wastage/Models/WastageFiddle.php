<?php

namespace App\Modules\Wastage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class WastageFiddle
 * @package App\Modules\Wastage\Models
 */
class WastageFiddle extends Model
{

    use SoftDeletes;

    protected $table = 'wastage_fiddle_picks';

    public $timestamps = false;

    protected $fillable = [
        'wastage_order_id',
        'pick',
        'fiddle_no'
    ];


    protected $hidden = [
        'deleted_at',
    ];

}
