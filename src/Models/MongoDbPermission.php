<?php

namespace Knovators\Authentication\Models;


use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Knovators\Authentication\Common\CommonService;
use Jenssegers\Mongodb\Eloquent\Model;

/**
 * Class Permission
 * @package Knovators\Authentication\Models
 */
class MongoDbPermission extends Model
{

    protected $fillable = [
        'route_name',
        'description',
        'module',
        'uri',
    ];
    protected $collection = 'permissions';

    /**
     * @return BelongsToMany
     */
    public function roles() {
        return $this->belongsToMany(CommonService::getClass('role'), 'permissions_roles',
            'permission_id', 'role_id');
    }

}
