<?php

namespace App\Modules\Thread\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

/**
 * Class Thread
 * @package App\Modules\Thread\Models
 */
class Thread extends Model
{

    use SoftDeletes, Notifiable;

    protected $table = 'threads';

    protected $fillable = [
        'name',
        'denier',
        'type_id',
        'price',
        'is_active',
        'created_by',
        'deleted_by',
    ];

}
