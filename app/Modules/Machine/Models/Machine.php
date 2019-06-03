<?php

namespace App\Modules\Machine\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Knovators\Support\Traits\HasModelEvent;

/**
 * Class Machine
 * @package App\Modules\Machine\Models
 */
class Machine extends Model
{

    use SoftDeletes, HasModelEvent;

    protected $table = 'machines';

    protected $fillable = [
        'name',
        'reed',
        'thread_color_id',
        'panno',
        'is_active',
        'created_by',
        'deleted_by',
    ];

    protected $hidden = [
        'created_by',
        'deleted_by',
        'deleted_at',
        'created_at',
        'updated_at'
    ];
}
