<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class State
 * @package App\Models
 */
class State extends Model
{

    public $timestamps = false;

    protected $table = 'states';

    protected $fillable = ['name', 'code', 'gst_code', 'is_active'];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

}
