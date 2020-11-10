<?php

namespace Knovators\Authentication\Models;


use Jenssegers\Mongodb\Eloquent\Model;
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
        $config = config('authentication.db');
        if ($config === 'mongodb') {
            return $this->belongsToMany(CommonService::getClass('role'), null,
            'permission_ids', 'role_ids');
        }
        return $this->belongsToMany(CommonService::getClass('role'), 'permissions_roles',
            'permission_id', 'role_id');
    }


     /**
     * @return BelongsToMany
     */
    public function users() {
        $config = config('authentication.db');
        if ($config === 'mongodb') {
            return $this->belongsToMany(CommonService::getClass('user'), null,
            'permission_ids', 'user_ids');
        }
        return $this->belongsToMany(CommonService::getClass('user'), 'permissions_users',
            'permission_id', 'user_id');
    }

}
