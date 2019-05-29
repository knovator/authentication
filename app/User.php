<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use \Knovators\Authentication\Models\User as Authenticatable;
use Knovators\Media\Models\Media;

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

    /**
     * @return BelongsTo
     */
    public function image() {
        return $this->belongsTo(Media::class, 'image_id', 'id')->select([
            'id',
            'name',
            'type',
            'mime_type',
            'uri'
        ]);
    }
}
