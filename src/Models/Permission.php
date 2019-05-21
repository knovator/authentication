<?php

namespace Knovators\Authentication\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Knovators\Authentication\Common\CommonService;

/**
 * Class Permission
 * @package Knovators\Authentication\Models
 */
class Permission extends Model
{

    protected $fillable = [
        'route_name',
        'description',
        'module',
        'uri',
    ];
    protected $table = 'permissions';

    /**
     * @return BelongsToMany
     */
    public function roles() {
        return $this->belongsToMany(CommonService::getClass('role'), 'permissions_roles',
            'permission_id', 'role_id');
    }

}
