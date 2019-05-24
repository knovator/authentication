<?php

namespace App;

use \Knovators\Authentication\Models\User as Authenticatable;

/**
 * Class User
 * @package App
 */
class User extends Authenticatable
{

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'deleted_at',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

}
